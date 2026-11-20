@php($t = trans('asic-secretariat.attendance_page'))
@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => $t['title'], 'nav' => $secNav, 'title' => $t['title']])

@section('content')
<main class="space-y-6" x-data="{
    i18n: @js($t),
    events: [], sessions: [], attendees: [], sessionSummary: [], idEvent: '', idSession: '',
    loading: true, loadError: '', actionError: '',
    showCreate: false, creating: false, createErrors: {}, form: { nama_sesi: '', waktu: '', waktu_selesai: '' },
    projector: false, cameraOn: false, cameraError: '', scanResult: null,
    get session() { return this.sessions.find(s => String(s.id_session) === this.idSession) || null; },
    get checkinUrl() { return this.session ? window.location.origin + '/participant/attendance?sesi=' + encodeURIComponent(this.session.qr_token) : ''; },
    get sessionOpen() {
        if (!this.session) return false;
        const now = Date.now(), start = new Date(this.session.waktu).getTime(), end = new Date(this.session.waktu_selesai || this.session.waktu).getTime();
        return now >= start && now <= end;
    },
    async init() {
        const response = await window.asicApi.get('/api/content/events', { per_page: 100 });
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || i18n.events_failed; return; }
        this.events = response.data?.data || []; this.idEvent = String(this.events[0]?.id_event || '');
        if (this.idEvent) await this.loadSessions();
        await this.loadSummary();
    },
    async loadSessions(selectId = null) {
        this.loading = true; this.sessions = []; this.attendees = [];
        const response = await window.asicApi.get('/api/events/' + this.idEvent + '/sessions');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || i18n.sessions_failed; return; }
        this.sessions = response.data?.data || [];
        this.idSession = String(selectId || this.sessions[0]?.id_session || '');
        await this.selectSession();
        await this.loadSummary();
    },
    async selectSession() {
        this.scanResult = null;
        this.$nextTick(() => { if (this.checkinUrl && this.$refs.sessionQr) window.asicQr.render(this.$refs.sessionQr, this.checkinUrl, 200); });
        if (this.idSession) await this.loadAttendees();
    },
    async loadAttendees() {
        const response = await window.asicApi.get('/api/sessions/' + this.idSession + '/attendees');
        if (!response.ok) { this.loadError = response.error || i18n.attendees_failed; return; }
        this.attendees = response.data?.data?.attendances || [];
    },
    async loadSummary() {
        const response = await window.asicApi.get('/api/recap/attendance', this.idEvent ? { id_event: this.idEvent } : {});
        if (!response.ok) { this.loadError = response.error || i18n.summary_failed; return; }
        this.sessionSummary = response.data?.data?.sessions || [];
    },
    async createSession() {
        this.creating = true; this.createErrors = {};
        const response = await window.asicApi.post('/api/sessions', { id_event: Number(this.idEvent), ...this.form });
        this.creating = false;
        if (!response.ok) { this.createErrors = response.errors || {}; window.asicApi.showToast(response.error || i18n.create_failed); return; }
        this.form = { nama_sesi: '', waktu: '', waktu_selesai: '' }; this.showCreate = false;
        window.asicApi.showToast(i18n.created, 'success');
        await this.loadSessions(response.data?.data?.id_session);
    },
    openProjector() {
        this.projector = true;
        this.$nextTick(() => window.asicQr.render(this.$refs.bigQr, this.checkinUrl, Math.min(560, Math.floor(Math.min(window.innerWidth, window.innerHeight) * 0.62))));
    },
    downloadQr() { window.asicQr.download(this.$refs.sessionQr, 'qr-sesi-' + this.idSession + '.png'); },
    async openCamera() {
        this.cameraError = ''; this.scanResult = null; this.cameraOn = true;
        await this.$nextTick();
        try {
            await window.asicScanner.start('attendance-reader', async (text) => {
                const response = await window.asicApi.post('/api/sessions/' + this.idSession + '/attendance/scan', { qr_token: String(text || '').trim() });
                this.scanResult = { ok: response.ok, message: response.data?.message || response.error || (response.ok ? i18n.scan_ok : i18n.scan_rejected) };
                if (response.ok) await this.loadAttendees();
            }, true);
        } catch (error) {
            this.cameraOn = false;
            this.cameraError = i18n[window.asicScanner.errorMessage(error)] || i18n.camera_failed;
        }
    },
    async closeCamera() { await window.asicScanner.stop(); this.cameraOn = false; },
    async markPresented(attendance) {
        this.actionError = '';
        const response = await window.asicApi.patch('/api/attendance/' + attendance.id_attendance + '/presentation', { status_presentasi: 'Sudah' });
        if (!response.ok) { this.actionError = response.error || i18n.presentation_failed; window.asicApi.showToast(this.actionError); return; }
        attendance.status_presentasi = response.data?.data?.status_presentasi || 'Sudah';
        window.asicApi.showToast(response.data?.message || i18n.presentation_updated, 'success');
    }
}" @keydown.escape.window="projector = false" @beforeunload.window="window.asicScanner.stop()">
    <header class="rounded-2xl bg-surface-container-low p-6"><p class="text-xs font-bold uppercase tracking-widest text-primary">{{ $t['eyebrow'] }}</p><h1 class="mt-1 text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <p x-show="actionError" x-text="actionError" role="alert" class="text-sm text-error"></p>
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"><template x-for="summary in sessionSummary" :key="summary.id_session"><article class="rounded-xl border border-surface-container-high bg-surface-container-lowest p-4"><p class="text-xs text-on-surface-variant" x-text="summary.nama_event"></p><h2 class="mt-1 font-bold" x-text="summary.nama_sesi"></h2><div class="mt-3 flex justify-between text-sm"><span>Check-in</span><strong x-text="summary.check_ins"></strong></div><div class="flex justify-between text-sm"><span>{{ $t['presentations_done'] }}</span><strong x-text="summary.presentations_completed"></strong></div></article></template></section>

    <div class="grid items-end gap-4 sm:grid-cols-[1fr_1fr_auto]"><label class="text-sm font-semibold">{{ $t['event'] }}<select x-model="idEvent" @change="loadSessions()" class="mt-1 block w-full rounded-xl"><template x-for="event in events" :key="event.id_event"><option :value="String(event.id_event)" x-text="event.nama_event"></option></template></select></label>
        <label class="text-sm font-semibold">{{ $t['session'] }}<select x-model="idSession" @change="selectSession()" class="mt-1 block w-full rounded-xl"><template x-for="item in sessions" :key="item.id_session"><option :value="String(item.id_session)" x-text="item.nama_sesi + ' · ' + asicDateTime(item.waktu)"></option></template></select></label>
        <button type="button" @click="showCreate = !showCreate" :disabled="!idEvent" class="inline-flex h-11 items-center gap-1.5 rounded-xl border border-primary px-4 text-sm font-bold text-primary disabled:opacity-40"><span class="material-symbols-outlined text-[18px]">add</span>{{ $t['add_session'] }}</button>
    </div>

    <form x-show="showCreate" x-cloak @submit.prevent="createSession()" class="grid gap-3 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 sm:grid-cols-[2fr_1fr_1fr_auto] sm:items-end">
        <label class="text-sm font-semibold">{{ $t['session_name'] }}<input x-model="form.nama_sesi" required maxlength="150" placeholder="{{ $t['session_placeholder'] }}" class="mt-1 block w-full"><span x-show="createErrors.nama_sesi" x-text="createErrors.nama_sesi?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">{{ $t['start'] }}<input type="datetime-local" x-model="form.waktu" required class="mt-1 block w-full"><span x-show="createErrors.waktu" x-text="createErrors.waktu?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">{{ $t['end'] }}<input type="datetime-local" x-model="form.waktu_selesai" required class="mt-1 block w-full"><span x-show="createErrors.waktu_selesai" x-text="createErrors.waktu_selesai?.[0]" class="text-xs text-error"></span></label>
        <button type="submit" :disabled="creating" class="h-11 rounded-xl bg-primary px-5 text-sm font-bold text-white disabled:opacity-50" x-text="creating ? i18n.saving : i18n.save_session"></button>
    </form>

    <section x-show="session" class="grid gap-5 lg:grid-cols-2">
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5">
            <div class="flex items-start justify-between gap-3">
                <div><h2 class="font-bold">{{ $t['qr_title'] }}</h2><p class="mt-0.5 text-xs text-on-surface-variant" x-text="session ? session.nama_sesi + ' · ' + asicDateTime(session.waktu) + ' – ' + asicDateTime(session.waktu_selesai) : ''"></p></div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold" :class="sessionOpen ? 'bg-[#E6F4EA] text-primary' : 'bg-surface-container-high text-on-surface-variant'" x-text="sessionOpen ? i18n.live : i18n.outside"></span>
            </div>
            <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <canvas x-ref="sessionQr" width="200" height="200" class="rounded-xl border border-surface-container-high bg-white p-1" role="img" aria-label="{{ $t['qr_alt'] }}"></canvas>
                <div class="space-y-2 text-sm text-on-surface-variant">
                    <p>{!! $t['qr_help'] !!}</p>
                    <p x-show="!sessionOpen" class="text-xs">{{ $t['qr_only_open'] }}</p>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button type="button" @click="openProjector()" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white"><span class="material-symbols-outlined text-[18px]">fullscreen</span>{{ $t['fullscreen'] }}</button>
                        <button type="button" @click="downloadQr()" class="inline-flex items-center gap-1.5 rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold"><span class="material-symbols-outlined text-[18px]">download</span>{{ $t['download'] }}</button>
                    </div>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div><h2 class="font-bold">{{ $t['scan_title'] }}</h2><p class="mt-0.5 text-xs text-on-surface-variant">{{ $t['scan_help'] }}</p></div>
                <button type="button" x-show="!cameraOn" @click="openCamera()" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white"><span class="material-symbols-outlined text-[18px]">photo_camera</span>{{ $t['open_camera'] }}</button>
                <button type="button" x-show="cameraOn" @click="closeCamera()" class="inline-flex items-center gap-1.5 rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-bold"><span class="material-symbols-outlined text-[18px]">close</span>{{ $t['close_camera'] }}</button>
            </div>
            <div x-show="cameraOn" class="mt-3 overflow-hidden rounded-xl bg-black"><div id="attendance-reader" class="w-full"></div></div>
            <p x-show="cameraError" x-text="cameraError" role="alert" class="mt-3 rounded-xl bg-error-container p-3 text-sm text-on-error-container"></p>
            <p x-show="scanResult" x-text="scanResult?.message" role="status" class="mt-3 rounded-xl p-3 text-sm font-semibold" :class="scanResult?.ok ? 'bg-[#E6F4EA] text-primary' : 'bg-error-container text-on-error-container'"></p>
        </article>
    </section>

    <p x-show="!loading && idEvent && sessions.length === 0" class="rounded-xl border border-surface-container-highest p-6 text-center text-sm text-on-surface-variant">{!! $t['no_sessions'] !!}</p>
    <p x-show="!loading && idSession && attendees.length === 0" class="rounded-xl border border-surface-container-highest p-6 text-center text-sm text-on-surface-variant">{{ $t['no_checkins'] }}</p>
    <div x-show="attendees.length" class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest"><table class="w-full text-left text-sm"><thead><tr class="border-b border-surface-container-high"><th class="p-4">{{ $t['col_participant'] }}</th><th class="p-4">{{ $t['col_attendance'] }}</th><th class="p-4">{{ $t['col_presentation'] }}</th><th class="p-4">{{ $t['col_time'] }}</th><th class="p-4">{{ $t['col_action'] }}</th></tr></thead><tbody><template x-for="row in attendees" :key="row.id_attendance"><tr class="border-b border-surface-container-highest"><td class="p-4" x-text="row.registration?.participant?.full_name || i18n.col_participant"></td><td class="p-4" x-text="i18n.statuses[row.status_kehadiran] || row.status_kehadiran"></td><td class="p-4" x-text="i18n.statuses[row.status_presentasi || 'Belum'] || row.status_presentasi"></td><td class="p-4" x-text="asicDateTime(row.tanggal_check_in)"></td><td class="p-4"><button x-show="row.status_presentasi !== 'Sudah'" type="button" @click="markPresented(row)" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white">{{ $t['mark_presented'] }}</button><span x-show="row.status_presentasi === 'Sudah'" class="text-xs text-on-surface-variant">{{ $t['done'] }}</span></td></tr></template></tbody></table></div>

    {{-- Layar penuh untuk proyektor / berbagi layar Zoom --}}
    <div x-show="projector" x-cloak class="fixed inset-0 z-[80] flex flex-col items-center justify-center gap-5 bg-white p-6 text-center" role="dialog" aria-modal="true" aria-label="{{ $t['projector_aria'] }}">
        <button type="button" @click="projector = false" class="absolute right-5 top-5 inline-flex h-11 w-11 items-center justify-center rounded-full bg-surface-container-high" aria-label="{{ $t['close'] }}"><span class="material-symbols-outlined">close</span></button>
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">{{ config('asic.event_short_name', '4th ASIC 2027') }} · {{ $t['projector_label'] }}</p>
        <h2 class="font-serif text-3xl font-semibold text-[#182820] md:text-4xl" x-text="session?.nama_sesi"></h2>
        <canvas x-ref="bigQr" class="rounded-2xl border border-surface-container-high bg-white p-2" role="img" aria-label="{{ $t['qr_alt'] }}"></canvas>
        <p class="max-w-xl text-lg text-on-surface-variant">{!! $t['projector_help'] !!}</p>
    </div>
</main>
@endsection
