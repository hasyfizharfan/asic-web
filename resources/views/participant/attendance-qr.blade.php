@php($t = fn ($k) => __('asic-participant.attendance_qr.'.$k))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.attendance_qr.title'), 'nav' => $participantNav, 'title' => __('asic-participant.attendance_qr.title')])

@section('content')
<main class="mx-auto max-w-5xl space-y-6" x-data="{
    i18n: @js(trans('asic-participant.attendance_qr')),
    registrations: [], registration: null, qrToken: '', qrBlocked: '', sessions: [],
    loading: true, scanning: false, loadError: '', scanError: '', fieldErrors: {}, sessionQrToken: '',
    cameraOn: false, cameraStarting: false, cameraError: '', lastSuccess: '',
    get isOnline() { return this.registration?.attendance_mode === 'Daring'; },
    async init() {
        await this.load();
        // QR sesi berisi tautan ...?sesi=TOKEN: dibuka dari kamera HP biasa langsung dicatat.
        const params = new URLSearchParams(window.location.search);
        const fromLink = params.get('sesi');
        if (fromLink) {
            params.delete('sesi');
            const rest = params.toString();
            window.history.replaceState({}, '', window.location.pathname + (rest ? '?' + rest : ''));
            if (this.registration) { this.sessionQrToken = fromLink; await this.scan(); }
        }
    },
    async load() {
        this.loading = true; this.loadError = ''; this.qrBlocked = ''; this.qrToken = '';
        const response = await window.asicApi.get('/api/registrations');
        if (!response.ok) { this.loadError = response.error || this.i18n.load_failed; this.loading = false; return; }
        this.registrations = response.data?.data?.data || [];
        const selectedId = new URLSearchParams(window.location.search).get('id_registration');
        this.registration = this.registrations.find(row => String(row.id_registration) === String(selectedId)) || this.registrations[0] || null;
        if (!this.registration) { this.loadError = this.i18n.no_registration; this.loading = false; return; }
        const [qr, history] = await Promise.all([
            this.isOnline ? Promise.resolve({ ok: false, status: 403 }) : window.asicApi.get('/api/registrations/' + this.registration.id_registration + '/attendance-qr'),
            window.asicApi.get('/api/registrations/' + this.registration.id_registration + '/sessions')
        ]);
        if (qr.ok) { this.qrToken = qr.data?.data?.qr_token || ''; }
        else if (qr.status === 403) { this.qrBlocked = qr.error || ''; }
        else { this.loadError = qr.error || this.i18n.qr_failed; }
        if (history.ok) this.sessions = history.data?.data || [];
        this.loading = false;
        this.$nextTick(() => { if (this.qrToken && this.$refs.qr) window.asicQr.render(this.$refs.qr, this.qrToken, 220); });
    },
    download() { window.asicQr.download(this.$refs.qr, 'qr-registrasi-ulang-' + this.registration.id_registration + '.png'); },
    async openCamera() {
        this.cameraError = ''; this.scanError = ''; this.lastSuccess = ''; this.cameraStarting = true; this.cameraOn = true;
        await this.$nextTick();
        try {
            await window.asicScanner.start('session-reader', async (text) => {
                this.cameraOn = false;
                this.sessionQrToken = window.asicScanner.sessionToken(text);
                await this.scan();
            });
        } catch (error) {
            this.cameraOn = false;
            this.cameraError = this.i18n[window.asicScanner.errorMessage(error)] || this.i18n.camera_failed;
        }
        this.cameraStarting = false;
    },
    async closeCamera() { await window.asicScanner.stop(); this.cameraOn = false; },
    async scan() {
        this.scanError = ''; this.fieldErrors = {}; this.lastSuccess = ''; this.scanning = true;
        const response = await window.asicApi.post('/api/attendance/scan', { qr_token: window.asicScanner.sessionToken(this.sessionQrToken) });
        this.scanning = false;
        // 409 = sudah tercatat sebelumnya: bukan kesalahan, tampilkan sebagai info.
        if (response.status === 409) { this.sessionQrToken = ''; this.lastSuccess = response.error || response.data?.message || ''; return; }
        if (!response.ok) { this.fieldErrors = response.errors || {}; this.scanError = response.error || this.i18n.checkin_failed; return; }
        this.sessionQrToken = '';
        this.lastSuccess = response.data?.message || this.i18n.checkin_success;
        await this.load();
        window.asicApi.showToast(this.lastSuccess, 'success');
    }
}" @beforeunload.window="window.asicScanner.stop()">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t('heading') }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t('subtitle') }}</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t('loading') }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <template x-if="registration">
        <section class="grid gap-5 lg:grid-cols-[1.25fr_1fr]">
            {{-- Absen sesi: peserta memindai QR sesi yang ditampilkan panitia --}}
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined">qr_code_scanner</span></span>
                    <div><h2 class="font-bold">{{ $t('session_checkin') }}</h2><p class="mt-0.5 text-sm text-on-surface-variant">{{ $t('session_note') }}</p></div>
                </div>

                <div x-show="cameraOn" class="mt-4 overflow-hidden rounded-xl bg-black">
                    <div id="session-reader" class="w-full"></div>
                </div>
                <p x-show="cameraOn" class="mt-2 text-center text-xs text-on-surface-variant">{{ $t('camera_hint') }}</p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" x-show="!cameraOn" @click="openCamera()" :disabled="scanning" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white hover:bg-[#168348] disabled:opacity-50"><span class="material-symbols-outlined text-[20px]">photo_camera</span><span x-text="scanning ? i18n.checking : i18n.open_camera"></span></button>
                    <button type="button" x-show="cameraOn" @click="closeCamera()" class="inline-flex items-center gap-2 rounded-xl border border-surface-container-highest px-5 py-2.5 text-sm font-bold"><span class="material-symbols-outlined text-[20px]">close</span>{{ $t('close_camera') }}</button>
                </div>

                <p x-show="lastSuccess" x-text="lastSuccess" role="status" class="mt-3 flex items-center gap-2 rounded-xl bg-[#E6F4EA] p-3 text-sm font-semibold text-primary"></p>
                <p x-show="cameraError" x-text="cameraError" role="alert" class="mt-3 rounded-xl bg-error-container p-3 text-sm text-on-error-container"></p>
                <p x-show="scanError" x-text="scanError" role="alert" class="mt-3 rounded-xl bg-error-container p-3 text-sm text-on-error-container"></p>
                <p class="mt-3 text-xs text-on-surface-variant">{{ $t('phone_camera_note') }}</p>

                <details class="mt-3 text-sm">
                    <summary class="cursor-pointer font-semibold text-primary">{{ $t('manual_toggle') }}</summary>
                    <form class="mt-2 flex gap-2" @submit.prevent="scan()">
                        <input x-model="sessionQrToken" required maxlength="500" :aria-label="i18n.session_token" :placeholder="i18n.session_token" class="min-w-0 flex-1 font-mono text-sm">
                        <button type="submit" :disabled="scanning" class="rounded-xl bg-primary px-4 text-sm font-bold text-white disabled:opacity-50" x-text="scanning ? i18n.checking : i18n.checkin"></button>
                    </form>
                    <p x-show="fieldErrors.qr_token" x-text="fieldErrors.qr_token?.[0]" class="mt-1 text-sm text-error"></p>
                </details>

                <h3 class="mt-5 text-sm font-bold">{{ $t('history_title') }}</h3>
                <p x-show="!sessions.length" class="mt-1 text-sm text-on-surface-variant">{{ $t('history_empty') }}</p>
                <ul class="mt-2 space-y-2 text-sm" x-show="sessions.length">
                    <template x-for="row in sessions" :key="row.id_session || row.id_attendance"><li class="flex items-start justify-between gap-2 border-t border-surface-container-high pt-2"><span><span class="block font-semibold" x-text="row.event_session?.nama_sesi || row.nama_sesi || ''"></span><span class="block text-xs text-on-surface-variant" x-text="asicDateTime(row.tanggal_check_in)"></span></span><span class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-primary"><span class="material-symbols-outlined text-[16px]">check_circle</span><span x-text="row.status_kehadiran || ''"></span></span></li></template>
                </ul>
            </article>

            {{-- QR pribadi: hanya peserta luring, ditunjukkan ke panitia di meja registrasi ulang --}}
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FEF6D8] text-[#7a5d00]"><span class="material-symbols-outlined">badge</span></span>
                    <div><h2 class="font-bold">{{ $t('personal_qr') }}</h2><p class="mt-0.5 text-xs text-on-surface-variant"><span>{{ $t('registration') }}</span> #<span x-text="registration.id_registration"></span> · <span x-text="registration.attendance_mode"></span></p></div>
                </div>
                <p x-show="isOnline" class="mt-4 rounded-xl bg-surface-container-low p-3 text-sm text-on-surface-variant">{{ $t('online_note') }}</p>
                <p x-show="!isOnline && !qrToken && !loading" class="mt-4 text-sm text-on-surface-variant" x-text="qrBlocked || i18n.not_ready"></p>
                <div x-show="qrToken" class="mt-4 space-y-3 text-center">
                    <canvas x-ref="qr" class="mx-auto rounded-xl bg-white p-2" width="220" height="220" :aria-label="i18n.personal_qr" role="img"></canvas>
                    <p class="text-left text-xs text-on-surface-variant">{{ $t('purpose_note') }}</p>
                    <div class="flex flex-wrap justify-center gap-3 text-sm"><button type="button" @click="download()" class="rounded-xl border border-primary px-4 py-2 font-semibold text-primary">{{ $t('download_png') }}</button><button type="button" @click="navigator.clipboard?.writeText(qrToken)" class="font-semibold text-primary">{{ $t('copy_token') }}</button></div>
                </div>
            </article>
        </section>
    </template>
</main>
@endsection
