<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Models\UserAccount;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TreasurerController extends Controller
{
    /** Urutan kategori peserta di ringkasan (mengikuti enum registration.registration_type). */
    private const CATEGORY_ORDER = ['Student', 'Regular', 'Committee'];

    private function identity(Request $request): array
    {
        /** @var UserAccount $user */
        $user = $request->user();
        $user->loadMissing('role');

        return ['role' => 'treasurer', 'roleName' => $user->role?->nama_role ?? 'Bendahara', 'userName' => $user->email];
    }

    public function dashboard(Request $request): View
    {
        $pendingProofs = Invoice::query()->where('status_pembayaran', 'Menunggu Verifikasi')->whereHas('latestPaymentProof')->count();
        // Nominal tidak dijumlahkan lintas mata uang: satu baris per mata uang (jumlah + banyak invoice).
        $verifiedRows = Invoice::query()->where('status_pembayaran', 'Terverifikasi')
            ->selectRaw('mata_uang, SUM(total_tagihan) as amount, COUNT(*) as invoice_count')->groupBy('mata_uang')->get();
        $verifiedTotals = collect(Currency::ALL)->mapWithKeys(function (string $c) use ($verifiedRows): array {
            $rows = $verifiedRows->filter(fn ($r) => Currency::normalize($r->mata_uang) === $c);

            return [$c => ['amount' => (float) $rows->sum('amount'), 'count' => (int) $rows->sum('invoice_count')]];
        })->all();
        $verifiedCount = array_sum(array_column($verifiedTotals, 'count'));
        $expiredInvoices = Invoice::query()->where(function (Builder $q): void {
            $q->whereIn('status_pembayaran', ['Kedaluwarsa', 'Kadaluwarsa'])
                ->orWhere(fn (Builder $due) => $due->where('status_pembayaran', 'Menunggu Pembayaran')
                    ->whereRaw('COALESCE(tanggal_jatuh_tempo_baru, tanggal_jatuh_tempo) < ?', [now()->toDateString()]));
        })->count();
        $categorySummary = Invoice::query()->where('status_pembayaran', 'Terverifikasi')
            ->join('registration', 'registration.id_registration', '=', 'invoice.id_registration')
            ->selectRaw('registration.registration_type as category, invoice.mata_uang as currency, COUNT(invoice.id_invoice) as invoice_count, SUM(invoice.total_tagihan) as amount')
            ->groupBy('registration.registration_type', 'invoice.mata_uang')->get()
            // Satu baris per kategori; IDR dan USD jadi kolom terpisah agar kategori tidak muncul dua kali.
            ->groupBy('category')->map(fn ($rows, $category) => [
                'category' => $category,
                'count' => (int) $rows->sum('invoice_count'),
                'amounts' => collect(Currency::ALL)->mapWithKeys(fn (string $c) => [$c => (float) $rows->filter(fn ($r) => Currency::normalize($r->currency) === $c)->sum('amount')])->all(),
            ])
            ->sortBy(fn (array $row) => ($i = array_search($row['category'], self::CATEGORY_ORDER, true)) === false ? 99 : $i)->values();
        $latestPending = Invoice::query()->with(['registration.participant', 'latestPaymentProof'])
            ->where('status_pembayaran', 'Menunggu Verifikasi')->latest('id_invoice')->limit(5)->get();

        return view('treasurer.dashboard', $this->identity($request) + compact('pendingProofs', 'verifiedTotals', 'verifiedCount', 'expiredInvoices', 'categorySummary', 'latestPending'));
    }

    public function tariffs(Request $request): View
    {
        $fees = RegistrationFee::query()->with(['event', 'registrationPeriod'])->withCount('invoices')->orderBy('id_event')->orderBy('id_period')->get();
        $events = Event::query()->orderBy('nama_event')->get(['id_event', 'nama_event']);
        $periods = RegistrationPeriod::query()->with('event:id_event,nama_event')->orderBy('id_event')->orderBy('tanggal_mulai')->get();

        return view('treasurer.tariffs', $this->identity($request) + compact('fees', 'events', 'periods'));
    }

    public function payments(Request $request): View
    {
        $invoices = Invoice::query()->with(['registration.participant.userAccount', 'latestPaymentProof.verification'])
            ->where('status_pembayaran', 'Menunggu Verifikasi')->latest('id_invoice')->get();

        $byStatus = Invoice::query()->selectRaw('status_pembayaran, COUNT(*) as total')->groupBy('status_pembayaran')->pluck('total', 'status_pembayaran');
        $overdue = Invoice::query()->where(function (Builder $q): void {
            $q->whereIn('status_pembayaran', ['Kedaluwarsa', 'Kadaluwarsa'])
                ->orWhere(fn (Builder $due) => $due->where('status_pembayaran', 'Menunggu Pembayaran')
                    ->whereRaw('COALESCE(tanggal_jatuh_tempo_baru, tanggal_jatuh_tempo) < ?', [now()->toDateString()]));
        })->count();
        $counts = [
            'total' => (int) $byStatus->sum(),
            'waiting' => (int) ($byStatus['Menunggu Verifikasi'] ?? 0),
            'verified' => (int) ($byStatus['Terverifikasi'] ?? 0),
            'rejected' => (int) ($byStatus['Ditolak'] ?? 0),
            'overdue' => $overdue,
        ];

        return view('treasurer.payments.index', $this->identity($request) + compact('invoices', 'counts'));
    }

    public function paymentDetails(Request $request, int $id): View
    {
        $invoice = Invoice::query()->with([
            'registration.participant.userAccount', 'invoiceDetails',
            'paymentProofs' => fn ($q) => $q->orderBy('id_payment_proof'), 'paymentProofs.verification.treasurer:id_user,email',
        ])->findOrFail($id);

        return view('treasurer.payments.show', $this->identity($request) + compact('id', 'invoice'));
    }

    public function receipts(Request $request): View
    {
        $receipts = IssuedDocument::query()->with(['registration.participant.userAccount', 'registration.event'])
            ->where('jenis_dokumen', 'Receipt')->latest('id_document')->get();

        return view('treasurer.receipts.preview', $this->identity($request) + compact('receipts'));
    }

    public function refunds(Request $request): View
    {
        return view('treasurer.refunds', $this->identity($request));
    }
}
