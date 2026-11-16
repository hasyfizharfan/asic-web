@php($t = fn ($k) => __('asic-participant.attendance_qr.'.$k))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.attendance_qr.title'), 'nav' => $participantNav, 'title' => __('asic-participant.attendance_qr.title')])

@section('content')
<main class="mx-auto max-w-4xl space-y-6" x-data="{
    i18n: @js(trans('asic-participant.attendance_qr')),
    registrations: [], registration: null, qrToken: '', qrBlocked: '', sessions: [],
    loading: true, scanning: false, loadError: '', scanError: '', fieldErrors: {}, sessionQrToken: '',
    get isOnline() { return this.registration?.attendance_mode === 'Daring'; },
    async init() { await this.load(); },
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
        this.$nextTick(() => { if (this.qrToken && this.$refs.qr) window.asicQr.render(this.$refs.qr, this.qrToken); });
    },
    download() { window.asicQr.download(this.$refs.qr, 'qr-kehadiran-' + this.registration.id_registration + '.png'); },
    async scan() {
        this.scanError = ''; this.fieldErrors = {}; this.scanning = true;
        const response = await window.asicApi.post('/api/attendance/scan', { qr_token: this.sessionQrToken });
        this.scanning = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; this.scanError = response.error || this.i18n.checkin_failed; return; }
        this.sessionQrToken = '';
        await this.load();
        if (response.data?.message) window.asicApi.showToast(response.data.message, 'success');
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t('heading') }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t('subtitle') }}</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t('loading') }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <template x-if="registration">
        <section class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <h2 class="font-bold">{{ $t('personal_qr') }}</h2>
                <p class="mt-1 text-xs text-on-surface-variant"><span>{{ $t('registration') }}</span> #<span x-text="registration.id_registration"></span> · <span x-text="registration.attendance_mode"></span></p>
                <p x-show="isOnline" class="mt-3 rounded-xl bg-secondary-container p-3 text-sm text-on-secondary-container">{{ $t('online_note') }}</p>
                <p x-show="!isOnline && !qrToken && !loading" class="mt-3 text-sm text-on-surface-variant" x-text="qrBlocked || i18n.not_ready"></p>
                <div x-show="qrToken" class="mt-4 space-y-3">
                    <canvas x-ref="qr" class="mx-auto rounded-xl bg-white p-2" width="240" height="240" :aria-label="i18n.personal_qr" role="img"></canvas>
                    <p class="text-xs text-on-surface-variant">{{ $t('purpose_note') }}</p>
                    <div class="flex flex-wrap gap-3 text-sm"><button type="button" @click="download()" class="rounded-xl bg-primary px-4 py-2 font-semibold text-white">{{ $t('download_png') }}</button><button type="button" @click="navigator.clipboard?.writeText(qrToken)" class="font-semibold text-primary">{{ $t('copy_token') }}</button></div>
                    <details class="text-xs"><summary class="cursor-pointer text-on-surface-variant">{{ $t('token_fallback') }}</summary><code class="mt-2 block break-all rounded-xl bg-surface-container-low p-3 font-mono" x-text="qrToken"></code></details>
                </div>
            </article>
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <h2 class="font-bold">{{ $t('session_checkin') }}</h2>
                <p class="mt-1 text-xs text-on-surface-variant">{{ $t('session_note') }}</p>
                <form class="mt-3 space-y-3" @submit.prevent="scan()">
                    <label class="block text-sm font-semibold">{{ $t('session_token') }}
                        <input x-model="sessionQrToken" required maxlength="100" class="mt-1 block w-full rounded-xl border-surface-container-highest font-mono text-sm">
                    </label>
                    <p x-show="fieldErrors.qr_token" x-text="fieldErrors.qr_token?.[0]" class="text-sm text-error"></p>
                    <p x-show="scanError" x-text="scanError" role="alert" class="text-sm text-error"></p>
                    <button type="submit" :disabled="scanning" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-50" x-text="scanning ? i18n.checking : i18n.checkin"></button>
                </form>
                <ul class="mt-4 space-y-2 text-sm" x-show="sessions.length">
                    <template x-for="row in sessions" :key="row.id_session || row.id_attendance"><li class="flex justify-between gap-2 border-t border-surface-container-high pt-2"><span x-text="row.nama_sesi || row.session?.nama_sesi || ''"></span><span class="text-xs text-on-surface-variant" x-text="row.status_kehadiran || ''"></span></li></template>
                </ul>
            </article>
        </section>
    </template>
</main>
@endsection
