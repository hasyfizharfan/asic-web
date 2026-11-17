@extends('layouts.portal', ['title' => 'Pengguna', 'role' => 'admin', 'roleName' => 'Admin', 'nav' => $adminNav])

@section('content')
<main class="space-y-5" x-data="{
    rows: [], roles: [], summary: null, governanceMatrix: null, selected: [], loading: true, error: '', search: '', roleFilter: '', page: 1, lastPage: 1,
    modal: false, saving: false, formError: '', editing: null, confirmModal: false, confirmAction: null,
    form: { id_role: '', email: '', password: '', full_name: '', affiliation: '', phone: '', job_title: '', status_akun: 'Aktif' },
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.error = '';
        const response = await window.asicApi.get('/api/admin/users', { search: this.search, id_role: this.roleFilter, page: this.page, per_page: 20 });
        this.loading = false;
        if (!response.ok) { this.error = response.error || 'Daftar pengguna gagal dimuat.'; return; }
        this.rows = response.data?.users || [];
        this.roles = response.data?.available_roles || [];
        this.summary = response.data?.summary || null;
        this.governanceMatrix = response.data?.governance_matrix || null;
        this.lastPage = response.data?.pagination?.last_page || 1;
        this.selected = this.selected.filter(id => this.rows.some(row => row.id_user === id));
    },
    openCreate() { this.editing = null; this.formError = ''; this.form = { id_role: this.roles[0]?.id_role || '', email: '', password: '', full_name: '', affiliation: '', phone: '', job_title: '', status_akun: 'Aktif' }; this.modal = true; },
    async openEdit(row) {
        this.formError = '';
        const response = await window.asicApi.get('/api/admin/users/' + row.id_user);
        if (!response.ok) { this.error = response.error || 'Detail pengguna gagal dimuat.'; return; }
        const user = response.data?.data || response.data?.user || {};
        this.editing = row.id_user;
        this.form = { id_role: user.id_role || row.id_role, email: user.email || row.email, password: '', full_name: user.participant?.full_name || user.name || row.name, affiliation: user.participant?.institution || row.affiliation, phone: user.participant?.phone || '', job_title: user.participant?.job_title || '', status_akun: user.status_akun || row.status_akun || (row.status === 'Active' ? 'Aktif' : 'Nonaktif') };
        this.modal = true;
    },
    async save() {
        this.saving = true; this.formError = '';
        const payload = { ...this.form };
        if (!payload.password) delete payload.password;
        const response = this.editing
            ? await window.asicApi.put('/api/admin/users/' + this.editing, payload)
            : await window.asicApi.post('/api/admin/users', payload);
        this.saving = false;
        if (!response.ok) { this.formError = response.error || Object.values(response.errors || {}).flat()[0] || 'Pengguna gagal disimpan.'; return; }
        this.modal = false; await this.load();
    },
    async toggle(row) {
        const response = await window.asicApi.patch('/api/admin/users/' + row.id_user + '/toggle-status', {});
        if (!response.ok) { this.error = response.error || 'Status pengguna gagal diubah.'; return; }
        await this.load();
    },
    async remove(row) {
        this.confirmAction = async () => {
            const response = await window.asicApi.delete('/api/admin/users/' + row.id_user);
            if (!response.ok) { this.error = response.error || 'Akun gagal dihapus.'; return; }
            await this.load();
        };
        this.confirmModal = true;
    },
    async batch(action) {
        if (!this.selected.length) return;
        const body = { action, user_ids: this.selected };
        if (action === 'change_role') body.id_role = Number(this.form.id_role);
        const response = await window.asicApi.post('/api/admin/users/batch-action', body);
        if (!response.ok) { this.error = response.error || 'Aksi massal gagal.'; return; }
        this.selected = []; await this.load();
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div><h1 class="text-2xl font-bold">Manajemen pengguna</h1><p class="mt-1 text-sm text-on-surface-variant">Data akun, peran, status, dan aksi massal dari backend.</p></div>
        <div class="flex gap-2"><a href="/api/admin/users/export-csv" class="rounded-xl border px-4 py-2 text-sm">Ekspor CSV</a><button @click="openCreate()" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah pengguna</button></div>
    </header>
    <section x-show="summary" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <article class="rounded-xl border p-4"><h2 class="text-xs text-on-surface-variant">Total akun</h2><p class="text-2xl font-bold" x-text="summary?.total_system_users?.count ?? 0"></p></article>
        <template x-for="(count, role) in (summary?.total_system_users?.breakdown || {})" :key="role"><article class="rounded-xl border p-4"><h2 class="text-xs capitalize text-on-surface-variant" x-text="role.replace('_', ' ')"></h2><p class="text-2xl font-bold" x-text="count"></p></article></template>
    </section>
    <section x-show="governanceMatrix" class="space-y-3 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <div><h2 class="font-semibold">Matriks tata kelola</h2><p class="text-sm text-on-surface-variant">Hak akses aktif untuk setiap kapabilitas dan peran.</p></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-left text-xs"><thead><tr class="border-b"><th class="p-2">Kapabilitas</th><template x-for="role in governanceMatrix?.roles || []" :key="role.id_role"><th class="p-2" x-text="role.name"></th></template></tr></thead><tbody><template x-for="capability in governanceMatrix?.capabilities || []" :key="capability.id"><tr class="border-b border-surface-container-highest"><td class="p-2" x-text="capability.title"></td><template x-for="role in governanceMatrix?.roles || []" :key="role.id_role"><td class="p-2"><span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold" :class="({ full_access: 'bg-[#E6F4EA] text-primary', view_only: 'bg-[#FEF6D8] text-[#7a5d00]' })[governanceMatrix?.matrix?.[capability.id]?.[role.id_role]] || 'bg-surface-container text-on-surface-variant'" x-text="({ full_access: @js(__('asic-admin.access.full_access')), view_only: @js(__('asic-admin.access.view_only')), restricted: @js(__('asic-admin.access.restricted')) })[governanceMatrix?.matrix?.[capability.id]?.[role.id_role] || 'restricted']"></span></td></template></tr></template></tbody></table></div>
    </section>
    <div class="flex flex-wrap gap-2"><input x-model="search" @keydown.enter.prevent="page=1; load()" placeholder="Cari nama atau email" class="rounded-lg border-surface-container-highest text-sm"><select x-model="roleFilter" @change="page=1; load()" class="rounded-lg border-surface-container-highest text-sm"><option value="">Semua peran</option><template x-for="role in roles" :key="role.id_role"><option :value="role.id_role" x-text="role.nama_role"></option></template></select><button @click="page=1; load()" class="rounded-lg bg-surface-container px-4 py-2 text-sm">Cari</button></div>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat pengguna…</p>
    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-error"></p>
    <div x-show="selected.length" class="flex flex-wrap items-center gap-2 rounded-xl border p-3 text-sm"><span x-text="selected.length + ' dipilih'"></span><button @click="batch('activate')" class="rounded border px-3 py-1">Aktifkan</button><button @click="batch('deactivate')" class="rounded border px-3 py-1">Nonaktifkan</button><select x-model="form.id_role" class="rounded border text-sm"><option value="">Pilih peran</option><template x-for="role in roles" :key="role.id_role"><option :value="role.id_role" x-text="role.nama_role"></option></template></select><button @click="batch('change_role')" class="rounded border px-3 py-1">Ubah peran</button><button @click="batch('delete')" class="rounded border border-error px-3 py-1 text-error">Hapus</button></div>
    <div x-show="!loading && !error && rows.length === 0" class="rounded-xl border p-8 text-center text-sm text-on-surface-variant">Belum ada pengguna untuk filter ini.</div>
    <div x-show="!loading && !error && rows.length" class="overflow-x-auto rounded-xl border bg-surface-container-lowest"><table class="w-full text-left text-sm"><thead><tr class="border-b text-xs uppercase text-on-surface-variant"><th class="p-3"><input type="checkbox" @change="selected = $event.target.checked ? rows.map(r => r.id_user) : []"></th><th class="p-3">Nama / Email</th><th class="p-3">Peran</th><th class="p-3">Afiliasi</th><th class="p-3">Status</th><th class="p-3">Aksi</th></tr></thead><tbody><template x-for="row in rows" :key="row.id_user"><tr class="border-b border-surface-container-highest"><td class="p-3"><input type="checkbox" :value="row.id_user" x-model.number="selected"></td><td class="p-3"><div class="font-semibold" x-text="row.name"></div><div class="text-xs text-on-surface-variant" x-text="row.email"></div></td><td class="p-3" x-text="row.indonesian_role"></td><td class="p-3" x-text="row.affiliation"></td><td class="p-3" x-text="row.status_akun"></td><td class="space-x-2 p-3"><button type="button" @click="openEdit(row)" class="icon-btn" aria-label="Ubah"><span class="material-symbols-outlined" aria-hidden="true">edit</span></button><button type="button" @click="toggle(row)" class="icon-btn icon-btn-muted" :aria-label="row.status_akun === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan'"><span class="material-symbols-outlined" aria-hidden="true" x-text="row.status_akun === 'Aktif' ? 'person_off' : 'person_check'"></span></button><button type="button" @click="remove(row)" class="icon-btn icon-btn-danger" aria-label="Hapus"><span class="material-symbols-outlined" aria-hidden="true">delete</span></button></td></tr></template></tbody></table></div>
    <nav class="flex items-center justify-end gap-3 text-sm"><button :disabled="page <= 1" @click="page--; load()" class="rounded border px-3 py-1 disabled:opacity-40">Sebelumnya</button><span x-text="'Halaman ' + page + ' / ' + lastPage"></span><button :disabled="page >= lastPage" @click="page++; load()" class="rounded border px-3 py-1 disabled:opacity-40">Berikutnya</button></nav>

    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="modal=false"><section class="w-full max-w-lg space-y-4 rounded-2xl bg-white p-6"><h2 class="text-lg font-bold" x-text="editing ? 'Ubah pengguna' : 'Tambah pengguna'"></h2><p x-show="formError" x-text="formError" role="alert" class="text-sm text-error"></p><form @submit.prevent="save()" class="grid gap-3 sm:grid-cols-2"><label class="space-y-1 text-sm">Nama lengkap<input required x-model="form.full_name" class="w-full rounded-lg border-surface-container-highest"></label><label class="space-y-1 text-sm">Email<input required type="email" x-model="form.email" class="w-full rounded-lg border-surface-container-highest"></label><label class="space-y-1 text-sm">Peran<select required x-model="form.id_role" class="w-full rounded-lg border-surface-container-highest"><template x-for="role in roles" :key="role.id_role"><option :value="role.id_role" x-text="role.nama_role"></option></template></select></label><label class="space-y-1 text-sm">Status<select x-model="form.status_akun" class="w-full rounded-lg border-surface-container-highest"><option>Aktif</option><option>Nonaktif</option></select></label><label class="space-y-1 text-sm">Afiliasi<input x-model="form.affiliation" class="w-full rounded-lg border-surface-container-highest"></label><label class="space-y-1 text-sm">Telepon<input x-model="form.phone" class="w-full rounded-lg border-surface-container-highest"></label><label class="space-y-1 text-sm">Jabatan<input x-model="form.job_title" class="w-full rounded-lg border-surface-container-highest"></label><label class="space-y-1 text-sm">Kata sandi <span x-show="editing">(kosongkan jika tidak diubah)</span><input :required="!editing" minlength="8" type="password" x-model="form.password" class="w-full rounded-lg border-surface-container-highest"></label><div class="col-span-full flex justify-end gap-2"><button type="button" @click="modal=false" class="rounded-lg border px-4 py-2 text-sm">Batal</button><button :disabled="saving" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white" x-text="saving ? 'Menyimpan…' : 'Simpan'"></button></div></form></section></div>
    <div x-show="confirmModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4" @click.self="confirmModal=false"><section role="dialog" aria-modal="true" class="w-full max-w-md space-y-4 rounded-2xl bg-white p-6"><h2 class="text-lg font-bold">Konfirmasi penghapusan</h2><p class="text-sm text-on-surface-variant">Akun yang dipilih akan dihapus jika permintaan berhasil.</p><div class="flex justify-end gap-2"><button type="button" @click="confirmModal=false" class="rounded-lg border px-4 py-2 text-sm">Batal</button><button type="button" @click="confirmModal=false; confirmAction?.()" class="rounded-lg bg-error px-4 py-2 text-sm font-semibold text-white">Hapus akun</button></div></section></div>
</main>
@endsection
