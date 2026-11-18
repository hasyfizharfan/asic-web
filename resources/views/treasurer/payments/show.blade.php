@php($t = trans('asic-treasurer.payment_detail'))
@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => $t['title'], 'nav' => $treasurerNav, 'title' => $t['title']])

@section('content')
<main class="mx-auto max-w-4xl space-y-5" x-data="{
    i18n: @js($t), methods: @js(trans('asic-common.payment_methods')),
    id: @js((string) $id), invoice: null, loading: true, error: '', busy: false,
    get proofs() { return this.invoice?.payment_proofs || []; },
    get latestProof() { return this.proofs[this.proofs.length - 1] || null; },
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.error = '';
        const result = await window.asicApi.get('/api/payments/' + this.id);
        this.loading = false;
        if (!result.ok) { this.error = result.error || this.i18n.load_failed; return; }
        this.invoice = result.data?.data || null;
    },
    async verify(valid) {
        const proof = this.latestProof;
        if (!proof) return;
        const reason = valid ? '' : window.prompt(this.i18n.reject_prompt);
        if (!valid && !reason) return;
        this.busy = true;
        const result = await window.asicApi.post('/api/payment-proofs/' + proof.id_payment_proof + '/verify', {
            hasil_verifikasi: valid ? 'Valid' : 'Tidak Valid',
            ...(reason ? { alasan_penolakan: reason } : {})
        });
        this.busy = false;
        if (!result.ok) { this.error = result.error || this.i18n.verify_failed; return; }
        this.invoice.status_pembayaran = result.data?.data?.status_pembayaran || this.invoice.status_pembayaran;
        await this.load();
        window.asicApi.showToast(result.data?.message || this.i18n.updated, 'success');
    }
}">
    <a href="{{ route('portal.treasurer.payments.index') }}" class="text-sm text-primary underline">{{ $t['back'] }}</a>
    <h1 class="text-2xl font-bold">{{ $t['title'] }}</h1>
    <p x-show="loading" role="status">{{ $t['loading'] }}</p>
    <p x-show="error" x-text="error" role="alert" class="rounded-lg bg-error-container p-3 text-error"></p>
    <section x-show="!loading && invoice" class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-on-surface-variant">{{ $t['invoice_no'] }}</dt><dd class="font-semibold" x-text="invoice?.nomor_invoice"></dd></div>
            <div><dt class="text-sm text-on-surface-variant">{{ $t['participant'] }}</dt><dd x-text="invoice?.registration?.participant?.full_name || invoice?.registration?.participant?.user_account?.email"></dd></div>
            <div><dt class="text-sm text-on-surface-variant">{{ $t['total'] }}</dt><dd class="font-semibold" x-text="window.asicMoney(invoice?.total_tagihan, invoice?.mata_uang)"></dd></div>
            <div><dt class="text-sm text-on-surface-variant">{{ $t['status'] }}</dt><dd x-text="invoice?.status_pembayaran"></dd></div>
            <template x-if="latestProof">
                <div><dt class="text-sm text-on-surface-variant">{{ $t['method'] }}</dt><dd class="font-semibold" x-text="methods[latestProof.metode_pembayaran] || '—'"></dd></div>
            </template>
            <template x-if="latestProof">
                <div><dt class="text-sm text-on-surface-variant">{{ $t['uploaded_at'] }}</dt><dd x-text="asicDateTime(latestProof.tanggal_upload)"></dd></div>
            </template>
            <template x-if="latestProof?.catatan_peserta">
                <div class="sm:col-span-2"><dt class="text-sm text-on-surface-variant">{{ $t['note'] }}</dt><dd class="whitespace-pre-line" x-text="latestProof.catatan_peserta"></dd></div>
            </template>
        </dl>
        <template x-if="latestProof"><a class="act-btn" target="_blank" rel="noopener" :href="'/api/payment-proofs/' + latestProof.id_payment_proof + '/file'"><span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>{{ $t['view_proof'] }}</a></template>
        <div x-show="invoice?.status_pembayaran === 'Menunggu Verifikasi' && latestProof" class="flex gap-3">
            <button type="button" :disabled="busy" @click="verify(true)" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">{{ $t['verify'] }}</button>
            <button type="button" :disabled="busy" @click="verify(false)" class="rounded-lg border border-error px-4 py-2 text-sm font-semibold text-error">{{ $t['reject'] }}</button>
        </div>
    </section>
</main>
@endsection
