@php($t = trans('asic-admin.permissions_page'))
@extends('layouts.portal', ['title' => $t['title'], 'role' => 'admin', 'roleName' => $t['title'], 'nav' => $adminNav])

@section('content')
<main class="space-y-6" x-data="{
    i18n: @js($t),
    matrixData: null, draft: null, loading: true, saving: false, loadError: '', formError: '', auditNote: '',
    auditRows: [], auditLoading: false, auditError: '', auditAction: '', auditPage: 1, auditLastPage: 1,
    async init() { await Promise.all([this.loadMatrix(), this.loadAudit()]); },
    async loadMatrix() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/admin/roles-permissions'); this.loading = false;
        if (!response.ok) { this.loadError = response.error || i18n.load_failed; return; }
        const data = response.data?.data || {}; this.draft = JSON.parse(JSON.stringify(data.matrix || {})); this.matrixData = data;
    },
    async loadAudit() {
        this.auditLoading = true; this.auditError = '';
        const response = await window.asicApi.get('/api/admin/roles-permissions/audit-log', { page: this.auditPage, per_page: 10, action: this.auditAction });
        this.auditLoading = false;
        if (!response.ok) { this.auditError = response.error || i18n.audit_failed; return; }
        this.auditRows = response.data?.data || []; this.auditLastPage = response.data?.pagination?.last_page || 1;
    },
    async save() {
        if (!this.draft || !Object.keys(this.draft).length) return;
        this.saving = true; this.formError = '';
        const response = await window.asicApi.post('/api/admin/roles-permissions', { matrix: this.draft, audit_note: this.auditNote });
        this.saving = false;
        if (!response.ok) { this.formError = response.error || i18n.save_failed; return; }
        const saved = JSON.parse(JSON.stringify(response.data?.data?.matrix || this.draft)); this.matrixData.matrix = saved; this.draft = JSON.parse(JSON.stringify(saved));
        await this.loadAudit(); window.asicApi.showToast(response.data?.message || i18n.saved, 'success');
    },
    async reset() {
        this.formError = '';
        const response = await window.asicApi.post('/api/admin/roles-permissions/reset', {});
        if (!response.ok) { this.formError = response.error || i18n.reset_failed; return; }
        await Promise.all([this.loadMatrix(), this.loadAudit()]);
        window.asicApi.showToast(response.data?.message || i18n.reset_done, 'success');
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></div><div class="flex gap-2"><a href="/api/admin/roles-permissions/audit-log" class="rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold">{{ $t['export_audit'] }}</a><a href="/api/admin/roles-permissions/export" class="rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold">{{ $t['export_matrix'] }}</a></div></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p><p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p><p x-show="formError" x-text="formError" role="alert" class="text-sm text-error"></p>
    <section x-show="matrixData" class="space-y-4">
        <label class="block text-sm font-semibold">{{ $t['audit_note'] }}<input x-model="auditNote" class="mt-1 block w-full rounded-xl border-surface-container-highest" placeholder="{{ $t['audit_note_placeholder'] }}"></label>
        <div class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest"><table class="w-full min-w-[760px] text-left text-sm"><thead><tr class="border-b border-surface-container-high"><th class="p-3">{{ $t['capability'] }}</th><template x-for="role in matrixData?.roles || []" :key="role.key"><th class="p-3" x-text="role.name"></th></template></tr></thead><tbody><template x-for="capability in matrixData?.capabilities || []" :key="capability.id"><tr class="border-b border-surface-container-highest"><td class="p-3"><p class="font-semibold" x-text="capability.title"></p><p class="text-xs text-on-surface-variant" x-text="capability.description"></p></td><template x-for="role in matrixData?.roles || []" :key="role.key"><td class="p-3"><select x-model="draft[capability.id][role.key]" class="rounded-lg border-surface-container-highest text-xs"><option value="full_access">{{ $t['full_access'] }}</option><option value="view_only">{{ $t['view_only'] }}</option><option value="restricted">{{ $t['restricted'] }}</option></select></td></template></tr></template></tbody></table></div>
        <div class="flex gap-3"><button type="button" @click="save()" :disabled="saving" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="saving ? i18n.saving : i18n.save"></button><button type="button" @click="reset()" class="rounded-xl border border-surface-container-highest px-5 py-2.5 text-sm font-semibold">{{ $t['reset'] }}</button></div>
    </section>
    <section class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <div class="flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-lg font-bold">{{ $t['audit_title'] }}</h2><p class="text-sm text-on-surface-variant">{{ $t['audit_order'] }}</p></div><label class="text-sm">{{ $t['filter_action'] }}<input x-model="auditAction" @keydown.enter.prevent="auditPage=1; loadAudit()" class="ml-2 rounded-lg border-surface-container-highest"></label><button type="button" @click="auditPage=1; loadAudit()" class="rounded-lg border px-3 py-2 text-sm">{{ $t['filter'] }}</button></div>
        <p x-show="auditLoading" role="status" class="text-sm">{{ $t['audit_loading'] }}</p><p x-show="auditError" x-text="auditError" role="alert" class="text-sm text-error"></p><p x-show="!auditLoading && !auditError && auditRows.length === 0" class="text-sm text-on-surface-variant">{{ $t['audit_empty'] }}</p>
        <div x-show="!auditLoading && auditRows.length" class="overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm"><thead><tr class="border-b"><th class="p-2">{{ $t['col_time'] }}</th><th class="p-2">{{ $t['col_action'] }}</th><th class="p-2">{{ $t['col_user'] }}</th><th class="p-2">{{ $t['col_note'] }}</th></tr></thead><tbody><template x-for="row in auditRows" :key="row.id"><tr class="border-b border-surface-container-highest"><td class="p-2" x-text="row.timestamp"></td><td class="p-2" x-text="row.action"></td><td class="p-2" x-text="row.user"></td><td class="p-2" x-text="row.note || '—'"></td></tr></template></tbody></table></div>
        <div class="flex items-center justify-end gap-3 text-sm"><button :disabled="auditPage <= 1" @click="auditPage--; loadAudit()" class="rounded border px-3 py-1 disabled:opacity-40">{{ $t['prev'] }}</button><span x-text="i18n.page + ' ' + auditPage + ' / ' + auditLastPage"></span><button :disabled="auditPage >= auditLastPage" @click="auditPage++; loadAudit()" class="rounded border px-3 py-1 disabled:opacity-40">{{ $t['next'] }}</button></div>
    </section>
</main>
@endsection
