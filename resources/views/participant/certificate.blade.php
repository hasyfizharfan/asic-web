@php($t = trans('asic-documents.certificate'))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-documents.certificate.page_title'), 'nav' => $participantNav, 'title' => __('asic-documents.certificate.page_title')])

@section('content')
<main class="mx-auto max-w-3xl space-y-5" x-data="{
    i18n: @js($t), registrations: [], registration: null, status: null, loading: true, issuing: false, error: '', message: '',
    async init() {
        const response = await window.asicApi.get('/api/registrations');
        if (!response.ok) { this.error = response.error || this.i18n.load_failed; this.loading = false; return; }
        this.registrations = response.data?.data?.data || [];
        const selected = new URLSearchParams(window.location.search).get('id_registration');
        this.registration = this.registrations.find(r => String(r.id_registration) === String(selected)) || this.registrations[0] || null;
        if (!this.registration) { this.error = this.i18n.no_registration; this.loading = false; return; }
        await this.load();
    },
    async load() {
        this.loading = true;
        const response = await window.asicApi.get('/api/registrations/' + this.registration.id_registration + '/certificate');
        this.loading = false;
        if (!response.ok) { this.error = response.error || this.i18n.load_failed; return; }
        this.status = response.data?.data;
    },
    async issue() {
        this.issuing = true; this.error = ''; this.message = '';
        const response = await window.asicApi.post('/api/registrations/' + this.registration.id_registration + '/certificate', {});
        this.issuing = false;
        if (!response.ok) { this.error = response.error || this.i18n.not_eligible; if (response.data?.data) this.status = { ...this.status, ...response.data.data }; return; }
        this.status = response.data?.data; this.message = response.data?.message || '';
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p>
    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <p x-show="message" x-text="message" role="status" class="rounded-xl bg-secondary-container p-4 text-sm text-on-secondary-container"></p>
    <section x-show="status" class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <h2 class="font-bold">{{ $t['requirements'] }}</h2>
        <ul class="space-y-2 text-sm">
            <template x-for="check in status?.checks || []" :key="check.key">
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[20px]" :class="check.ok ? 'text-primary' : 'text-error'" x-text="check.ok ? 'check_circle' : 'cancel'"></span><span x-text="check.label"></span></li>
            </template>
        </ul>
        <p class="text-xs text-on-surface-variant">{{ $t['name_note'] }}</p>
        <div class="flex flex-wrap gap-3">
            <button type="button" x-show="!status?.document" @click="issue()" :disabled="issuing || !status?.eligible" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-40" x-text="issuing ? i18n.issuing : i18n.issue"></button>
            <template x-if="status?.document"><button type="button" @click="window.asicPreview(status.document.download_url, status.document.nomor_dokumen || '')" class="inline-flex items-center gap-1.5 rounded-xl border border-primary px-4 py-2 text-sm font-bold text-primary hover:bg-[#E6F4EA]"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">visibility</span>{{ __('asic-common.preview') }}</button></template>
            <a x-show="status?.document" :href="status?.document?.download_url" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>{{ $t['download'] }}</a>
        </div>
        <p x-show="status?.document" class="text-sm" x-text="i18n.issued.replace(':number', status?.document?.nomor_dokumen || '')"></p>
    </section>
</main>
@endsection
