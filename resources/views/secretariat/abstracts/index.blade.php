@extends('layouts.portal', ['role' => 'secretariat', 'title' => __('asic-secretariat.abstracts.title').' — ASIC 2027'])

@section('content')
<div class="relative flex w-full flex-col space-y-6" x-data="{
    drawerOpen: false, selectedAbstract: null, abstracts: [], events: [], stats: {total:0,pending:0,accepted:0,rejected:0,documents_pending:0},
    searchQuery: '', statusFilter: '', scopeFilter: '', eventFilter: '', page: 1, lastPage: 1, total: 0,
    loading: true, loadError: '', issuing: false, actionError: '', warnings: [], timer: null,
    async init() { const e = await window.asicApi.get('/api/content/events', {per_page: 100}); this.events = e.data?.data || []; await this.loadPage(); },
    params(withPage = true) { const p = {per_page: 15}; if (withPage) p.page = this.page; if (this.searchQuery.trim()) p.search = this.searchQuery.trim(); if (this.statusFilter) p.status_abstrak = this.statusFilter; if (this.scopeFilter) p.scope = this.scopeFilter; if (this.eventFilter) p.id_event = this.eventFilter; return p; },
    async loadPage() {
        this.loading = true; this.loadError = '';
        const r = await window.asicApi.get('/api/tim-abstrak/submissions', this.params()); this.loading = false;
        if (!r.ok) { this.loadError = r.error || 'Daftar abstrak gagal dimuat.'; return; }
        const pageData = r.data?.data || {}; this.abstracts = pageData.data || []; this.stats = r.data?.stats || this.stats;
        this.page = pageData.current_page || 1; this.lastPage = pageData.last_page || 1; this.total = pageData.total || 0;
        if (this.selectedAbstract) this.selectedAbstract = this.abstracts.find(a => a.id_abstract === this.selectedAbstract.id_abstract) || this.selectedAbstract;
    },
    filtersChanged() { clearTimeout(this.timer); this.timer = setTimeout(() => { this.page = 1; this.loadPage(); }, 300); },
    exportUrl() { const p = new URLSearchParams(); if (this.searchQuery.trim()) p.set('search', this.searchQuery.trim()); if (this.statusFilter) p.set('status', this.statusFilter); if (this.scopeFilter) p.set('scope', this.scopeFilter); if (this.eventFilter) p.set('id_event', this.eventFilter); return '/api/exports/abstracts?' + p.toString(); },
    openDrawer(item) { this.selectedAbstract = item; this.actionError = ''; this.warnings = []; this.drawerOpen = true; },
    async issueDocuments() {
        if (!this.selectedAbstract || this.selectedAbstract.status_abstrak === 'Diajukan') return;
        this.issuing = true; this.actionError = '';
        const r = await window.asicApi.post(`/api/tim-abstrak/submissions/${this.selectedAbstract.id_abstract}/issue-documents`, {});
        this.issuing = false;
        if (!r.ok) { this.actionError = r.error || 'Penerbitan dokumen gagal.'; if (r.status === 409) await this.loadPage(); return; }
        this.warnings = r.data?.data?.warnings || []; await this.loadPage();
        window.asicApi.showToast(r.data?.message || 'Dokumen diproses.', 'success');
    },
    authorName(item) { return item.registration?.participant?.full_name || [item.registration?.participant?.first_name, item.registration?.participant?.last_name].filter(Boolean).join(' ') || '—'; },
    authorEmail(item) { return item.registration?.participant?.user_account?.email || item.registration?.participant?.userAccount?.email || '—'; }
}">
    <header class="flex flex-col gap-2 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-xs font-mono font-bold uppercase tracking-wider text-primary">{{ __('asic-secretariat.faculty_unand') }}</p><h1 class="mt-2 text-2xl font-bold text-on-surface">{{ __('asic-secretariat.abstracts.title') }}</h1><p class="mt-1 max-w-2xl text-sm text-on-surface-variant">{{ __('asic-secretariat.abstracts.subtitle') }}</p></div>
        <a :href="exportUrl()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white"><span class="material-symbols-outlined text-lg">download</span>{{ __('asic-secretariat.abstracts.export_btn') }}</a>
    </header>

    <section class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
        @foreach([['Total Pengajuan','total','library_books'],['Diajukan','pending','inbox'],['Diterima','accepted','check_circle'],['Ditolak','rejected','cancel'],['Dokumen belum terbit','documents_pending','pending_actions']] as [$label,$key,$icon])
        <article class="rounded-xl border border-surface-container-high bg-surface-container-lowest p-4"><div class="flex items-center justify-between text-xs text-on-surface-variant"><span>{{ $label }}</span><span class="material-symbols-outlined text-primary">{{ $icon }}</span></div><div class="mt-2 text-2xl font-bold font-mono text-on-surface" x-text="stats.{{ $key }}">0</div></article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-surface-container-highest bg-surface-container-lowest">
        <div class="flex flex-col gap-3 border-b border-surface-container-highest p-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="relative min-w-64 flex-1"><span class="material-symbols-outlined absolute left-3 top-2.5 text-lg text-on-surface-variant">search</span><input type="search" x-model="searchQuery" @input="filtersChanged()" @keydown.enter.prevent="loadPage()" placeholder="{{ __('asic-secretariat.abstracts.search_placeholder') }}" class="w-full rounded-xl border border-outline-variant bg-surface-container-low py-2.5 pl-10 pr-4 text-sm"></div>
            <div class="flex flex-wrap gap-2">
                <select x-model="statusFilter" @change="filtersChanged()" class="rounded-lg border border-outline-variant bg-surface-container-low px-3 py-2 text-sm"><option value="">Semua status</option><option value="Diajukan">Diajukan</option><option value="Diterima">Diterima</option><option value="Ditolak">Ditolak</option></select>
                <select x-model="scopeFilter" @change="filtersChanged()" class="rounded-lg border border-outline-variant bg-surface-container-low px-3 py-2 text-sm"><option value="">Semua bidang</option>@foreach($scopes as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select>
                <select x-model="eventFilter" @change="filtersChanged()" class="rounded-lg border border-outline-variant bg-surface-container-low px-3 py-2 text-sm"><option value="">Semua event</option><template x-for="event in events" :key="event.id_event"><option :value="event.id_event" x-text="event.nama_event"></option></template></select>
            </div>
        </div>
        <div class="overflow-x-auto"><table class="w-full min-w-[1050px] text-left text-sm">
            <thead class="border-b border-surface-container-high bg-surface-container-low text-xs font-semibold uppercase tracking-wide text-on-surface-variant"><tr><th class="p-4">ID</th><th class="p-4">Judul & bidang</th><th class="p-4">Penulis & institusi</th><th class="p-4">Metode</th><th class="p-4">Tanggal submit</th><th class="p-4">Status</th><th class="p-4 text-right">Tindakan</th></tr></thead>
            <tbody class="divide-y divide-surface-container-high">
                <tr x-show="loading"><td colspan="7" class="p-8 text-center text-on-surface-variant">Memuat abstrak…</td></tr>
                <tr x-show="loadError"><td colspan="7" x-text="loadError" role="alert" class="p-8 text-center text-error"></td></tr>
                <template x-for="item in abstracts" :key="item.id_abstract"><tr class="cursor-pointer hover:bg-surface-container-low/60" @click="openDrawer(item)">
                    <td class="whitespace-nowrap p-4 font-mono font-bold text-primary" x-text="'#' + item.id_abstract"></td>
                    <td class="max-w-md p-4"><div class="line-clamp-2 font-semibold text-on-surface" x-text="item.judul_abstrak"></div><div class="mt-1 text-xs text-on-surface-variant" x-text="'Bidang: ' + (item.scope || '—')"></div></td>
                    <td class="p-4"><div class="font-medium" x-text="authorName(item)"></div><div class="text-xs text-on-surface-variant" x-text="item.registration?.participant?.institution || '—'"></div></td>
                    <td class="p-4" x-text="item.metode_presentasi || '—'"></td><td class="whitespace-nowrap p-4 text-xs text-on-surface-variant" x-text="item.tanggal_submit || '—'"></td>
                    <td class="p-4"><span class="rounded-full bg-surface-container-high px-2.5 py-1 text-xs font-semibold" x-text="item.status_abstrak"></span></td>
                    <td class="whitespace-nowrap p-4 text-right" @click.stop><a :href="'{{ url('/secretariat/abstracts') }}/' + item.id_abstract" class="inline-flex items-center gap-1 rounded-lg bg-surface-container-low px-3 py-2 text-xs font-semibold text-primary"><span>{{ __('asic-secretariat.abstracts.btn_review') }}</span><span class="material-symbols-outlined text-sm">arrow_forward</span></a></td>
                </tr></template>
                <tr x-show="!loading && !loadError && abstracts.length === 0"><td colspan="7" class="p-10 text-center text-sm text-on-surface-variant">Belum ada abstrak yang sesuai filter.</td></tr>
            </tbody>
        </table></div>
        <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-surface-container-high p-4 text-xs text-on-surface-variant"><span x-text="`Menampilkan ${abstracts.length} dari ${total} abstrak`"></span><div class="flex items-center gap-2"><button @click="page=Math.max(1,page-1); loadPage()" :disabled="page<=1 || loading" class="rounded-lg border px-3 py-2 disabled:opacity-40">Sebelumnya</button><span x-text="`${page} / ${lastPage}`"></span><button @click="page=Math.min(lastPage,page+1); loadPage()" :disabled="page>=lastPage || loading" class="rounded-lg border px-3 py-2 disabled:opacity-40">Berikutnya</button></div></footer>
    </section>

    <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-40 bg-black/40" @click="drawerOpen=false"></div>
    <aside x-show="drawerOpen" x-cloak class="fixed inset-y-0 right-0 z-50 flex w-full flex-col justify-between overflow-y-auto bg-surface-container-lowest shadow-2xl sm:w-[420px]" @click.stop>
        <div class="space-y-5 p-6"><div class="flex items-center justify-between border-b border-surface-container-high pb-4"><span class="text-xs font-bold uppercase tracking-wider text-primary">{{ __('asic-secretariat.abstracts.fast_screening') }}</span><button @click="drawerOpen=false" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button></div>
            <template x-if="selectedAbstract"><div class="space-y-4"><div class="rounded-xl bg-surface-container-low p-4"><span class="font-mono text-xs font-semibold text-primary" x-text="'#' + selectedAbstract.id_abstract"></span><h2 class="mt-1 font-bold" x-text="selectedAbstract.judul_abstrak"></h2><p class="mt-1 text-xs text-on-surface-variant" x-text="`${selectedAbstract.scope || '—'} · ${selectedAbstract.metode_presentasi || '—'} · ${selectedAbstract.status_abstrak}`"></p><p class="mt-2 text-sm" x-text="authorName(selectedAbstract)"></p><p class="text-xs text-on-surface-variant" x-text="selectedAbstract.registration?.participant?.institution || '—'"></p><p class="text-xs text-on-surface-variant" x-text="authorEmail(selectedAbstract)"></p></div>
                <div class="rounded-xl border border-dashed border-outline-variant p-4"><h3 class="text-xs font-bold uppercase">Checklist meja seleksi</h3><p class="mt-2 text-sm text-on-surface-variant">Belum tersedia: checklist tidak disimpan di backend.</p></div>
                <div class="rounded-xl border border-dashed border-outline-variant p-4"><h3 class="text-xs font-bold uppercase">Catatan internal</h3><p class="mt-2 text-sm text-on-surface-variant">Belum tersedia: catatan internal tidak memiliki endpoint penyimpanan.</p></div>
                <div x-show="actionError" x-text="actionError" role="alert" class="rounded-lg bg-error-container p-3 text-sm text-error"></div>
                <div x-show="warnings.length" class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900"><strong>Peringatan tindak lanjut</strong><template x-for="(warning,index) in warnings" :key="index"><p class="mt-1" x-text="warning"></p></template></div>
            </div></template>
        </div>
        <div class="space-y-2 border-t border-surface-container-high bg-surface-container-low p-6"><button @click="issueDocuments()" :disabled="!selectedAbstract || issuing || selectedAbstract.status_abstrak === 'Diajukan'" class="w-full rounded-lg bg-secondary px-4 py-2.5 text-xs font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50" x-text="issuing ? 'Memproses…' : 'Terbitkan / pastikan dokumen resmi'"></button><p x-show="selectedAbstract?.status_abstrak === 'Diajukan'" class="text-xs text-on-surface-variant">Menunggu putusan Tim Abstrak; dokumen belum dapat diterbitkan.</p><a :href="selectedAbstract ? '{{ url('/secretariat/abstracts') }}/' + selectedAbstract.id_abstract : '#'" class="block w-full rounded-lg border border-surface-container-high bg-white px-4 py-2.5 text-center text-xs font-semibold text-primary">{{ __('asic-secretariat.abstracts.open_dossier') }} →</a></div>
    </aside>
</div>
@endsection
