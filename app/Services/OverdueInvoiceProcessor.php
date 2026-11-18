<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Services\DocumentRenderer as Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aturan jatuh tempo invoice (kesepakatan mitra Meeting 2, 10 Nov 2026; rincian = keputusan
 * sementara K-05/K-06 di docs/KEPUTUSAN.md):
 * - Invoice bertarif periode awal (early bird) yang lewat jatuh tempo: diskon hangus, invoice
 *   diterbitkan ulang dengan tarif periode berikutnya (reguler) dan jatuh tempo baru 7 hari.
 * - Invoice bertarif reguler yang lewat jatuh tempo: Kedaluwarsa. Bendahara boleh memperpanjang
 *   maksimal config('asic.max_invoice_extensions') kali; lewat lagi setelah batas itu = registrasi dibatalkan.
 */
class OverdueInvoiceProcessor
{
    public const OPEN_STATUSES = ['Menunggu Pembayaran', 'Ditolak'];

    public function __construct(private DocumentService $documents, private InvoiceIssuer $issuer) {}

    public static function maxExtensions(): int
    {
        return (int) config('asic.max_invoice_extensions', 2);
    }

    public static function dueDate(Invoice $invoice): ?Carbon
    {
        $due = $invoice->tanggal_jatuh_tempo_baru ?? $invoice->tanggal_jatuh_tempo;

        return $due ? Carbon::parse($due)->endOfDay() : null;
    }

    public function isOverdue(Invoice $invoice): bool
    {
        $due = self::dueDate($invoice);

        return $due !== null && $due->isPast() && in_array($invoice->status_pembayaran, self::OPEN_STATUSES, true);
    }

    /** Tarif periode berikutnya untuk kombinasi yang sama; null bila invoice sudah bertarif periode terakhir (reguler). */
    public function laterFee(Invoice $invoice): ?RegistrationFee
    {
        $fee = RegistrationFee::find($invoice->id_fee);
        $period = $fee ? RegistrationPeriod::find($fee->id_period) : null;
        if (! $fee || ! $period) {
            return null;
        }
        $laterPeriods = RegistrationPeriod::where('id_event', $fee->id_event)
            ->where('tanggal_mulai', '>', $period->tanggal_mulai)
            ->orderBy('tanggal_mulai')->pluck('id_period');
        foreach ($laterPeriods as $idPeriod) {
            $later = RegistrationFee::where([
                'id_event' => $fee->id_event, 'id_period' => $idPeriod,
                'registration_type' => $fee->registration_type, 'attendance_mode' => $fee->attendance_mode,
                'tujuan_publikasi' => $fee->tujuan_publikasi, 'mata_uang' => $invoice->mata_uang,
            ])->first();
            if ($later) {
                return $later;
            }
        }

        return null;
    }

    public function isEarlyBird(Invoice $invoice): bool
    {
        return $this->laterFee($invoice) !== null;
    }

    /** @return 'repriced'|'expired'|'cancelled'|null */
    public function process(Invoice $invoice): ?string
    {
        return DB::transaction(function () use ($invoice): ?string {
            $inv = Invoice::with('registration')->lockForUpdate()->find($invoice->id_invoice);
            if (! $inv || ! $this->isOverdue($inv) || $inv->registration?->registration_status === 'Dibatalkan') {
                return null;
            }
            if ($fee = $this->laterFee($inv)) {
                $this->reprice($inv, $fee);

                return 'repriced';
            }
            $inv->update(['status_pembayaran' => 'Kedaluwarsa']);
            if ((int) $inv->jumlah_perpanjangan >= self::maxExtensions()) {
                $inv->registration->update(['registration_status' => 'Dibatalkan']);
                app(StatusNotifier::class)->notify($inv->registration, 'registration_cancelled', 'registration_cancelled:'.$inv->id_registration);

                return 'cancelled';
            }
            app(StatusNotifier::class)->notify($inv->registration, 'invoice_expired', 'invoice_expired:'.$inv->id_invoice.':'.(int) $inv->jumlah_perpanjangan);

            return 'expired';
        });
    }

    /** @return array{repriced: int, expired: int, cancelled: int} */
    public function processAll(): array
    {
        $counts = ['repriced' => 0, 'expired' => 0, 'cancelled' => 0];
        Invoice::whereIn('status_pembayaran', self::OPEN_STATUSES)
            ->whereRaw('COALESCE(tanggal_jatuh_tempo_baru, tanggal_jatuh_tempo) < ?', [now()->toDateString()])
            ->orderBy('id_invoice')
            ->each(function (Invoice $invoice) use (&$counts): void {
                if ($result = $this->process($invoice)) {
                    $counts[$result]++;
                }
            });

        return $counts;
    }

    private function reprice(Invoice $inv, RegistrationFee $fee): void
    {
        /** @var Registration $reg */
        $reg = Registration::with(['registrationAddOns.addOn', 'registrationPeriod'])->findOrFail($inv->id_registration);
        $oldPeriod = $reg->registrationPeriod?->nama_periode;
        $reg->update(['id_period' => $fee->id_period]);
        $reg->load('registrationPeriod');

        $items = $this->issuer->lineItems($reg, $fee);
        $total = max(0.0, round(array_sum(array_column($items, 'subtotal')), 2));
        $due = now()->addDays((int) config('asic.invoice_due_days', 7))->toDateString();

        $inv->invoiceDetails()->delete();
        foreach ($items as $item) {
            InvoiceDetail::create(['id_invoice' => $inv->id_invoice] + $item);
        }
        $inv->update([
            'id_fee' => $fee->id_fee, 'total_tagihan' => $total, 'tanggal_invoice' => now(),
            'tanggal_jatuh_tempo' => $due, 'tanggal_jatuh_tempo_baru' => null, 'jumlah_perpanjangan' => 0,
            'status_pembayaran' => 'Menunggu Pembayaran',
        ]);

        $note = 'Revised invoice: the payment deadline for the '.($oldPeriod ?: 'early bird').' rate has passed, so the '
            .($reg->registrationPeriod?->nama_periode ?: 'regular').' rate applies.';
        $document = $this->documents->reissue($reg, 'Invoice', [
            'items' => $items, 'total' => $total, 'invoice_due' => $due, 'currency' => $inv->mata_uang,
            'payment_qr_info' => $inv->payment_qr_info, 'revision_note' => $note,
        ], $inv->nomor_invoice);

        app(StatusNotifier::class)->notify($reg, 'invoice_repriced', 'document:'.$document->id_document.':repriced:'.$inv->id_invoice.':'.$fee->id_fee, [
            'due' => $due, 'total' => Money::money($total, $inv->mata_uang).' '.$inv->mata_uang,
        ]);
    }
}
