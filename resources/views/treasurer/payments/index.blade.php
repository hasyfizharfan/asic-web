@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => 'Pembayaran', 'nav' => $treasurerNav])

@section('content')
<main class="space-y-6" x-data="{
    invoices: [], loading: true, loadError: '', actionError: '', status: 'Menunggu Verifikasi', search: '',
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/payments', { status: this.status, search: this.search });
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Antrean pembayaran gagal dimuat.'; return; }
        this.invoices = response.data?.data?.data || response.data?.data || [];
    },
    async verify(invoice, proof, valid) {
        this.actionError = '';
        const reason = valid ? null : window.prompt('Alasan penolakan bukti transfer:');
        if (!valid && !reason) return;
        const response = await window.asicApi.post('/api/payment-proofs/' + proof.id_payment_proof + '/verify', {
            hasil_verifikasi: valid ? 'Valid' : 'Tidak Valid',
            ...(reason ? { alasan_penolakan: reason } : {})
        });
        if (!response.ok) { this.actionError = response.error || 'Verifikasi gagal.'; window.asicApi.showToast(this.actionError); return; }
        invoice.status_pembayaran = response.data?.data?.status_pembayaran || (valid ? 'Terverifikasi' : 'Ditolak');
        await this.load();
        window.asicApi.showToast(response.data?.message || 'Status pembayaran diperbarui.', 'success');
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-on-surface">Antrean pembayaran</h1><p class="mt-1 text-sm text-on-surface-variant">Periksa bukti transfer lalu verifikasi atau tolak dengan alasan.</p></div>
        <a href="/api/exports/payments" class="rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold">Ekspor pembayaran</a>
    </header>
    <div class="flex flex-wrap gap-3">
        <select x-model="status" @change="load()" class="rounded-xl border-surface-container-highest text-sm">
            <option value="Menunggu Verifikasi">Menunggu Verifikasi</option><option value="all">Semua status</option><option value="Terverifikasi">Terverifikasi</option><option value="Ditolak">Ditolak</option>
        </select>
        <input x-model="search" @keydown.enter.prevent="load()" placeholder="Cari nomor invoice" class="rounded-xl border-surface-container-highest text-sm">
        <button type="button" @click="load()" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white">Cari</button>
    </div>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat pembayaran…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <div x-show="!loading && !loadError && invoices.length === 0" class="rounded-xl border border-surface-container-highest p-8 text-center text-sm text-on-surface-variant">Tidak ada pembayaran untuk filter ini.</div>
    <div x-show="!loading && !loadError && invoices.length > 0" class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead><tr class="border-b border-surface-container-high text-xs uppercase text-on-surface-variant"><th class="p-4">Invoice / Peserta</th><th class="p-4">Nominal</th><th class="p-4">Status</th><th class="p-4">Bukti</th><th class="p-4">Tindakan</th></tr></thead>
            <tbody><template x-for="invoice in invoices" :key="invoice.id_invoice"><tr class="border-b border-surface-container-highest align-top">
                <td class="p-4"><a :href="'/treasurer/payments/' + invoice.id_invoice" class="font-semibold text-primary" x-text="invoice.nomor_invoice"></a><div class="mt-1 text-xs text-on-surface-variant" x-text="invoice.registration?.participant?.full_name || invoice.registration?.participant?.user_account?.email || 'Peserta'"></div></td>
                <td class="p-4" x-text="new Intl.NumberFormat('id-ID').format(Number(invoice.total_tagihan || 0))"></td><td class="p-4" x-text="invoice.status_pembayaran"></td>
                <td class="p-4"><template x-if="invoice.latest_payment_proof"><a :href="'/api/payment-proofs/' + invoice.latest_payment_proof.id_payment_proof + '/file'" target="_blank" class="text-primary underline">Lihat bukti</a></template><span x-show="!invoice.latest_payment_proof">—</span></td>
                <td class="space-x-2 p-4" x-show="invoice.status_pembayaran === 'Menunggu Verifikasi' && invoice.latest_payment_proof"><button type="button" @click="verify(invoice, invoice.latest_payment_proof, true)" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white">Verifikasi</button><button type="button" @click="verify(invoice, invoice.latest_payment_proof, false)" class="rounded-lg border border-error px-3 py-1.5 text-xs font-bold text-error">Tolak</button></td>
            </tr></template></tbody>
        </table>
    </div>
</main>
@endsection
