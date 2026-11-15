@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => 'Check-in peserta', 'nav' => $secNav])

@section('content')
<main class="mx-auto max-w-5xl space-y-6" x-data="{
    events: [], sessions: [], attendees: [], idEvent: '', idSession: '', qrToken: '',
    loading: true, scanning: false, loadError: '', scanError: '', fieldErrors: {},
    async init() {
        const response = await window.asicApi.get('/api/public/events');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Daftar event gagal dimuat.'; return; }
        this.events = response.data?.data || [];
        this.idEvent = String(this.events[0]?.id_event || '');
        if (this.idEvent) await this.loadSessions();
    },
    async loadSessions() {
        this.loading = true; this.sessions = []; this.attendees = [];
        const response = await window.asicApi.get('/api/events/' + this.idEvent + '/sessions');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Sesi event gagal dimuat.'; return; }
        this.sessions = response.data?.data || [];
        this.idSession = String(this.sessions[0]?.id_session || '');
        if (this.idSession) await this.loadAttendees();
    },
    async loadAttendees() {
        const response = await window.asicApi.get('/api/sessions/' + this.idSession + '/attendees');
        if (!response.ok) { this.loadError = response.error || 'Daftar kehadiran gagal dimuat.'; return; }
        this.attendees = response.data?.data?.attendances || [];
    },
    async scan() {
        this.scanError = ''; this.fieldErrors = {}; this.scanning = true;
        const response = await window.asicApi.post('/api/sessions/' + this.idSession + '/attendance/scan', { qr_token: this.qrToken });
        this.scanning = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; this.scanError = response.error || 'QR gagal dipindai.'; return; }
        this.qrToken = '';
        await this.loadAttendees();
        window.asicApi.showToast(response.data?.message || 'Check-in berhasil dicatat.', 'success');
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">Registrasi ulang dan scan QR</h1><p class="mt-1 text-sm text-on-surface-variant">Pindai token QR peserta yang pembayarannya sudah terverifikasi untuk mencatat check-in pada sesi aktif.</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat event dan sesi…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <section class="grid gap-5 lg:grid-cols-2">
        <article class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <label class="block text-sm font-semibold">Event
                <select x-model="idEvent" @change="loadSessions()" class="mt-1 block w-full rounded-xl border-surface-container-highest"><template x-for="event in events" :key="event.id_event"><option :value="String(event.id_event)" x-text="event.nama_event"></option></template></select>
            </label>
            <label class="block text-sm font-semibold">Sesi
                <select x-model="idSession" @change="loadAttendees()" class="mt-1 block w-full rounded-xl border-surface-container-highest"><template x-for="session in sessions" :key="session.id_session"><option :value="String(session.id_session)" x-text="session.nama_sesi"></option></template></select>
            </label>
            <form class="space-y-3 border-t border-surface-container-highest pt-4" @submit.prevent="scan()">
                <label class="block text-sm font-semibold">Token QR peserta
                    <input x-model="qrToken" required class="mt-1 block w-full rounded-xl border-surface-container-highest font-mono">
                </label>
                <p x-show="fieldErrors.qr_token" x-text="fieldErrors.qr_token?.[0]" class="text-sm text-error"></p>
                <p x-show="scanError" x-text="scanError" role="alert" class="text-sm text-error"></p>
                <button type="submit" :disabled="scanning || !idSession" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-50" x-text="scanning ? 'Memeriksa…' : 'Catat check-in'"></button>
            </form>
        </article>
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <h2 class="font-bold">Peserta hadir</h2>
            <p x-show="!loading && !idSession" class="mt-2 text-sm text-on-surface-variant">Belum ada sesi untuk event ini.</p>
            <p x-show="idSession && attendees.length === 0" class="mt-2 text-sm text-on-surface-variant">Belum ada check-in pada sesi ini.</p>
            <ul class="mt-3 divide-y divide-surface-container-highest"><template x-for="attendee in attendees" :key="attendee.id_attendance"><li class="py-3 text-sm"><span class="font-semibold" x-text="attendee.registration?.participant?.full_name || 'Peserta' "></span><span class="ml-2 text-xs text-on-surface-variant" x-text="attendee.status_kehadiran"></span></li></template></ul>
        </article>
    </section>
    <aside class="rounded-xl border border-surface-container-highest bg-surface-container-low p-4 text-sm text-on-surface-variant">Override check-in supervisor, cetak badge, penerbitan kit, dan pencarian nomor registrasi belum tersedia.</aside>
</main>
@endsection
