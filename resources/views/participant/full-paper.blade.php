@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Berkas peserta', 'nav' => $participantNav])

@section('content')
<main class="mx-auto max-w-4xl space-y-6" x-data="{
    registrations: [], registration: null, files: [], loading: true, uploading: false, loadError: '', fieldErrors: {},
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/registrations');
        if (!response.ok) { this.loadError = response.error || 'Registrasi gagal dimuat.'; this.loading = false; return; }
        this.registrations = response.data?.data?.data || [];
        const id = new URLSearchParams(window.location.search).get('id_registration');
        this.registration = this.registrations.find(row => String(row.id_registration) === String(id)) || this.registrations[0] || null;
        if (!this.registration) { this.loadError = 'Belum ada registrasi untuk mengunggah berkas.'; this.files = []; this.loading = false; return; }
        const fileResponse = await window.asicApi.get('/api/registrations/' + this.registration.id_registration + '/files');
        this.loading = false;
        if (!fileResponse.ok) { this.loadError = fileResponse.error || 'Berkas gagal dimuat.'; return; }
        this.files = fileResponse.data?.data || [];
    },
    async upload(event) {
        this.fieldErrors = {}; this.uploading = true;
        const response = await window.asicApi.upload('/api/registrations/' + this.registration.id_registration + '/files', new FormData(event.target));
        this.uploading = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; window.asicApi.showToast(response.error || 'Berkas gagal diunggah.'); return; }
        event.target.reset(); await this.load();
        window.asicApi.showToast(response.data?.message || 'Berkas berhasil diunggah.', 'success');
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">Full paper dan presentasi</h1><p class="mt-1 text-sm text-on-surface-variant">Berkas peserta disimpan privat dan dapat diakses melalui tautan terotorisasi.</p></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat berkas…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <section x-show="registration" class="space-y-5">
        <form class="grid gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 sm:grid-cols-2" @submit.prevent="upload($event)">
            <label class="block text-sm font-semibold">Jenis berkas
                <select name="jenis_file" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option value="Artikel">Full paper / Artikel</option><option value="Presentasi">Presentasi</option></select>
            </label>
            <label class="block text-sm font-semibold">Pilih berkas (PDF, DOC, DOCX, PPT, PPTX; maks. 10 MB)
                <input name="file" type="file" required accept=".pdf,.doc,.docx,.ppt,.pptx" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
            </label>
            <p x-show="fieldErrors.jenis_file" x-text="fieldErrors.jenis_file?.[0]" class="text-sm text-error"></p><p x-show="fieldErrors.file" x-text="fieldErrors.file?.[0]" class="text-sm text-error"></p>
            <button type="submit" :disabled="uploading" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50 sm:col-span-2" x-text="uploading ? 'Mengunggah…' : 'Unggah berkas'"></button>
        </form>
        <div class="space-y-3"><h2 class="text-lg font-bold">Berkas terunggah</h2><p x-show="!loading && files.length === 0" class="rounded-xl border border-surface-container-highest p-5 text-sm text-on-surface-variant">Belum ada berkas.</p>
            <template x-for="file in files" :key="file.id_file"><article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-surface-container-highest bg-surface-container-lowest p-4"><div><p class="font-semibold" x-text="file.nama_file"></p><p class="text-xs text-on-surface-variant" x-text="file.jenis_file + ' · ' + file.status_file + ' · ' + (file.tanggal_upload || '')"></p></div><a :href="'/api/participant-files/' + file.id_file + '/download'" class="font-semibold text-primary">Unduh</a></article></template>
        </div>
    </section>
</main>
@endsection
