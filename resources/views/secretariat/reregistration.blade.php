@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => 'Registrasi ulang', 'nav' => $secNav, 'title' => 'Registrasi ulang'])

@section('content')
<main class="mx-auto max-w-6xl space-y-6" x-data="{
    events: [], sessions: [], query: '', results: [], selected: null, attendees: [],
    idEvent: '', idSession: '', qrToken: '', loading: false, searching: false, error: '', actionError: '',
    async init() {
        const response = await window.asicApi.get('/api/content/events', { per_page: 100 });
        if (!response.ok) { this.error = response.error || 'Event gagal dimuat.'; return; }
        this.events = response.data?.data || [];
        this.idEvent = String(this.events[0]?.id_event || '');
        if (this.idEvent) await this.loadSessions();
    },
    async search() {
        this.error = ''; this.selected = null; this.results = [];
        if (this.query.trim().length < 2) { this.error = 'Masukkan minimal 2 karakter.'; return; }
        this.searching = true;
        const response = await window.asicApi.get('/api/registrations/search', { q: this.query.trim() });
        this.searching = false;
        if (!response.ok) { this.error = response.error || 'Pencarian registrasi gagal.'; return; }
        this.results = response.data?.data || [];
    },
    async loadSessions() {
        this.sessions = []; this.attendees = [];
        const response = await window.asicApi.get('/api/events/' + this.idEvent + '/sessions');
        if (!response.ok) { this.error = response.error || 'Sesi gagal dimuat.'; return; }
        this.sessions = response.data?.data || [];
        this.idSession = String(this.sessions[0]?.id_session || '');
        if (this.idSession) await this.loadAttendees();
    },
    async loadAttendees() {
        const response = await window.asicApi.get('/api/sessions/' + this.idSession + '/attendees');
        if (!response.ok) { this.error = response.error || 'Kehadiran sesi gagal dimuat.'; return; }
        this.attendees = response.data?.data?.attendances || [];
    },
    async checkIn() {
        this.actionError = '';
        const response = await window.asicApi.post('/api/sessions/' + this.idSession + '/attendance', { id_registration: this.selected.id_registration });
        if (!response.ok) { this.actionError = response.error || 'Check-in gagal.'; return; }
        await this.loadAttendees();
        window.asicApi.showToast(response.data?.message || 'Check-in berhasil dicatat.', 'success');
    },
    async scan() {
        this.actionError = '';
        const response = await window.asicApi.post('/api/sessions/' + this.idSession + '/attendance/scan', { qr_token: this.qrToken });
        if (!response.ok) { this.actionError = response.error || 'QR gagal dipindai.'; return; }
        this.qrToken = ''; await this.loadAttendees();
        window.asicApi.showToast(response.data?.message || 'Check-in berhasil dicatat.', 'success');
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-surface-container-low p-6">
        <div><p class="text-xs font-bold uppercase tracking-widest text-primary">Meja sekretariat</p><h1 class="mt-1 text-2xl font-bold">Registrasi ulang peserta</h1><p class="mt-1 text-sm text-on-surface-variant">Cari berdasarkan nama, email, atau ID registrasi; catat kehadiran pada sesi aktif.</p></div>
        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container">Data langsung</span>
    </header>
    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <p x-show="actionError" x-text="actionError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <section class="grid gap-5 lg:grid-cols-[1.1fr_.9fr]">
        <article class="space-y-5 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm">
            <div><h2 class="font-bold">Pencarian peserta</h2><form class="mt-3 flex gap-2" @submit.prevent="search()"><input x-model="query" minlength="2" placeholder="Nama, email, atau ID registrasi" class="min-w-0 flex-1 rounded-xl border-surface-container-highest"><button :disabled="searching" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-50" x-text="searching ? 'Mencari…' : 'Cari'"></button></form></div>
            <div x-show="results.length" class="space-y-2"><template x-for="row in results" :key="row.id_registration"><button type="button" @click="selected=row" class="w-full rounded-xl border border-surface-container-high p-3 text-left hover:bg-surface-container-low"><span class="flex justify-between gap-2"><strong x-text="row.participant?.full_name"></strong><span class="text-xs text-on-surface-variant" x-text="'#' + row.id_registration"></span></span><span class="mt-1 block text-xs text-on-surface-variant" x-text="(row.participant?.email || '') + ' · ' + (row.event || '')"></span></button></template></div>
            <p x-show="query.length >= 2 && results.length === 0 && !searching && !error" class="rounded-xl bg-surface-container-low p-4 text-sm text-on-surface-variant">Tidak ada registrasi yang cocok.</p>
            <section x-show="selected" class="space-y-3 rounded-xl bg-surface-container-low p-4">
                <div><h3 class="font-bold" x-text="selected?.participant?.full_name"></h3><p class="text-xs text-on-surface-variant" x-text="selected?.participant?.institution || 'Institusi tidak tersedia'"></p></div>
                <div class="grid gap-3 text-sm sm:grid-cols-3"><div><span class="block text-xs text-on-surface-variant">Registrasi</span><strong x-text="selected?.registration_status"></strong></div><div><span class="block text-xs text-on-surface-variant">Pembayaran</span><strong x-text="selected?.invoice?.status_pembayaran || 'Belum ada invoice'"></strong></div><div><span class="block text-xs text-on-surface-variant">Abstrak</span><strong x-text="selected?.abstract?.status_abstrak || 'Belum ada abstrak'"></strong></div></div>
                <p class="text-sm" x-text="selected?.abstract?.judul_abstrak || 'Judul abstrak belum tersedia.'"></p>
                <button type="button" @click="checkIn()" :disabled="!idSession || selected?.invoice?.status_pembayaran !== 'Terverifikasi'" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-40">Catat check-in sesi aktif</button>
            </section>
        </article>
        <article class="space-y-4 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-bold">Sesi dan pemindaian QR</h2>
            <label class="block text-sm font-semibold">Event<select x-model="idEvent" @change="loadSessions()" class="mt-1 block w-full rounded-xl border-surface-container-highest"><template x-for="event in events" :key="event.id_event"><option :value="String(event.id_event)" x-text="event.nama_event"></option></template></select></label>
            <label class="block text-sm font-semibold">Sesi<select x-model="idSession" @change="loadAttendees()" class="mt-1 block w-full rounded-xl border-surface-container-highest"><template x-for="session in sessions" :key="session.id_session"><option :value="String(session.id_session)" x-text="session.nama_sesi"></option></template></select></label>
            <form class="flex gap-2 border-t border-surface-container-high pt-4" @submit.prevent="scan()"><input x-model="qrToken" required placeholder="Token QR peserta" class="min-w-0 flex-1 rounded-xl border-surface-container-highest font-mono"><button :disabled="!idSession" class="rounded-xl border border-primary px-4 py-2 text-sm font-bold text-primary disabled:opacity-40">Pindai</button></form>
            <p class="text-xs text-on-surface-variant" x-text="attendees.length + ' peserta check-in pada sesi ini'"></p>
            <div class="grid grid-cols-3 gap-2 text-center text-xs"><button disabled class="rounded-lg border p-2 opacity-60">Override · Belum tersedia</button><button disabled class="rounded-lg border p-2 opacity-60">Cetak badge · Belum tersedia</button><button disabled class="rounded-lg border p-2 opacity-60">Issue kit · Belum tersedia</button></div>
        </article>
    </section>
</main>
@endsection
