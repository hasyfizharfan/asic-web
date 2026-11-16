@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => 'Bendahara', 'nav' => $treasurerNav, 'title' => 'Detail pembayaran'])

@section('content')
<main class="mx-auto max-w-4xl space-y-5" x-data="{
    id: @js((string) $id), invoice: null, loading: true, error: '', busy: false,
    get proofs() { return this.invoice?.payment_proofs || []; },
    get latestProof() { return this.proofs[this.proofs.length - 1] || null; },
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.error = '';
        const result = await window.asicApi.get('/api/payments/' + this.id);
        this.loading = false;
        if (!result.ok) { this.error = result.error || 'Invoice gagal dimuat.'; return; }
        this.invoice = result.data?.data || null;
    },
    async verify(valid) {
        const proof = this.latestProof;
        if (!proof) return;
        const reason = valid ? '' : window.prompt('Alasan penolakan:');
        if (!valid && !reason) return;
        this.busy = true;
        const result = await window.asicApi.post('/api/payment-proofs/' + proof.id_payment_proof + '/verify', {
            hasil_verifikasi: valid ? 'Valid' : 'Tidak Valid',
            ...(reason ? { alasan_penolakan: reason } : {})
        });
        this.busy = false;
        if (!result.ok) { this.error = result.error || 'Verifikasi gagal.'; return; }
        this.invoice.status_pembayaran = result.data?.data?.status_pembayaran || this.invoice.status_pembayaran;
        await this.load();
        window.asicApi.showToast(result.data?.message || 'Pembayaran diperbarui.', 'success');
    }
}">
    <a href="{{ route('portal.treasurer.payments.index') }}" class="text-sm text-primary underline">Kembali ke antrean</a>
    <h1 class="text-2xl font-bold">Detail pembayaran</h1>
    <p x-show="loading" role="status">Memuat invoice…</p>
    <p x-show="error" x-text="error" role="alert" class="rounded-lg bg-error-container p-3 text-error"></p>
    <section x-show="!loading && invoice" class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6">
        <div><span class="text-sm text-on-surface-variant">Nomor invoice</span><p class="font-semibold" x-text="invoice?.nomor_invoice"></p></div>
        <div><span class="text-sm text-on-surface-variant">Peserta</span><p x-text="invoice?.registration?.participant?.full_name || invoice?.registration?.participant?.user_account?.email"></p></div>
        <div><span class="text-sm text-on-surface-variant">Total</span><p x-text="window.asicMoney(invoice?.total_tagihan, invoice?.mata_uang)"></p></div>
        <div><span class="text-sm text-on-surface-variant">Status</span><p x-text="invoice?.status_pembayaran"></p></div>
        <template x-if="latestProof"><a class="text-primary underline" target="_blank" :href="'/api/payment-proofs/' + latestProof.id_payment_proof + '/file'">Lihat bukti transfer</a></template>
        <div x-show="invoice?.status_pembayaran === 'Menunggu Verifikasi' && latestProof" class="flex gap-3">
            <button type="button" :disabled="busy" @click="verify(true)" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Verifikasi</button>
            <button type="button" :disabled="busy" @click="verify(false)" class="rounded-lg border border-error px-4 py-2 text-sm font-semibold text-error">Tolak</button>
        </div>
    </section>
</main>
@endsection
