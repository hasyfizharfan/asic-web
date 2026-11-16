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
        // Nominal tidak dijumlahkan lintas mata uang.
        $verifiedSums = Invoice::query()->where('status_pembayaran', 'Terverifikasi')->selectRaw('mata_uang, SUM(total_tagihan) as amount')->groupBy('mata_uang')->pluck('amount', 'mata_uang');
        $verifiedTotals = collect(Currency::ALL)->mapWithKeys(fn ($c) => [$c => (float) ($verifiedSums[$c] ?? 0)])->all();
        $expiredInvoices = Invoice::query()->where(function (Builder $q): void {
            $q->whereIn('status_pembayaran', ['Kedaluwarsa', 'Kadaluwarsa'])
                ->orWhere(fn (Builder $due) => $due->where('status_pembayaran', 'Menunggu Pembayaran')
                    ->whereRaw('COALESCE(tanggal_jatuh_tempo_baru, tanggal_jatuh_tempo) < ?', [now()->toDateString()]));
        })->count();
        $categorySummary = Invoice::query()->where('status_pembayaran', 'Terverifikasi')
            ->join('registration', 'registration.id_registration', '=', 'invoice.id_registration')
            ->selectRaw('registration.registration_type as category, invoice.mata_uang as currency, COUNT(invoice.id_invoice) as invoice_count, SUM(invoice.total_tagihan) as amount')
            ->groupBy('registration.registration_type', 'invoice.mata_uang')->orderBy('registration.registration_type')->orderBy('invoice.mata_uang')->get();
        $latestPending = Invoice::query()->with(['registration.participant', 'latestPaymentProof'])
            ->where('status_pembayaran', 'Menunggu Verifikasi')->latest('id_invoice')->limit(5)->get();

        return view('treasurer.dashboard', $this->identity($request) + compact('pendingProofs', 'verifiedTotals', 'expiredInvoices', 'categorySummary', 'latestPending'));
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

        return view('treasurer.payments.index', $this->identity($request) + compact('invoices'));
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
