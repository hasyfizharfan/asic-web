@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => 'Kehadiran', 'nav' => $secNav])

@section('content')
<main class="space-y-6" x-data="{
    events: [], sessions: [], attendees: [], idEvent: '', idSession: '',
    loading: true, loadError: '', actionError: '',
    async init() {
        const response = await window.asicApi.get('/api/public/events');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Event gagal dimuat.'; return; }
        this.events = response.data?.data || []; this.idEvent = String(this.events[0]?.id_event || '');
        if (this.idEvent) await this.loadSessions();
    },
    async loadSessions() {
        this.loading = true; this.sessions = []; this.attendees = [];
        const response = await window.asicApi.get('/api/events/' + this.idEvent + '/sessions');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Sesi gagal dimuat.'; return; }
        this.sessions = response.data?.data || []; this.idSession = String(this.sessions[0]?.id_session || '');
        if (this.idSession) await this.loadAttendees();
    },
    async loadAttendees() {
        const response = await window.asicApi.get('/api/sessions/' + this.idSession + '/attendees');
        if (!response.ok) { this.loadError = response.error || 'Kehadiran gagal dimuat.'; return; }
        this.attendees = response.data?.data?.attendances || [];
    },
    async markPresented(attendance) {
        this.actionError = '';
        const response = await window.asicApi.patch('/api/attendance/' + attendance.id_attendance + '/presentation', { status_presentasi: 'Sudah' });
        if (!response.ok) { this.actionError = response.error || 'Status presentasi gagal diperbarui.'; window.asicApi.showToast(this.actionError); return; }
        attendance.status_presentasi = response.data?.data?.status_presentasi || 'Sudah';
        window.asicApi.showToast(response.data?.message || 'Status presentasi diperbarui.', 'success');
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">Kehadiran dan presentasi</h1><p class="mt-1 text-sm text-on-surface-variant">Daftar peserta dan status presentasi dimuat dari sesi event.</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat kehadiran…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <p x-show="actionError" x-text="actionError" role="alert" class="text-sm text-error"></p>
    <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold">Event<select x-model="idEvent" @change="loadSessions()" class="mt-1 block w-full rounded-xl border-surface-container-highest"><template x-for="event in events" :key="event.id_event"><option :value="String(event.id_event)" x-text="event.nama_event"></option></template></select></label>
        <label class="text-sm font-semibold">Sesi<select x-model="idSession" @change="loadAttendees()" class="mt-1 block w-full rounded-xl border-surface-container-highest"><template x-for="session in sessions" :key="session.id_session"><option :value="String(session.id_session)" x-text="session.nama_sesi"></option></template></select></label>
    </div>
    <p x-show="!loading && idSession && attendees.length === 0" class="rounded-xl border border-surface-container-highest p-6 text-center text-sm text-on-surface-variant">Belum ada peserta check-in pada sesi ini.</p>
    <div x-show="attendees.length" class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest"><table class="w-full text-left text-sm"><thead><tr class="border-b border-surface-container-high"><th class="p-4">Peserta</th><th class="p-4">Kehadiran</th><th class="p-4">Presentasi</th><th class="p-4">Waktu check-in</th><th class="p-4">Aksi</th></tr></thead><tbody><template x-for="row in attendees" :key="row.id_attendance"><tr class="border-b border-surface-container-highest"><td class="p-4" x-text="row.registration?.participant?.full_name || 'Peserta'"></td><td class="p-4" x-text="row.status_kehadiran"></td><td class="p-4" x-text="row.status_presentasi || 'Belum'"></td><td class="p-4" x-text="row.tanggal_check_in || '—'"></td><td class="p-4"><button x-show="row.status_presentasi !== 'Sudah'" type="button" @click="markPresented(row)" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white">Tandai presentasi</button><span x-show="row.status_presentasi === 'Sudah'" class="text-xs text-on-surface-variant">Selesai</span></td></tr></template></tbody></table></div>
</main>
@endsection
