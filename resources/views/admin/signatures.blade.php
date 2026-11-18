@php($t = trans('asic-admin.signatures_page'))
@extends('layouts.portal', ['role' => 'admin', 'roleName' => $t['title'], 'nav' => $adminNav, 'title' => $t['title']])

@section('content')
<main class="mx-auto max-w-4xl space-y-6" x-data="{
    i18n: @js($t),
    roles: @js($signatureRoles), signatures: [], loading: true, saving: false, loadError: '', fieldErrors: {},
    async init() { await this.load(); },
    async load() {
        this.loading = true;
        const signaturesResponse = await window.asicApi.get('/api/digital-signatures');
        this.loading = false;
        if (!signaturesResponse.ok) { this.loadError = signaturesResponse.error || i18n.load_failed; return; }
        this.signatures = signaturesResponse.data?.data || [];
    },
    async upload(event) {
        this.fieldErrors = {}; this.saving = true;
        const response = await window.asicApi.upload('/api/digital-signatures', new FormData(event.target));
        this.saving = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; window.asicApi.showToast(response.error || i18n.save_failed); return; }
        event.target.reset(); await this.load();
        window.asicApi.showToast(response.data?.message || i18n.saved, 'success');
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p><p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    @if($canManageSignatures)
    <form class="grid gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 sm:grid-cols-2" @submit.prevent="upload($event)">
        <label class="text-sm font-semibold">{{ $t['role'] }}<select name="id_role" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option value="">{{ $t['choose_role'] }}</option><template x-for="role in roles" :key="role.id_role"><option :value="role.id_role" x-text="role.nama_role"></option></template></select></label>
        <label class="text-sm font-semibold">{{ $t['signature_file'] }}<input type="file" name="file_ttd" required accept=".png,.jpg,.jpeg,.svg" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm"></label>
        <p x-show="fieldErrors.id_role" x-text="fieldErrors.id_role?.[0]" class="text-sm text-error"></p><p x-show="fieldErrors.file_ttd" x-text="fieldErrors.file_ttd?.[0]" class="text-sm text-error"></p>
        <label class="text-sm font-semibold sm:col-span-2">{{ $t['stamp_file'] }}<input type="file" name="file_stempel" accept=".png,.jpg,.jpeg,.svg" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm"></label>
        <p x-show="fieldErrors.file_stempel" x-text="fieldErrors.file_stempel?.[0]" class="text-sm text-error"></p>
        <button type="submit" :disabled="saving" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50 sm:col-span-2" x-text="saving ? i18n.uploading : i18n.save"></button>
    </form>
    @else
    <p class="rounded-xl border border-surface-container-highest p-4 text-sm">{{ $t['view_only'] }}</p>
    @endif
    <section class="space-y-3"><h2 class="text-lg font-bold">{{ $t['saved_files'] }}</h2><p x-show="!loading && signatures.length === 0" class="rounded-xl border border-surface-container-highest p-5 text-sm text-on-surface-variant">{{ $t['empty'] }}</p><template x-for="signature in signatures" :key="signature.id_signature"><article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-surface-container-highest bg-surface-container-lowest p-4"><div><p class="font-semibold" x-text="signature.role?.nama_role || signature.id_role"></p><p class="text-xs text-on-surface-variant" x-text="signature.file_ttd ? i18n.has_signature : i18n.no_signature"></p></div><div class="flex gap-3"><a :href="'/api/digital-signatures/role/' + signature.id_role + '/files/ttd'" class="act-btn"><span class="material-symbols-outlined" aria-hidden="true">draw</span>{{ $t['signature'] }}</a><a :href="'/api/digital-signatures/role/' + signature.id_role + '/files/stempel'" class="act-btn"><span class="material-symbols-outlined" aria-hidden="true">approval</span>{{ $t['stamp'] }}</a></div></article></template></section>
</main>
@endsection
