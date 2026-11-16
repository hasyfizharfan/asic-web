<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Support\Currency;
use Illuminate\Support\Facades\DB;

/**
 * Penerbit invoice otomatis (KF-20). Tarif diambil dari registration_fee lalu di-snapshot
 * ke invoice_detail + total_tagihan + PDF tersimpan, sehingga perubahan tarif tidak
 * memengaruhi invoice yang sudah terbit. Mata uang tarif mengikuti negara peserta
 * (Currency::forRegistration) dan di-snapshot ke invoice.mata_uang; tidak ada fallback ke mata uang lain.
 */
class InvoiceIssuer
{
    public function __construct(private DocumentService $documents) {}

    /**
     * @return array{status: 'issued'|'exists'|'skipped', invoice: ?Invoice, reason: ?string}
     */
    public function issueFor(Registration $registration): array
    {
        return DB::transaction(function () use ($registration) {
            $reg = Registration::with(['registrationAddOns.addOn'])
                ->whereKey($registration->getKey())->lockForUpdate()->firstOrFail();

            if ($existing = Invoice::where('id_registration', $reg->id_registration)->first()) {
                return ['status' => 'exists', 'invoice' => $existing, 'reason' => null];
            }
            if ($reg->registration_status !== 'Terdaftar') {
                return $this->skipped('Registrasi tidak berstatus Terdaftar.');
            }
            $currency = Currency::forRegistration($reg);
            $fee = RegistrationFee::where([
                'id_event' => $reg->id_event,
                'id_period' => $reg->id_period,
                'registration_type' => $reg->registration_type,
                'attendance_mode' => $reg->attendance_mode,
                'tujuan_publikasi' => $reg->publication_option,
                'mata_uang' => $currency,
            ])->first();
            if (! $fee) {
                return $this->skipped("Tarif {$currency} untuk kombinasi event/periode/kategori/mode/publikasi belum dikonfigurasi.");
            }
            if ($currency !== Currency::IDR && $reg->registrationAddOns->isNotEmpty()) {
                return $this->skipped('Layanan tambahan (add-on) hanya tersedia dalam IDR; invoice USD tidak dapat memuat add-on.');
            }

            $items = [[
                'jenis_biaya' => 'Biaya Registrasi '.$reg->registration_type.' ('.$reg->attendance_mode.')',
                'deskripsi' => 'Publikasi: '.$reg->publication_option,
                'jumlah' => 1,
                'harga_satuan' => (float) $fee->amount,
                'subtotal' => (float) $fee->amount,
            ]];
            if ((float) $fee->discount > 0) {
                $items[] = [
                    'jenis_biaya' => 'Potongan Harga', 'deskripsi' => null,
                    'jumlah' => 1, 'harga_satuan' => -(float) $fee->discount, 'subtotal' => -(float) $fee->discount,
                ];
            }
            foreach ($reg->registrationAddOns as $line) {
                $qty = max(1, (int) $line->jumlah);
                $items[] = [
                    'jenis_biaya' => 'Add-on: '.($line->addOn?->nama_add_on ?? 'Layanan tambahan'),
                    'deskripsi' => null, 'jumlah' => $qty,
                    'harga_satuan' => round((float) $line->subtotal / $qty, 2), 'subtotal' => (float) $line->subtotal,
                ];
            }
            $total = max(0.0, round(array_sum(array_column($items, 'subtotal')), 2));

            $template = $this->documents->resolveTemplate($reg->id_event, 'Invoice');
            $nomor = $this->documents->nextNumber($template, 'Invoice', $reg);
            $due = now()->addDays((int) config('asic.invoice_due_days', 7))->toDateString();

            $invoice = Invoice::create([
                'id_registration' => $reg->id_registration,
                'id_fee' => $fee->id_fee,
                'nomor_invoice' => $nomor,
                'tanggal_invoice' => now(),
                'tanggal_jatuh_tempo' => $due,
                'total_tagihan' => $total,
                'mata_uang' => $currency,
                'payment_qr_info' => config('asic.payment_qr_info'),
                'status_pembayaran' => 'Menunggu Pembayaran',
            ]);
            foreach ($items as $item) {
                InvoiceDetail::create(['id_invoice' => $invoice->id_invoice] + $item);
            }

            $this->documents->issue($reg, 'Invoice', [
                'items' => $items, 'total' => $total, 'invoice_due' => $due, 'currency' => $currency,
                'payment_qr_info' => $invoice->payment_qr_info,
            ], $nomor);

            return ['status' => 'issued', 'invoice' => $invoice, 'reason' => null];
        });
    }

    private function skipped(string $reason): array
    {
        return ['status' => 'skipped', 'invoice' => null, 'reason' => $reason];
    }
}
