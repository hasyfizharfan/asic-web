@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => 'Detail abstrak', 'nav' => $secNav])

@section('content')
<main class="mx-auto max-w-4xl space-y-6" x-data="{
    submission: null, loading: true, loadError: '', issuing: false, actionError: '',
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/tim-abstrak/submissions/{{ (int) $id }}');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Detail abstrak gagal dimuat.'; return; }
        this.submission = response.data?.data;
    },
    async issueDocuments() {
        this.issuing = true; this.actionError = '';
        const response = await window.asicApi.post('/api/tim-abstrak/submissions/{{ (int) $id }}/issue-documents', {});
        this.issuing = false;
        if (!response.ok) { this.actionError = response.error || 'Dokumen gagal diterbitkan.'; window.asicApi.showToast(this.actionError); return; }
        await this.load();
        window.asicApi.showToast(response.data?.message || 'Dokumen seleksi diproses.', 'success');
    }
}">
    <a href="{{ route('portal.secretariat.abstracts.index') }}" class="text-sm font-semibold text-primary">← Kembali ke antrean abstrak</a>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat abstrak…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <template x-if="submission">
        <article class="space-y-5 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6">
            <header><p class="text-xs text-on-surface-variant">ID abstrak <span x-text="submission.id_abstract"></span></p><h1 class="mt-1 text-2xl font-bold" x-text="submission.judul_abstrak"></h1></header>
            <dl class="grid gap-3 text-sm sm:grid-cols-2"><div><dt class="text-on-surface-variant">Scope</dt><dd x-text="submission.scope"></dd></div><div><dt class="text-on-surface-variant">Presentasi</dt><dd x-text="submission.metode_presentasi"></dd></div><div><dt class="text-on-surface-variant">Status</dt><dd class="font-semibold" x-text="submission.status_abstrak"></dd></div><div><dt class="text-on-surface-variant">Tanggal submit</dt><dd x-text="submission.tanggal_submit || '—'"></dd></div></dl>
            <a :href="'/api/registrations/' + submission.id_registration + '/abstract/download'" class="inline-flex rounded-xl border border-primary px-4 py-2 text-sm font-semibold text-primary">Unduh manuskrip</a>
            <div class="rounded-xl bg-surface-container-low p-4"><h2 class="font-bold">Riwayat review</h2><p x-show="!submission.reviews?.length" class="mt-2 text-sm text-on-surface-variant">Belum ada review.</p><template x-for="review in submission.reviews || []" :key="review.id_review"><div class="mt-2 border-t border-surface-container-high pt-2 text-sm"><span class="font-semibold" x-text="review.hasil_review"></span><p x-text="review.catatan_review || 'Tanpa catatan'"></p></div></template></div>
            <div class="space-y-2 border-t border-surface-container-highest pt-4"><p x-show="actionError" x-text="actionError" role="alert" class="text-sm text-error"></p><button type="button" @click="issueDocuments()" :disabled="issuing || submission.status_abstrak === 'Diajukan'" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="issuing ? 'Memproses…' : 'Terbitkan / pastikan dokumen seleksi'"></button><p class="text-xs text-on-surface-variant">Aksi idempoten: dokumen yang sudah ada tidak dibuat ulang.</p></div>
        </article>
    </template>
</main>
@endsection
