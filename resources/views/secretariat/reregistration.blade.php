@php($t = fn ($k) => __('asic-secretariat.reregistration.'.$k))
@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => __('asic-secretariat.reregistration.page_title'), 'nav' => $secNav, 'title' => __('asic-secretariat.reregistration.page_title')])

@section('content')
<main class="mx-auto max-w-6xl space-y-6" x-data="{
    i18n: @js(trans('asic-secretariat.reregistration')),
    today: @js(now()->toDateString()),
    events: [], idEvent: '', query: '', results: [], selected: null, qrToken: '',
    searching: false, busy: false, error: '', result: null,
    get event() { return this.events.find(e => String(e.id_event) === this.idEvent) || null; },
    get isFirstDay() { return !!this.event && String(this.event.tanggal_mulai || '').slice(0, 10) === this.today; },
    async init() {
        const response = await window.asicApi.get('/api/content/events', { per_page: 100 });
        if (!response.ok) { this.error = response.error || this.i18n.load_events_failed; return; }
        this.events = response.data?.data || [];
        this.idEvent = String(this.events[0]?.id_event || '');
    },
    async search() {
        this.error = ''; this.selected = null; this.results = [];
        if (this.query.trim().length < 2 && !/^\d+$/.test(this.query.trim())) { this.error = this.i18n.min_chars; return; }
        this.searching = true;
        const response = await window.asicApi.get('/api/registrations/search', { q: this.query.trim() });
        this.searching = false;
        if (!response.ok) { this.error = response.error || this.i18n.search_failed; return; }
        this.results = response.data?.data || [];
    },
    show(response) {
        const kind = response.ok ? 'success' : (response.status === 409 ? 'already' : 'rejected');
        this.result = { kind, message: response.data?.message || response.error, data: response.data?.data || null };
        if (this.selected && response.data?.data) { this.selected.reregistered_at = response.data.data.reregistered_at; }
    },
    async scan() {
        if (!this.idEvent || !this.qrToken) return;
        this.busy = true; this.result = null;
        const response = await window.asicApi.post('/api/events/' + this.idEvent + '/reregistration/scan', { qr_token: this.qrToken.trim() });
        this.busy = false; this.qrToken = '';
        this.show(response);
    },
    cameraOn: false, cameraError: '',
    async openCamera() {
        this.cameraError = ''; this.cameraOn = true;
        await this.$nextTick();
        try {
            await window.asicScanner.start('rereg-reader', async (text) => { this.qrToken = String(text || '').trim(); await this.scan(); }, true);
        } catch (error) {
            this.cameraOn = false;
            this.cameraError = this.i18n[window.asicScanner.errorMessage(error)] || this.i18n.camera_failed;
        }
    },
    async closeCamera() { await window.asicScanner.stop(); this.cameraOn = false; },
    async manual() {
        if (!this.idEvent || !this.selected) return;
        this.busy = true; this.result = null;
        const response = await window.asicApi.post('/api/events/' + this.idEvent + '/reregistration', { id_registration: this.selected.id_registration });
        this.busy = false;
        this.show(response);
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-surface-container-low p-6">
        <div><p class="text-xs font-bold uppercase tracking-widest text-primary">{{ $t('eyebrow') }}</p><h1 class="mt-1 text-2xl font-bold">{{ $t('heading') }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t('subtitle') }}</p></div>
        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container">{{ $t('live_badge') }}</span>
    </header>

    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>

    <section class="flex flex-wrap items-end gap-4 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm">
        <label class="block min-w-[16rem] flex-1 text-sm font-semibold">{{ $t('event_label') }}
            <select x-model="idEvent" class="mt-1 block w-full rounded-xl border-surface-container-highest"><template x-for="item in events" :key="item.id_event"><option :value="String(item.id_event)" x-text="item.nama_event"></option></template></select>
        </label>
        <p class="text-sm" x-show="event"><span class="block text-xs text-on-surface-variant">{{ $t('first_day') }}</span><strong x-text="asicDate(event?.tanggal_mulai)"></strong></p>
        <p class="rounded-xl px-3 py-2 text-sm font-semibold" x-show="event" :class="isFirstDay ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'" x-text="isFirstDay ? i18n.first_day_open : i18n.first_day_closed"></p>
    </section>

    <section x-show="result" role="status" class="rounded-2xl p-5" :class="{ 'bg-secondary-container text-on-secondary-container': result?.kind === 'success', 'bg-tertiary-container text-on-tertiary-container': result?.kind === 'already', 'bg-error-container text-on-error-container': result?.kind === 'rejected' }">
        <p class="text-xs font-bold uppercase tracking-widest" x-text="result?.kind === 'success' ? i18n.result_success : (result?.kind === 'already' ? i18n.result_already : i18n.result_rejected)"></p>
        <p class="mt-1 font-semibold" x-text="result?.message"></p>
        <p class="mt-1 text-sm" x-show="result?.data" x-text="(result?.data?.participant?.full_name || '') + ' · #' + (result?.data?.id_registration || '') + ' · ' + (result?.data?.attendance_mode || '') + (result?.data?.reregistered_at ? ' · ' + asicDateTime(result.data.reregistered_at) : '')"></p>
    </section>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <article class="space-y-4 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-bold">{{ $t('scan_title') }}</h2>
                <button type="button" x-show="!cameraOn" @click="openCamera()" :disabled="!idEvent" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-40"><span class="material-symbols-outlined text-[18px]">photo_camera</span>{{ $t('camera_open') }}</button>
                <button type="button" x-show="cameraOn" @click="closeCamera()" class="inline-flex items-center gap-1.5 rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-bold"><span class="material-symbols-outlined text-[18px]">close</span>{{ $t('camera_close') }}</button>
            </div>
            <div x-show="cameraOn" class="overflow-hidden rounded-xl bg-black"><div id="rereg-reader" class="w-full"></div></div>
            <p class="text-xs text-on-surface-variant">{{ $t('camera_note') }}</p>
            <p x-show="cameraError" x-text="cameraError" role="alert" class="rounded-xl bg-error-container p-3 text-sm text-on-error-container"></p>
            <form class="flex gap-2" @submit.prevent="scan()"><input x-model="qrToken" required placeholder="{{ $t('scan_placeholder') }}" class="min-w-0 flex-1 rounded-xl border-surface-container-highest font-mono"><button :disabled="!idEvent || busy" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-40">{{ $t('scan_btn') }}</button></form>
        </article>

        <article class="space-y-4 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-bold">{{ $t('search_title') }}</h2>
            <form class="flex gap-2" @submit.prevent="search()"><input x-model="query" placeholder="{{ $t('search_placeholder') }}" class="min-w-0 flex-1 rounded-xl border-surface-container-highest"><button :disabled="searching" class="rounded-xl border border-primary px-4 py-2 text-sm font-bold text-primary disabled:opacity-50" x-text="searching ? i18n.searching : i18n.search_btn"></button></form>
            <div x-show="results.length" class="space-y-2"><template x-for="row in results" :key="row.id_registration"><button type="button" @click="selected = row" class="w-full rounded-xl border border-surface-container-high p-3 text-left hover:bg-surface-container-low"><span class="flex justify-between gap-2"><strong x-text="row.participant?.full_name"></strong><span class="text-xs text-on-surface-variant" x-text="'#' + row.id_registration"></span></span><span class="mt-1 block text-xs text-on-surface-variant" x-text="(row.participant?.email || '') + ' · ' + (row.event || '') + ' · ' + (row.attendance_mode || '')"></span></button></template></div>
            <p x-show="query.length >= 2 && results.length === 0 && !searching && !error" class="rounded-xl bg-surface-container-low p-4 text-sm text-on-surface-variant">{{ $t('no_results') }}</p>
            <section x-show="selected" class="space-y-3 rounded-xl bg-surface-container-low p-4">
                <div><h3 class="font-bold" x-text="selected?.participant?.full_name"></h3><p class="text-xs text-on-surface-variant" x-text="selected?.participant?.institution || i18n.institution_missing"></p></div>
                <div class="grid gap-3 text-sm sm:grid-cols-4">
                    <div><span class="block text-xs text-on-surface-variant">{{ $t('col_registration') }}</span><strong x-text="asicStatus(selected?.registration_status)"></strong></div>
                    <div><span class="block text-xs text-on-surface-variant">{{ $t('col_mode') }}</span><strong x-text="selected?.attendance_mode"></strong></div>
                    <div><span class="block text-xs text-on-surface-variant">{{ $t('col_payment') }}</span><strong x-text="asicStatus(selected?.invoice?.status_pembayaran) || i18n.no_invoice"></strong></div>
                    <div><span class="block text-xs text-on-surface-variant">{{ $t('col_rereg') }}</span><strong x-text="selected?.reregistered_at ? asicDateTime(selected.reregistered_at) : i18n.not_yet"></strong></div>
                </div>
                <button type="button" @click="manual()" :disabled="!idEvent || busy" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-40">{{ $t('manual_btn') }}</button>
            </section>
        </article>
    </section>
</main>
@endsection
