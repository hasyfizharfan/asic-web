@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => 'Kehadiran', 'nav' => $secNav])

@section('content')
<main class="space-y-6" x-data="{
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
        if (!response.ok) { this.loadError = response.error || 'Event gagal dimuat.'; return; }
        this.events = response.data?.data || []; this.idEvent = String(this.events[0]?.id_event || '');
        if (this.idEvent) await this.loadSessions();
        await this.loadSummary();
    },
    async loadSessions(selectId = null) {
        this.loading = true; this.sessions = []; this.attendees = [];
        const response = await window.asicApi.get('/api/events/' + this.idEvent + '/sessions');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Sesi gagal dimuat.'; return; }
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
        if (!response.ok) { this.loadError = response.error || 'Kehadiran gagal dimuat.'; return; }
        this.attendees = response.data?.data?.attendances || [];
    },
    async loadSummary() {
        const response = await window.asicApi.get('/api/recap/attendance', this.idEvent ? { id_event: this.idEvent } : {});
        if (!response.ok) { this.loadError = response.error || 'Rekap per sesi gagal dimuat.'; return; }
        this.sessionSummary = response.data?.data?.sessions || [];
    },
    async createSession() {
        this.creating = true; this.createErrors = {};
        const response = await window.asicApi.post('/api/sessions', { id_event: Number(this.idEvent), ...this.form });
        this.creating = false;
        if (!response.ok) { this.createErrors = response.errors || {}; window.asicApi.showToast(response.error || 'Sesi gagal dibuat.'); return; }
        this.form = { nama_sesi: '', waktu: '', waktu_selesai: '' }; this.showCreate = false;
        window.asicApi.showToast('Sesi dibuat. QR sesi siap ditampilkan.', 'success');
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
                this.scanResult = { ok: response.ok, message: response.data?.message || response.error || (response.ok ? 'Kehadiran tercatat.' : 'QR ditolak.') };
                if (response.ok) await this.loadAttendees();
            }, true);
        } catch (error) {
            this.cameraOn = false;
            this.cameraError = { camera_https: 'Kamera hanya bisa dibuka di alamat aman (https).', camera_denied: 'Akses kamera ditolak. Izinkan kamera untuk situs ini di pengaturan browser.', camera_missing: 'Kamera tidak ditemukan di perangkat ini.' }[window.asicScanner.errorMessage(error)] || 'Kamera gagal dibuka.';
        }
    },
    async closeCamera() { await window.asicScanner.stop(); this.cameraOn = false; },
    async markPresented(attendance) {
        this.actionError = '';
        const response = await window.asicApi.patch('/api/attendance/' + attendance.id_attendance + '/presentation', { status_presentasi: 'Sudah' });
        if (!response.ok) { this.actionError = response.error || 'Status presentasi gagal diperbarui.'; window.asicApi.showToast(this.actionError); return; }
        attendance.status_presentasi = response.data?.data?.status_presentasi || 'Sudah';
        window.asicApi.showToast(response.data?.message || 'Status presentasi diperbarui.', 'success');
    }
}" @keydown.escape.window="projector = false" @beforeunload.window="window.asicScanner.stop()">
    <header class="rounded-2xl bg-surface-container-low p-6"><p class="text-xs font-bold uppercase tracking-widest text-primary">Operasional acara</p><h1 class="mt-1 text-2xl font-bold text-on-surface">Kehadiran dan presentasi</h1><p class="mt-1 text-sm text-on-surface-variant">Tampilkan QR sesi di layar ruangan/Zoom agar peserta memindainya sendiri dengan HP. Peserta luring juga bisa dipindai panitia dari QR registrasi ulang miliknya.</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat kehadiran…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <p x-show="actionError" x-text="actionError" role="alert" class="text-sm text-error"></p>
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"><template x-for="summary in sessionSummary" :key="summary.id_session"><article class="rounded-xl border border-surface-container-high bg-surface-container-lowest p-4"><p class="text-xs text-on-surface-variant" x-text="summary.nama_event"></p><h2 class="mt-1 font-bold" x-text="summary.nama_sesi"></h2><div class="mt-3 flex justify-between text-sm"><span>Check-in</span><strong x-text="summary.check_ins"></strong></div><div class="flex justify-between text-sm"><span>Presentasi selesai</span><strong x-text="summary.presentations_completed"></strong></div></article></template></section>

    <div class="grid items-end gap-4 sm:grid-cols-[1fr_1fr_auto]"><label class="text-sm font-semibold">Event<select x-model="idEvent" @change="loadSessions()" class="mt-1 block w-full rounded-xl"><template x-for="event in events" :key="event.id_event"><option :value="String(event.id_event)" x-text="event.nama_event"></option></template></select></label>
        <label class="text-sm font-semibold">Sesi<select x-model="idSession" @change="selectSession()" class="mt-1 block w-full rounded-xl"><template x-for="item in sessions" :key="item.id_session"><option :value="String(item.id_session)" x-text="item.nama_sesi + ' · ' + asicDateTime(item.waktu)"></option></template></select></label>
        <button type="button" @click="showCreate = !showCreate" :disabled="!idEvent" class="inline-flex h-11 items-center gap-1.5 rounded-xl border border-primary px-4 text-sm font-bold text-primary disabled:opacity-40"><span class="material-symbols-outlined text-[18px]">add</span>Tambah sesi</button>
    </div>

    <form x-show="showCreate" x-cloak @submit.prevent="createSession()" class="grid gap-3 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 sm:grid-cols-[2fr_1fr_1fr_auto] sm:items-end">
        <label class="text-sm font-semibold">Nama sesi<input x-model="form.nama_sesi" required maxlength="150" placeholder="Contoh: Sesi Paralel 1 - Ruang A" class="mt-1 block w-full"><span x-show="createErrors.nama_sesi" x-text="createErrors.nama_sesi?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Mulai<input type="datetime-local" x-model="form.waktu" required class="mt-1 block w-full"><span x-show="createErrors.waktu" x-text="createErrors.waktu?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Selesai<input type="datetime-local" x-model="form.waktu_selesai" required class="mt-1 block w-full"><span x-show="createErrors.waktu_selesai" x-text="createErrors.waktu_selesai?.[0]" class="text-xs text-error"></span></label>
        <button type="submit" :disabled="creating" class="h-11 rounded-xl bg-primary px-5 text-sm font-bold text-white disabled:opacity-50" x-text="creating ? 'Menyimpan…' : 'Simpan sesi'"></button>
    </form>

    <section x-show="session" class="grid gap-5 lg:grid-cols-2">
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5">
            <div class="flex items-start justify-between gap-3">
                <div><h2 class="font-bold">QR sesi untuk peserta</h2><p class="mt-0.5 text-xs text-on-surface-variant" x-text="session ? session.nama_sesi + ' · ' + asicDateTime(session.waktu) + ' s.d. ' + asicDateTime(session.waktu_selesai) : ''"></p></div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold" :class="sessionOpen ? 'bg-[#E6F4EA] text-primary' : 'bg-surface-container-high text-on-surface-variant'" x-text="sessionOpen ? 'Sedang berlangsung' : 'Di luar jadwal'"></span>
            </div>
            <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <canvas x-ref="sessionQr" width="200" height="200" class="rounded-xl border border-surface-container-high bg-white p-1" role="img" aria-label="QR sesi"></canvas>
                <div class="space-y-2 text-sm text-on-surface-variant">
                    <p>Tampilkan QR ini di proyektor atau bagikan layar di Zoom. Peserta memindainya dari menu <b>Kehadiran</b> atau dengan kamera HP biasa, lalu kehadiran langsung tercatat.</p>
                    <p x-show="!sessionOpen" class="text-xs">QR hanya diterima selama jam sesi.</p>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button type="button" @click="openProjector()" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white"><span class="material-symbols-outlined text-[18px]">fullscreen</span>Tampilkan layar penuh</button>
                        <button type="button" @click="downloadQr()" class="inline-flex items-center gap-1.5 rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold"><span class="material-symbols-outlined text-[18px]">download</span>Unduh</button>
                    </div>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div><h2 class="font-bold">Pindai QR peserta luring</h2><p class="mt-0.5 text-xs text-on-surface-variant">Untuk peserta yang tidak membawa HP: panitia memindai QR registrasi ulang peserta. Kamera tetap menyala untuk peserta berikutnya.</p></div>
                <button type="button" x-show="!cameraOn" @click="openCamera()" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white"><span class="material-symbols-outlined text-[18px]">photo_camera</span>Buka kamera</button>
                <button type="button" x-show="cameraOn" @click="closeCamera()" class="inline-flex items-center gap-1.5 rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-bold"><span class="material-symbols-outlined text-[18px]">close</span>Tutup kamera</button>
            </div>
            <div x-show="cameraOn" class="mt-3 overflow-hidden rounded-xl bg-black"><div id="attendance-reader" class="w-full"></div></div>
            <p x-show="cameraError" x-text="cameraError" role="alert" class="mt-3 rounded-xl bg-error-container p-3 text-sm text-on-error-container"></p>
            <p x-show="scanResult" x-text="scanResult?.message" role="status" class="mt-3 rounded-xl p-3 text-sm font-semibold" :class="scanResult?.ok ? 'bg-[#E6F4EA] text-primary' : 'bg-error-container text-on-error-container'"></p>
        </article>
    </section>

    <p x-show="!loading && idEvent && sessions.length === 0" class="rounded-xl border border-surface-container-highest p-6 text-center text-sm text-on-surface-variant">Belum ada sesi. Tekan <b>Tambah sesi</b> untuk membuat sesi dan QR-nya.</p>
    <p x-show="!loading && idSession && attendees.length === 0" class="rounded-xl border border-surface-container-highest p-6 text-center text-sm text-on-surface-variant">Belum ada peserta check-in pada sesi ini.</p>
    <div x-show="attendees.length" class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest"><table class="w-full text-left text-sm"><thead><tr class="border-b border-surface-container-high"><th class="p-4">Peserta</th><th class="p-4">Kehadiran</th><th class="p-4">Presentasi</th><th class="p-4">Waktu check-in</th><th class="p-4">Aksi</th></tr></thead><tbody><template x-for="row in attendees" :key="row.id_attendance"><tr class="border-b border-surface-container-highest"><td class="p-4" x-text="row.registration?.participant?.full_name || 'Peserta'"></td><td class="p-4" x-text="row.status_kehadiran"></td><td class="p-4" x-text="row.status_presentasi || 'Belum'"></td><td class="p-4" x-text="asicDateTime(row.tanggal_check_in)"></td><td class="p-4"><button x-show="row.status_presentasi !== 'Sudah'" type="button" @click="markPresented(row)" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white">Tandai presentasi</button><span x-show="row.status_presentasi === 'Sudah'" class="text-xs text-on-surface-variant">Selesai</span></td></tr></template></tbody></table></div>

    {{-- Layar penuh untuk proyektor / berbagi layar Zoom --}}
    <div x-show="projector" x-cloak class="fixed inset-0 z-[80] flex flex-col items-center justify-center gap-5 bg-white p-6 text-center" role="dialog" aria-modal="true" aria-label="QR sesi layar penuh">
        <button type="button" @click="projector = false" class="absolute right-5 top-5 inline-flex h-11 w-11 items-center justify-center rounded-full bg-surface-container-high" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">4th ASIC 2027 · Absen sesi</p>
        <h2 class="font-serif text-3xl font-semibold text-[#182820] md:text-4xl" x-text="session?.nama_sesi"></h2>
        <canvas x-ref="bigQr" class="rounded-2xl border border-surface-container-high bg-white p-2" role="img" aria-label="QR sesi"></canvas>
        <p class="max-w-xl text-lg text-on-surface-variant">Pindai dengan kamera HP atau dari menu <b>Kehadiran</b> di portal peserta.</p>
    </div>
</main>
@endsection
