@extends('layouts.portal', ['role' => 'secretariat', 'title' => __('asic-secretariat.detail.page_title').' — ASIC 2027'])

@section('content')
<main class="space-y-6" x-data="{
    submission: null, loading: true, loadError: '', issuing: false, actionError: '', warnings: [],
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const r = await window.asicApi.get('/api/tim-abstrak/submissions/{{ (int) $id }}'); this.loading = false;
        if (!r.ok) { this.loadError = r.error || 'Detail abstrak gagal dimuat.'; return; }
        this.submission = r.data?.data;
    },
    participant() { return this.submission?.registration?.participant || {}; },
    email() { return this.participant()?.user_account?.email || this.participant()?.userAccount?.email || '—'; },
    async issueDocuments() {
        if (!this.submission || this.submission.status_abstrak === 'Diajukan') return;
        this.issuing = true; this.actionError = '';
        const r = await window.asicApi.post('/api/tim-abstrak/submissions/{{ (int) $id }}/issue-documents', {}); this.issuing = false;
        if (!r.ok) { this.actionError = r.status === 409 ? 'Abstrak masih menunggu putusan Tim Abstrak.' : (r.error || 'Dokumen gagal diterbitkan.'); await this.load(); return; }
        this.warnings = r.data?.data?.warnings || []; await this.load();
        window.asicApi.showToast(r.data?.message || 'Dokumen diproses.', 'success');
    }
}">
    <a href="{{ route('portal.secretariat.abstracts.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary"><span class="material-symbols-outlined">arrow_back</span>{{ __('asic-secretariat.detail.back_to_list') }}</a>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat abstrak…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-error"></p>
    <template x-if="submission"><div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">
        <section class="space-y-6 xl:col-span-8">
            <article class="space-y-5 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6 sm:p-8">
                <header class="space-y-2 border-b border-surface-container-highest pb-5"><div class="inline-flex rounded-full bg-primary/10 px-3 py-1 font-mono text-xs font-bold text-primary" x-text="'Abstract #' + submission.id_abstract"></div><h1 class="text-2xl font-bold leading-tight text-on-surface sm:text-3xl" x-text="submission.judul_abstrak"></h1><div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-on-surface-variant"><span>Bidang: <strong class="text-on-surface" x-text="submission.scope || '—'"></strong></span><span>Metode: <strong class="text-on-surface" x-text="submission.metode_presentasi || '—'"></strong></span><span>Status: <strong class="text-on-surface" x-text="submission.status_abstrak"></strong></span><span>Dikirim: <strong class="text-on-surface" x-text="asicDateTime(submission.tanggal_submit)"></strong></span></div></header>
                <div class="grid grid-cols-1 gap-4 rounded-xl bg-surface-container-low p-4 sm:grid-cols-2"><div><h2 class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">{{ __('asic-secretariat.detail.authors_section') }}</h2><p class="mt-2 font-semibold" x-text="participant()?.full_name || [participant()?.first_name, participant()?.last_name].filter(Boolean).join(' ') || '—'"></p><p class="text-sm text-on-surface-variant" x-text="participant()?.institution || '—'"></p></div><div><h2 class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Kontak & registrasi</h2><p class="mt-2 text-sm" x-text="email()"></p><p class="text-sm text-on-surface-variant" x-text="'ID registrasi: ' + (submission.id_registration || '—')"></p></div></div>
                <div><h2 class="text-sm font-bold uppercase tracking-wide">{{ __('asic-secretariat.detail.manuscript_title') }}</h2><p class="mt-2 text-sm text-on-surface-variant">{{ __('asic-secretariat.detail.manuscript_reviewer_only') }}</p></div>
            </article>
            <article class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6"><h2 class="text-lg font-bold">Riwayat review lengkap</h2><p x-show="!submission.reviews?.length" class="text-sm text-on-surface-variant">Belum ada riwayat review.</p><template x-for="review in submission.reviews || []" :key="review.id_review"><div class="rounded-xl bg-surface-container-low p-4"><div class="flex flex-wrap justify-between gap-2 text-sm"><strong x-text="review.hasil_review"></strong><time class="text-xs text-on-surface-variant" x-text="asicDateTime(review.tanggal_review)"></time></div><p class="mt-2 whitespace-pre-line text-sm text-on-surface-variant" x-text="review.catatan_review || 'Tanpa catatan.'"></p><p class="mt-2 text-xs text-on-surface-variant" x-text="'Reviewer: ' + (review.reviewer?.email || '—')"></p></div></template></article>
        </section>
        <aside class="space-y-5 xl:col-span-4">
            <article class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6"><h2 class="text-lg font-bold">{{ __('asic-secretariat.detail.decision_title') }}</h2><p class="text-sm text-on-surface-variant">{{ __('asic-secretariat.detail.decision_notice') }}</p><p class="rounded-lg bg-surface-container-low p-3 text-sm">Putusan abstrak hanya dapat diberikan oleh Tim Abstrak. Sekretariat dapat menerbitkan ulang dokumen setelah putusan tersedia.</p><div x-show="actionError" x-text="actionError" role="alert" class="text-sm text-error"></div><div x-show="warnings.length" class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900"><strong>Peringatan</strong><template x-for="(warning,index) in warnings" :key="index"><p class="mt-1" x-text="warning"></p></template></div><button @click="issueDocuments()" :disabled="issuing || submission.status_abstrak === 'Diajukan'" class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" x-text="issuing ? 'Memproses…' : 'Terbitkan / pastikan dokumen resmi'"></button><p x-show="submission.status_abstrak === 'Diajukan'" class="text-xs text-on-surface-variant">Tidak tersedia sebelum abstrak diputuskan.</p><p class="text-xs text-on-surface-variant">Penerbitan idempoten; dokumen yang sudah ada tidak diduplikasi.</p></article>
        </aside>
    </div></template>
</main>
@endsection
