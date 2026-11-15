@extends('layouts.portal', ['role' => 'admin', 'roleName' => 'Matriks Permission', 'nav' => $adminNav])

@section('content')
<main class="space-y-6" x-data="{
    matrixData: null, draft: null, loading: true, saving: false, loadError: '', formError: '', auditNote: '',
    async init() {
        const response = await window.asicApi.get('/api/admin/roles-permissions'); this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Matriks permission gagal dimuat.'; return; }
        this.matrixData = response.data?.data; this.draft = structuredClone(this.matrixData?.matrix || {});
    },
    async save() {
        this.saving = true; this.formError = '';
        const response = await window.asicApi.post('/api/admin/roles-permissions', { matrix: this.draft, audit_note: this.auditNote });
        this.saving = false;
        if (!response.ok) { this.formError = response.error || 'Perubahan permission gagal disimpan.'; return; }
        this.matrixData.matrix = response.data?.data?.matrix || this.draft;
        this.draft = structuredClone(this.matrixData.matrix);
        window.asicApi.showToast(response.data?.message || 'Matriks permission disimpan.', 'success');
    },
    async reset() {
        this.formError = '';
        const response = await window.asicApi.post('/api/admin/roles-permissions/reset', {});
        if (!response.ok) { this.formError = response.error || 'Reset matriks gagal.'; return; }
        const loaded = await window.asicApi.get('/api/admin/roles-permissions');
        if (!loaded.ok) { this.formError = loaded.error || 'Matriks baru gagal dimuat.'; return; }
        this.matrixData = loaded.data?.data; this.draft = structuredClone(this.matrixData?.matrix || {});
        window.asicApi.showToast(response.data?.message || 'Matriks direset.', 'success');
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-2xl font-bold text-on-surface">Matriks role dan permission</h1><p class="mt-1 text-sm text-on-surface-variant">Perubahan dikirim ke PermissionMatrix backend.</p></div><a href="/api/admin/roles-permissions/export" class="rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold">Ekspor audit</a></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat matriks…</p><p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p><p x-show="formError" x-text="formError" role="alert" class="text-sm text-error"></p>
    <section x-show="matrixData" class="space-y-4">
        <label class="block text-sm font-semibold">Catatan audit<input x-model="auditNote" class="mt-1 block w-full rounded-xl border-surface-container-highest" placeholder="Alasan perubahan permission"></label>
        <div class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest"><table class="w-full min-w-[760px] text-left text-sm"><thead><tr class="border-b border-surface-container-high"><th class="p-3">Capability</th><template x-for="role in matrixData?.roles || []" :key="role.key"><th class="p-3" x-text="role.name"></th></template></tr></thead><tbody><template x-for="capability in matrixData?.capabilities || []" :key="capability.id"><tr class="border-b border-surface-container-highest"><td class="p-3"><p class="font-semibold" x-text="capability.title"></p><p class="text-xs text-on-surface-variant" x-text="capability.description"></p></td><template x-for="role in matrixData?.roles || []" :key="role.key"><td class="p-3"><select x-model="draft[capability.id][role.key]" class="rounded-lg border-surface-container-highest text-xs"><option value="full_access">Full access</option><option value="view_only">View only</option><option value="restricted">Restricted</option></select></td></template></tr></template></tbody></table></div>
        <div class="flex gap-3"><button type="button" @click="save()" :disabled="saving" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="saving ? 'Menyimpan…' : 'Simpan matriks'"></button><button type="button" @click="reset()" class="rounded-xl border border-surface-container-highest px-5 py-2.5 text-sm font-semibold">Reset bawaan</button></div>
    </section>
</main>
@endsection
