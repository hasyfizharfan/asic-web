@php($t = trans('asic-participant.portal.full_paper'))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.portal.full_paper.title'), 'nav' => $participantNav])

@section('content')
<main class="mx-auto max-w-4xl space-y-6" x-data="{
    i18n: @js($t), registrations: [], registration: null, files: [], loading: true, uploading: false, loadError: '', fieldErrors: {},
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/registrations');
        if (!response.ok) { this.loadError = response.error || this.i18n.load_failed; this.loading = false; return; }
        this.registrations = response.data?.data?.data || [];
        const id = new URLSearchParams(window.location.search).get('id_registration');
        this.registration = this.registrations.find(row => String(row.id_registration) === String(id)) || this.registrations.find(row => row.participation_type !== 'Listener') || this.registrations[0] || null;
        if (!this.registration) { this.loadError = this.i18n.no_registration; this.files = []; this.loading = false; return; }
        const fileResponse = await window.asicApi.get('/api/registrations/' + this.registration.id_registration + '/files');
        this.loading = false;
        if (!fileResponse.ok) { this.loadError = fileResponse.error || this.i18n.load_failed; return; }
        this.files = fileResponse.data?.data || [];
    },
    get isListener() { return this.registration?.participation_type === 'Listener'; },
    get canUpload() { return !!this.registration && !this.isListener && this.registration?.abstract_submission?.status_abstrak === 'Diterima'; },
    async upload(event) {
        this.fieldErrors = {}; this.uploading = true;
        const response = await window.asicApi.upload('/api/registrations/' + this.registration.id_registration + '/files', new FormData(event.target));
        this.uploading = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; window.asicApi.showToast(response.error || this.i18n.upload_failed); return; }
        event.target.reset(); await this.load();
        window.asicApi.showToast(response.data?.message || this.i18n.uploaded, 'success');
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p><p class="mt-1 text-xs"><a href="{{ route('public.downloads') }}" target="_blank" rel="noopener" class="font-semibold text-primary underline">{{ __('asic-documents.public.title') }}</a></p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <section x-show="registration" class="space-y-5">
        <div x-show="!canUpload" class="flex gap-3 rounded-2xl border border-[#f5e3a3] bg-[#FEF6D8] p-5 text-sm text-[#5c4700]" role="status">
            <span class="material-symbols-outlined text-[22px]">info</span>
            <p x-text="isListener ? i18n.listener_note : i18n.not_accepted_note"></p>
        </div>
        <form x-show="canUpload" class="grid gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 sm:grid-cols-2" @submit.prevent="upload($event)">
            <label class="block text-sm font-semibold">{{ $t['field_type'] }}
                <select name="jenis_file" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option value="Artikel">{{ $t['type_article'] }}</option><option value="Presentasi">{{ $t['type_presentation'] }}</option></select>
            </label>
            <label class="block text-sm font-semibold">{{ $t['field_file'] }}
                <input name="file" type="file" required accept=".pdf,.doc,.docx,.ppt,.pptx" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
            </label>
            <p x-show="fieldErrors.jenis_file" x-text="fieldErrors.jenis_file?.[0]" class="text-sm text-error"></p><p x-show="fieldErrors.file" x-text="fieldErrors.file?.[0]" class="text-sm text-error"></p>
            <button type="submit" :disabled="uploading" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50 sm:col-span-2" x-text="uploading ? i18n.uploading : i18n.upload"></button>
        </form>
        <div class="space-y-3"><h2 class="text-lg font-bold">{{ $t['uploaded_files'] }}</h2><p x-show="!loading && files.length === 0" class="rounded-xl border border-surface-container-highest p-5 text-sm text-on-surface-variant">{{ $t['empty'] }}</p>
            <template x-for="file in files" :key="file.id_file"><article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-surface-container-highest bg-surface-container-lowest p-4"><div><p class="font-semibold" x-text="file.nama_file"></p><p class="text-xs text-on-surface-variant" x-text="file.jenis_file + ' · ' + file.status_file + ' · ' + asicDateTime(file.tanggal_upload)"></p></div><a :href="'/api/participant-files/' + file.id_file + '/download'" class="font-semibold text-primary">{{ $t['download'] }}</a></article></template>
        </div>
    </section>
</main>
@endsection
