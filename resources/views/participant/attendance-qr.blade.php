@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Kehadiran', 'nav' => $participantNav])

@section('content')
<main class="mx-auto max-w-4xl space-y-6" x-data="{
    registrations: [], registration: null, qrToken: '', sessions: [],
    loading: true, scanning: false, loadError: '', scanError: '', fieldErrors: {}, sessionQrToken: '',
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/registrations');
        if (!response.ok) { this.loadError = response.error || 'Registrasi gagal dimuat.'; this.loading = false; return; }
        this.registrations = response.data?.data?.data || [];
        const selectedId = new URLSearchParams(window.location.search).get('id_registration');
        this.registration = this.registrations.find(row => String(row.id_registration) === String(selectedId)) || this.registrations[0] || null;
        if (!this.registration) { this.loadError = 'Belum ada registrasi aktif.'; this.loading = false; return; }
        const [qr, history] = await Promise.all([
            window.asicApi.get('/api/registrations/' + this.registration.id_registration + '/attendance-qr'),
            window.asicApi.get('/api/registrations/' + this.registration.id_registration + '/sessions')
        ]);
        if (qr.ok) this.qrToken = qr.data?.data?.qr_token || '';
        else if (qr.status !== 403) this.loadError = qr.error || 'QR peserta gagal dimuat.';
        if (history.ok) this.sessions = history.data?.data || [];
        else if (!this.loadError) this.loadError = history.error || 'Riwayat sesi gagal dimuat.';
        this.loading = false;
    },
    async scan() {
        this.scanError = ''; this.fieldErrors = {}; this.scanning = true;
        const response = await window.asicApi.post('/api/attendance/scan', { qr_token: this.sessionQrToken });
        this.scanning = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; this.scanError = response.error || 'Check-in gagal.'; return; }
        this.sessionQrToken = '';
        await this.load();
        window.asicApi.showToast(response.data?.message || 'Kehadiran sesi berhasil dicatat.', 'success');
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">QR kehadiran dan sesi</h1><p class="mt-1 text-sm text-on-surface-variant">QR peserta tersedia setelah pembayaran diverifikasi. Masukkan token QR yang ditampilkan pada sesi untuk check-in.</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat QR dan riwayat sesi…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <template x-if="registration">
        <section class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <h2 class="font-bold">QR peserta</h2>
                <p class="mt-1 text-xs text-on-surface-variant">Registrasi <span x-text="registration.id_registration"></span></p>
                <p x-show="!qrToken && !loading" class="mt-3 text-sm text-on-surface-variant">QR aktif setelah pembayaran terverifikasi.</p>
                <code x-show="qrToken" class="mt-4 block break-all rounded-xl bg-surface-container-low p-4 font-mono text-sm" x-text="qrToken"></code>
                <button x-show="qrToken" type="button" @click="navigator.clipboard?.writeText(qrToken)" class="mt-3 text-sm font-semibold text-primary">Salin token QR</button>
            </article>
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <h2 class="font-bold">Check-in sesi</h2>
                <form class="mt-3 space-y-3" @submit.prevent="scan()">
                    <label class="block text-sm font-semibold">Token QR sesi
                        <input x-model="sessionQrToken" required maxlength="100" class="mt-1 block w-full rounded-xl border-surface-container-highest font-mono text-sm">
                    </label>
                    <p x-show="fieldErrors.qr_token" x-text="fieldErrors.qr_token?.[0]" class="text-sm text-error"></p>
                    <p x-show="scanError" x-text="scanError" role="alert" class="text-sm text-error"></p>
                    <button type="submit" :disabled="scanning" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-50" x-text="scanning ? 'Memeriksa…' : 'Check-in' "></button>
                </form>
            </article>
        </section>
    </template>
    <section class="space-y-3">
        <h2 class="text-lg font-bold">Riwayat sesi</h2>
        <p x-show="!loading && sessions.length === 0" class="rounded-xl border border-surface-container-highest p-5 text-sm text-on-surface-variant">Belum ada kehadiran sesi.</p>
        <template x-for="session in sessions" :key="session.id_session">
            <article class="flex flex-wrap justify-between gap-3 rounded-xl border border-surface-container-highest bg-surface-container-lowest p-4 text-sm">
                <span class="font-semibold" x-text="session.nama_sesi"></span><span x-text="session.status_kehadiran || 'Belum hadir'"></span><span class="text-on-surface-variant" x-text="session.tanggal_check_in || session.waktu || ''"></span>
            </article>
        </template>
    </section>
</main>
@endsection
