@php($t = trans('asic-participant.portal.checkout'))
@extends('layouts.portal', [
    'role' => 'participant',
    'roleName' => __('asic-participant.portal.checkout.title'),
    'nav' => $participantNav,
])

@section('content')
<main class="mx-auto max-w-3xl space-y-6" x-data="{
    i18n: @js($t), loading: true, submitting: false, loadError: '', fieldErrors: {},
    registration: null, invoice: null,
    async init() {
        const requestedId = new URLSearchParams(window.location.search).get('id_registration');
        const response = await window.asicApi.get('/api/registrations');
        if (!response.ok) { this.loadError = response.error || this.i18n.load_failed; this.loading = false; return; }
        const registrations = response.data?.data?.data || [];
        this.registration = registrations.find(row => String(row.id_registration) === String(requestedId)) || registrations[0] || null;
        if (!this.registration) { this.loadError = this.i18n.no_registration; this.loading = false; return; }
        if (!this.registration.invoice?.id_invoice) {
            const presenter = ['Oral Presenter', 'Poster Presenter'].includes(this.registration.participation_type);
            this.loadError = presenter ? @js(__('asic-participant.flow.invoice_after_acceptance')) : @js(__('asic-participant.flow.invoice_unavailable'));
            this.loading = false; return;
        }
        const invoiceResponse = await window.asicApi.get('/api/invoices/' + this.registration.invoice.id_invoice);
        this.loading = false;
        if (!invoiceResponse.ok) { this.loadError = invoiceResponse.error || this.i18n.invoice_failed; return; }
        this.invoice = invoiceResponse.data?.data;
    },
    async uploadProof(event) {
        this.fieldErrors = {};
        this.submitting = true;
        const response = await window.asicApi.upload('/api/invoices/' + this.invoice.id_invoice + '/payment-proofs', new FormData(event.target));
        this.submitting = false;
        if (!response.ok) {
            this.fieldErrors = response.errors || {};
            window.asicApi.showToast(response.error || this.i18n.upload_failed);
            return;
        }
        this.invoice = { ...this.invoice, status_pembayaran: response.data?.data?.status_pembayaran || 'Menunggu Verifikasi' };
        event.target.reset();
        window.asicApi.showToast(response.data?.message || this.i18n.uploaded, 'success');
    },
    paymentInfo: @js(\App\Support\PaymentSettings::current()),
    internationalNote: @js(\App\Support\PaymentSettings::current()['international_note']),
    formatAmount(value) { return window.asicMoney(value, this.invoice?.mata_uang); }
}">
    <header class="space-y-1">
        <h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1>
        <p class="text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p>
    </header>

    <div x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-5 text-sm">{{ $t['loading'] }}</div>
    <div x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-5 text-sm text-on-error-container"></div>

    <template x-if="invoice">
        <section class="space-y-5 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6">
            <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-on-surface-variant">{{ $t['invoice_no'] }}</dt><dd class="font-mono font-bold" x-text="invoice.nomor_invoice"></dd></div>
                <div><dt class="text-on-surface-variant">{{ $t['status'] }}</dt><dd class="font-semibold" x-text="invoice.status_pembayaran"></dd></div>
                <div><dt class="text-on-surface-variant">{{ $t['due'] }}</dt><dd x-text="invoice.tanggal_jatuh_tempo"></dd></div>
                <div><dt class="text-on-surface-variant">Total</dt><dd class="text-lg font-bold text-primary" x-text="formatAmount(invoice.total_tagihan)"></dd></div>
            </dl>
            <div class="rounded-xl bg-surface-container-low p-4 text-sm">
                <h2 class="font-bold">{{ $t['instructions'] }}</h2>
                <p class="mt-1 whitespace-pre-line" x-text="invoice.payment_qr_info || i18n.contact_treasurer"></p>
                <dl class="mt-2 grid gap-1 text-xs sm:grid-cols-2">
                    <template x-for="[key, label] in [['bank_name', @js(__('asic-participant.pay_info.bank'))], ['account_name', @js(__('asic-participant.pay_info.account_name'))], ['account_no', @js(__('asic-participant.pay_info.account_no'))], ['swift_code', 'SWIFT'], ['branch', @js(__('asic-participant.pay_info.branch'))]]" :key="key">
                        <div x-show="paymentInfo[key]"><dt class="text-on-surface-variant" x-text="label"></dt><dd class="font-mono font-semibold" x-text="paymentInfo[key]"></dd></div>
                    </template>
                </dl>
                <p x-show="invoice.mata_uang === 'USD' && internationalNote" class="mt-2 whitespace-pre-line font-semibold" x-text="internationalNote"></p>
            </div>
            <template x-if="invoice.status_pembayaran === 'Menunggu Pembayaran' || invoice.status_pembayaran === 'Ditolak'">
                <form class="space-y-4 border-t border-surface-container-highest pt-5" @submit.prevent="uploadProof($event)">
                    <h2 class="font-bold">{{ $t['upload_title'] }}</h2>
                    <label class="block text-sm font-semibold">{{ $t['amount'] }}
                        <input type="number" name="nominal_transfer" min="0.01" :step="invoice.mata_uang === 'USD' ? '0.01' : '1'" required class="mt-1 block w-full rounded-xl border-surface-container-highest">
                    </label>
                    <p x-show="fieldErrors.nominal_transfer" x-text="fieldErrors.nominal_transfer?.[0]" class="text-sm text-error"></p>
                    <label class="block text-sm font-semibold">{{ $t['proof'] }}
                        <input type="file" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
                    </label>
                    <p x-show="fieldErrors.file_bukti" x-text="fieldErrors.file_bukti?.[0]" class="text-sm text-error"></p>
                    <label class="block text-sm font-semibold">{{ $t['note'] }}
                        <textarea name="catatan_peserta" rows="2" class="mt-1 block w-full rounded-xl border-surface-container-highest"></textarea>
                    </label>
                    <p x-show="fieldErrors.catatan_peserta" x-text="fieldErrors.catatan_peserta?.[0]" class="text-sm text-error"></p>
                    <button type="submit" :disabled="submitting" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="submitting ? i18n.sending : i18n.send"></button>
                </form>
            </template>
            <p x-show="invoice.status_pembayaran === 'Menunggu Verifikasi'" class="rounded-xl bg-secondary-container p-4 text-sm">{{ $t['waiting'] }}</p>
            <p x-show="invoice.status_pembayaran === 'Terverifikasi'" class="rounded-xl bg-secondary-container p-4 text-sm">{{ $t['verified'] }}</p>
        </section>
    </template>
</main>
@endsection
