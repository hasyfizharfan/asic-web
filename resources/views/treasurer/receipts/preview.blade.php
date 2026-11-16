@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => 'Kuitansi'])

@section('content')
<main class="mx-auto max-w-5xl space-y-6" x-data="{
    receipts: [], loading: true, loadError: '',
    async init() {
        const payments = await window.asicApi.get('/api/payments', { status: 'Terverifikasi', per_page: 100 });
        if (!payments.ok) { this.loadError = payments.error || 'Data pembayaran gagal dimuat.'; this.loading = false; return; }
        const rows = payments.data?.data?.data || payments.data?.data || [];
        try {
            const results = await Promise.all(rows.map(async invoice => {
                const id = invoice.registration?.id_registration;
                if (!id) return [];
                const response = await window.asicApi.get('/api/registrations/' + id + '/documents');
                if (!response.ok) throw new Error(response.error || 'Dokumen kuitansi gagal dimuat.');
                return (response.data?.data || []).filter(doc => doc.jenis_dokumen === 'Receipt').map(doc => ({ ...doc, invoice, participant: invoice.registration?.participant }));
            }));
            this.receipts = results.flat();
        } catch (error) { this.loadError = error.message || 'Dokumen kuitansi gagal dimuat.'; }
        finally { this.loading = false; }
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">Kuitansi pembayaran</h1><p class="mt-1 text-sm text-on-surface-variant">Kuitansi terbit dari pembayaran terverifikasi. Unduhan dilayani oleh endpoint dokumen.</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat kuitansi…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-error"></p>
    <p x-show="!loading && !loadError && receipts.length === 0" class="rounded-2xl border border-surface-container-highest p-8 text-center text-sm text-on-surface-variant">Belum ada kuitansi terbit.</p>
    <div class="space-y-3"><template x-for="receipt in receipts" :key="receipt.id_document"><article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5"><div><h2 class="font-bold" x-text="receipt.jenis_dokumen + ' · ' + (receipt.nomor_dokumen || 'Tanpa nomor')"></h2><p class="mt-1 text-sm text-on-surface-variant" x-text="(receipt.participant?.full_name || receipt.participant?.user_account?.email || 'Peserta') + ' · Invoice ' + receipt.invoice.nomor_invoice + ' · ' + (receipt.tanggal_terbit || '')"></p></div><a :href="'/api/documents/' + receipt.id_document + '/download'" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white">Unduh PDF</a></article></template></div>
</main>
@endsection
