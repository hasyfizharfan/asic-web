@extends('layouts.portal', [
    'role' => 'participant',
    'roleName' => 'Pembayaran',
    'nav' => $participantNav,
])

@section('content')
<main class="mx-auto max-w-3xl space-y-6" x-data="{
    loading: true, submitting: false, loadError: '', fieldErrors: {},
    registration: null, invoice: null,
    async init() {
        const requestedId = new URLSearchParams(window.location.search).get('id_registration');
        const response = await window.asicApi.get('/api/registrations');
        if (!response.ok) { this.loadError = response.error || 'Data pendaftaran gagal dimuat.'; this.loading = false; return; }
        const registrations = response.data?.data?.data || [];
        this.registration = registrations.find(row => String(row.id_registration) === String(requestedId)) || registrations[0] || null;
        if (!this.registration) { this.loadError = 'Belum ada pendaftaran untuk ditagihkan.'; this.loading = false; return; }
        if (!this.registration.invoice?.id_invoice) { this.loadError = 'Invoice belum tersedia untuk pendaftaran ini.'; this.loading = false; return; }
        const invoiceResponse = await window.asicApi.get('/api/invoices/' + this.registration.invoice.id_invoice);
        this.loading = false;
        if (!invoiceResponse.ok) { this.loadError = invoiceResponse.error || 'Invoice gagal dimuat.'; return; }
        this.invoice = invoiceResponse.data?.data;
    },
    async uploadProof(event) {
        this.fieldErrors = {};
        this.submitting = true;
        const response = await window.asicApi.upload('/api/invoices/' + this.invoice.id_invoice + '/payment-proofs', new FormData(event.target));
        this.submitting = false;
        if (!response.ok) {
            this.fieldErrors = response.errors || {};
            window.asicApi.showToast(response.error || 'Bukti transfer gagal diunggah.');
            return;
        }
        this.invoice = { ...this.invoice, status_pembayaran: response.data?.data?.status_pembayaran || 'Menunggu Verifikasi' };
        event.target.reset();
        window.asicApi.showToast(response.data?.message || 'Bukti transfer berhasil dikirim.', 'success');
    },
    formatAmount(value) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0)); }
}">
    <header class="space-y-1">
        <h1 class="text-2xl font-bold text-on-surface">Pembayaran registrasi</h1>
        <p class="text-sm text-on-surface-variant">Pembayaran tahap ini dilakukan melalui transfer manual dan verifikasi bendahara.</p>
    </header>

    <div x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-5 text-sm">Memuat invoice…</div>
    <div x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-5 text-sm text-on-error-container"></div>

    <template x-if="invoice">
        <section class="space-y-5 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6">
            <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-on-surface-variant">Nomor invoice</dt><dd class="font-mono font-bold" x-text="invoice.nomor_invoice"></dd></div>
                <div><dt class="text-on-surface-variant">Status</dt><dd class="font-semibold" x-text="invoice.status_pembayaran"></dd></div>
                <div><dt class="text-on-surface-variant">Jatuh tempo</dt><dd x-text="invoice.tanggal_jatuh_tempo"></dd></div>
                <div><dt class="text-on-surface-variant">Total</dt><dd class="text-lg font-bold text-primary" x-text="formatAmount(invoice.total_tagihan)"></dd></div>
            </dl>
            <div class="rounded-xl bg-surface-container-low p-4 text-sm">
                <h2 class="font-bold">Instruksi transfer</h2>
                <p class="mt-1 whitespace-pre-line" x-text="invoice.payment_qr_info || 'Hubungi bendahara untuk mendapatkan informasi rekening pembayaran.'"></p>
            </div>
            <template x-if="invoice.status_pembayaran === 'Menunggu Pembayaran' || invoice.status_pembayaran === 'Ditolak'">
                <form class="space-y-4 border-t border-surface-container-highest pt-5" @submit.prevent="uploadProof($event)">
                    <h2 class="font-bold">Unggah bukti pembayaran</h2>
                    <label class="block text-sm font-semibold">Nominal transfer
                        <input type="number" name="nominal_transfer" min="1" step="1" required class="mt-1 block w-full rounded-xl border-surface-container-highest">
                    </label>
                    <p x-show="fieldErrors.nominal_transfer" x-text="fieldErrors.nominal_transfer?.[0]" class="text-sm text-error"></p>
                    <label class="block text-sm font-semibold">Bukti transfer (JPG, PNG, atau PDF)
                        <input type="file" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
                    </label>
                    <p x-show="fieldErrors.file_bukti" x-text="fieldErrors.file_bukti?.[0]" class="text-sm text-error"></p>
                    <label class="block text-sm font-semibold">Catatan
                        <textarea name="catatan_peserta" rows="2" class="mt-1 block w-full rounded-xl border-surface-container-highest"></textarea>
                    </label>
                    <p x-show="fieldErrors.catatan_peserta" x-text="fieldErrors.catatan_peserta?.[0]" class="text-sm text-error"></p>
                    <button type="submit" :disabled="submitting" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="submitting ? 'Mengirim…' : 'Kirim untuk verifikasi'"></button>
                </form>
            </template>
            <p x-show="invoice.status_pembayaran === 'Menunggu Verifikasi'" class="rounded-xl bg-secondary-container p-4 text-sm">Bukti sudah dikirim. Menunggu verifikasi bendahara.</p>
            <p x-show="invoice.status_pembayaran === 'Terverifikasi'" class="rounded-xl bg-secondary-container p-4 text-sm">Pembayaran terverifikasi. QR kehadiran tersedia pada halaman kehadiran.</p>
        </section>
    </template>
</main>
@endsection
