@php($t = trans('asic-participant.portal.documents'))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.portal.documents.title'), 'nav' => $participantNav])

@section('content')
<main class="mx-auto max-w-4xl space-y-6" x-data="{
    i18n: @js($t), types: @js(trans('asic-documents.types')), documents: [], loading: true, loadError: '',
    async init() {
        const registrationsResponse = await window.asicApi.get('/api/registrations');
        if (!registrationsResponse.ok) { this.loadError = registrationsResponse.error || this.i18n.load_failed; this.loading = false; return; }
        const registrations = registrationsResponse.data?.data?.data || [];
        try {
            const results = await Promise.all(registrations.map(async registration => {
                const response = await window.asicApi.get('/api/registrations/' + registration.id_registration + '/documents');
                if (!response.ok) throw new Error(response.error || this.i18n.load_failed);
                return (response.data?.data || []).map(document => ({ ...document, id_registration: registration.id_registration }));
            }));
            this.documents = results.flat();
        } catch (error) { this.loadError = error.message || this.i18n.load_failed; }
        finally { this.loading = false; }
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <div x-show="!loading && !loadError && documents.length === 0" class="rounded-2xl border border-surface-container-highest p-10 text-center text-sm text-on-surface-variant">{{ $t['empty'] }}</div>
    <div class="space-y-3">
        <template x-for="document in documents" :key="document.id_document">
            <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <div><h2 class="font-bold" x-text="types[document.jenis_dokumen] || document.jenis_dokumen"></h2><p class="mt-1 text-xs text-on-surface-variant"><span>{{ $t['number'] }}</span> <span x-text="document.nomor_dokumen || '—'"></span> · <span>{{ $t['issued'] }}</span> <span x-text="asicDate(document.tanggal_terbit)"></span> · <span>{{ $t['registration'] }}</span> <span x-text="document.id_registration"></span></p></div>
                <a :href="'/api/documents/' + document.id_document + '/download'" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white">{{ $t['download'] }}</a>
            </article>
        </template>
    </div>
</main>
@endsection
