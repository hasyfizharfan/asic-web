@php($t = trans('asic-documents.admin'))
@extends('layouts.portal', ['title' => __('asic-documents.admin.title'), 'role' => 'admin', 'roleName' => __('asic-documents.admin.title'), 'nav' => $adminNav])

@section('content')
<main class="space-y-5" x-data="{
    i18n: @js($t), types: @js(trans('asic-documents.types')), subtypes: @js(trans('asic-documents.subtypes')),
    downloadTypes: @js(\App\Models\DocumentTemplate::DOWNLOADS),
    tab: 'issued', events: [], idEvent: '', rows: [], loading: true, error: '', saving: false, fieldErrors: {},
    upload: { jenis_dokumen: 'Abstrak', nama_template: '', subjenis: '', bahasa: '', versi: '' },
    async init() {
        const response = await window.asicApi.get('/api/content/events', { per_page: 100 });
        if (response.ok) { this.events = response.data?.data || []; this.idEvent = String(this.events[0]?.id_event || ''); }
        await this.load();
    },
    async load() {
        if (!this.idEvent) { this.loading = false; return; }
        this.loading = true; this.error = '';
        const response = await window.asicApi.get('/api/document-templates', { id_event: this.idEvent, kelompok: this.tab });
        this.loading = false;
        if (!response.ok) { this.error = response.error || this.i18n.load_failed; return; }
        this.rows = (response.data?.data || []).filter(r => this.tab === 'issued' ? r.status_template === 'Aktif' : true);
    },
    async saveFormat(row) {
        this.saving = true; this.fieldErrors = {};
        const response = await window.asicApi.put('/api/document-templates/issued/' + row.jenis_dokumen, { id_event: Number(this.idEvent), format_nomor: row.format_nomor });
        this.saving = false;
        if (!response.ok) { this.fieldErrors = { [row.jenis_dokumen]: response.errors?.format_nomor?.[0] || response.error || this.i18n.save_failed }; return; }
        await this.load();
    },
    async submitUpload(event) {
        this.saving = true; this.fieldErrors = {};
        const data = new FormData(event.target); data.append('id_event', this.idEvent);
        const response = await window.asicApi.upload('/api/document-templates', data);
        this.saving = false;
        if (!response.ok) { this.fieldErrors = response.errors || { file: [response.error || this.i18n.save_failed] }; return; }
        event.target.reset(); await this.load();
    },
    async setStatus(row, status) {
        const response = await window.asicApi.patch('/api/document-templates/' + row.id_template, { status_template: status });
        if (!response.ok) { this.error = response.error || this.i18n.save_failed; return; }
        await this.load();
    }
}">
    <header><h1 class="text-2xl font-bold">{{ $t['title'] }}</h1><p class="mt-1 max-w-3xl text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex gap-2" role="tablist">
            <button type="button" role="tab" @click="tab = 'issued'; load()" :class="tab === 'issued' ? 'bg-primary text-white' : 'bg-surface-container'" class="rounded-lg px-4 py-2 text-sm">{{ $t['tab_issued'] }}</button>
            <button type="button" role="tab" @click="tab = 'downloads'; load()" :class="tab === 'downloads' ? 'bg-primary text-white' : 'bg-surface-container'" class="rounded-lg px-4 py-2 text-sm">{{ $t['tab_downloads'] }}</button>
        </div>
        <label class="ml-auto text-sm font-semibold">{{ $t['event'] }}<select x-model="idEvent" @change="load()" class="mt-1 block rounded-xl border-surface-container-highest"><template x-for="item in events" :key="item.id_event"><option :value="String(item.id_event)" x-text="item.nama_event"></option></template></select></label>
    </div>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">…</p>
    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>

    <section x-show="tab === 'issued' && !loading" class="space-y-3">
        <p class="text-xs text-on-surface-variant">{{ $t['format_help'] }}</p>
        <template x-for="row in rows" :key="row.jenis_dokumen">
            <article class="grid gap-3 rounded-xl border border-surface-container-high bg-surface-container-lowest p-4 md:grid-cols-[14rem_1fr_auto]">
                <div><p class="font-semibold" x-text="types[row.jenis_dokumen] || row.jenis_dokumen"></p><span x-show="row.bawaan" class="rounded-full bg-surface-container px-2 py-0.5 text-[11px]">{{ $t['builtin'] }}</span></div>
                <label class="text-sm"><span class="sr-only">{{ $t['format'] }}</span><input x-model="row.format_nomor" maxlength="100" class="w-full rounded-lg border-surface-container-highest font-mono text-sm"><span x-show="fieldErrors[row.jenis_dokumen]" x-text="fieldErrors[row.jenis_dokumen]" class="text-xs text-error"></span></label>
                <div class="flex gap-2 text-sm"><button type="button" @click="saveFormat(row)" :disabled="saving" class="rounded-lg bg-primary px-3 py-2 font-semibold text-white disabled:opacity-40">{{ $t['save'] }}</button><a :href="'/api/document-templates/preview/' + row.jenis_dokumen + '?id_event=' + idEvent" target="_blank" rel="noopener" class="act-btn"><span class="material-symbols-outlined" aria-hidden="true">visibility</span>{{ $t['preview'] }}</a></div>
            </article>
        </template>
        <aside class="rounded-xl bg-surface-container-low p-4 text-sm">
            <p class="font-semibold">{{ $t['signers'] }}</p>
            <ul class="mt-1 text-on-surface-variant">@foreach(config('asic.signers', []) as $signer)<li>{{ $signer['name'] ?? '' }} — {{ $signer['title'] ?? '' }}</li>@endforeach</ul>
            <p class="mt-2 text-xs text-on-surface-variant">{{ $t['signer_note'] }}</p>
        </aside>
    </section>

    <section x-show="tab === 'downloads' && !loading" class="space-y-4">
        <form @submit.prevent="submitUpload($event)" class="grid gap-3 rounded-xl border border-surface-container-high bg-surface-container-lowest p-4 md:grid-cols-3">
            <h2 class="font-bold md:col-span-3">{{ $t['upload'] }}</h2>
            <label class="text-sm">{{ $t['type'] }}<select name="jenis_dokumen" class="mt-1 block w-full rounded-lg border-surface-container-highest"><template x-for="jenis in downloadTypes" :key="jenis"><option :value="jenis" x-text="types[jenis] || jenis"></option></template></select></label>
            <label class="text-sm">{{ $t['subtype'] }}<select name="subjenis" class="mt-1 block w-full rounded-lg border-surface-container-highest"><option value="">{{ $t['any'] }}</option><template x-for="(label, key) in subtypes" :key="key"><option :value="key" x-text="label"></option></template></select></label>
            <label class="text-sm">{{ $t['language'] }}<select name="bahasa" class="mt-1 block w-full rounded-lg border-surface-container-highest"><option value="">{{ $t['any'] }}</option><option value="id">ID</option><option value="en">EN</option></select></label>
            <label class="text-sm">{{ $t['name'] }}<input name="nama_template" required maxlength="150" class="mt-1 block w-full rounded-lg border-surface-container-highest"><span x-show="fieldErrors.nama_template" x-text="fieldErrors.nama_template?.[0]" class="text-xs text-error"></span></label>
            <label class="text-sm">{{ $t['version'] }}<input name="versi" maxlength="20" class="mt-1 block w-full rounded-lg border-surface-container-highest"></label>
            <label class="text-sm">{{ $t['file'] }}<input type="file" name="file" required accept=".docx,.doc,.pptx,.ppt,.pdf" class="mt-1 block w-full text-sm"><span x-show="fieldErrors.file" x-text="fieldErrors.file?.[0]" class="text-xs text-error"></span></label>
            <div class="md:col-span-3"><button :disabled="saving" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-40" x-text="saving ? i18n.saving : i18n.save"></button><p class="mt-2 text-xs text-on-surface-variant">{{ $t['history_note'] }}</p></div>
        </form>
        <p x-show="rows.length === 0" class="rounded-xl border p-6 text-center text-sm text-on-surface-variant">{{ $t['empty'] }}</p>
        <template x-for="row in rows" :key="row.id_template">
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-surface-container-high bg-surface-container-lowest p-4 text-sm">
                <div><p class="font-semibold" x-text="row.nama_template"></p><p class="text-xs text-on-surface-variant" x-text="[types[row.jenis_dokumen], subtypes[row.subjenis], (row.bahasa || '').toUpperCase(), row.versi ? 'v' + row.versi : '', row.status_template].filter(Boolean).join(' · ')"></p></div>
                <div class="flex gap-3"><a :href="'/api/document-templates/' + row.id_template + '/download'" class="icon-btn" aria-label="{{ $t['download'] }}"><span class="material-symbols-outlined" aria-hidden="true">download</span></a><button type="button" @click="setStatus(row, row.status_template === 'Aktif' ? 'Tidak Aktif' : 'Aktif')" class="icon-btn icon-btn-muted" :aria-label="row.status_template === 'Aktif' ? i18n.deactivate : i18n.activate"><span class="material-symbols-outlined" aria-hidden="true" x-text="row.status_template === 'Aktif' ? 'toggle_on' : 'toggle_off'"></span></button></div>
            </article>
        </template>
    </section>
</main>
@endsection
