@extends('layouts.portal', ['role' => 'reviewer', 'title' => __('asic-reviewer.queue_title') ?? 'Antrean Abstrak - ASIC 2027'])

@section('content')
<div class="space-y-6" x-data="{
    search: '', statusFilter: '', areaFilter: '', reviewedFilter: '', abstracts: [],
    stats: {total: 0, pending: 0, reviewed_by_me: 0}, page: 1, lastPage: 1, total: 0,
    perPage: 15, loading: true, loadError: '', timer: null,
    async init() { await this.loadPage(); },
    async loadPage() {
        this.loading = true; this.loadError = '';
        const params = new URLSearchParams({page: this.page, per_page: this.perPage});
        if (this.search.trim()) params.set('search', this.search.trim());
        if (this.statusFilter) params.set('status_abstrak', this.statusFilter);
        if (this.areaFilter) params.set('scope', this.areaFilter);
        if (this.reviewedFilter) params.set('reviewed_by_me', this.reviewedFilter === 'mine' ? 'true' : 'false');
        const response = await window.asicApi.get('/api/tim-abstrak/submissions?' + params.toString());
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Antrean abstrak gagal dimuat.'; return; }
        const pageData = response.data?.data || {};
        this.stats = response.data?.stats || this.stats;
        this.page = pageData.current_page || 1; this.lastPage = pageData.last_page || 1; this.total = pageData.total || 0;
        this.abstracts = (pageData.data || []).map(row => ({id: row.id_abstract, title: row.judul_abstrak, area: row.scope, status: row.status_abstrak}));
    },
    filtersChanged() { clearTimeout(this.timer); this.timer = setTimeout(() => { this.page = 1; this.loadPage(); }, 300); },
    changePage(next) { if (next >= 1 && next <= this.lastPage) { this.page = next; this.loadPage(); } }
}">
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-2">
        <div class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">{{ __('asic-reviewer.queue_breadcrumb') ?? 'Scientific Review / Antrean Abstrak' }}</div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div><h1 class="text-2xl sm:text-3xl font-bold text-on-surface font-display tracking-tight">{{ __('asic-reviewer.queue_title') ?? 'Antrean Abstrak' }}</h1><p class="text-xs sm:text-sm text-on-surface-variant mt-1">{{ __('asic-reviewer.queue_lead') ?? 'Daftar abstrak yang diajukan untuk ditinjau.' }}</p></div>
            @if($blindReview ?? false)<div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-fixed text-primary text-xs font-bold"><span class="material-symbols-outlined text-[18px]">visibility_off</span><span>Double-Blind Active</span></div>@endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach([['Total Abstrak', 'total', 'text-on-surface'], ['Menunggu Review', 'pending', 'text-[#745b00]'], ['Saya Review', 'reviewed_by_me', 'text-primary'] ] as [$label, $key, $color])
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container-highest shadow-xs space-y-1"><span class="text-xs font-semibold text-on-surface-variant">{{ $label }}</span><div class="text-3xl font-extrabold {{ $color }} font-mono" x-text="stats.{{ $key }}">0</div></div>
        @endforeach
    </div>

    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container-highest shadow-xs overflow-hidden">
        <div class="p-5 border-b border-surface-container-highest flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-on-surface font-display">{{ __('asic-reviewer.table_title') ?? 'Antrean Abstrak' }}</h2>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-full sm:w-64"><input type="search" x-model="search" @input="filtersChanged()" @keydown.enter.prevent="loadPage()" placeholder="Cari berdasarkan judul..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"><span class="material-symbols-outlined absolute left-2.5 top-2.5 text-on-surface-variant text-[18px]">search</span></div>
                <select x-model="statusFilter" @change="filtersChanged()" class="px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-xs"><option value="">Semua Status</option><option value="Diajukan">Diajukan</option><option value="Diterima">Diterima</option><option value="Ditolak">Ditolak</option></select>
                <select x-model="areaFilter" @change="filtersChanged()" class="px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-xs"><option value="">Semua Bidang</option>@foreach($scopes as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select>
                <select x-model="reviewedFilter" @change="filtersChanged()" class="px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-xs"><option value="">Semua review</option><option value="mine">Saya review</option><option value="not_mine">Belum saya review</option></select>
            </div>
        </div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm">
            <thead class="bg-surface-container-low text-xs font-bold text-on-surface-variant uppercase tracking-wider border-b border-surface-container-highest"><tr><th class="py-3.5 px-6">Pengajuan</th><th class="py-3.5 px-6">Bidang</th><th class="py-3.5 px-6">Status</th><th class="py-3.5 px-6 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-surface-container-highest">
                <tr x-show="loading"><td colspan="4" class="p-8 text-center text-sm text-on-surface-variant">Memuat antrean abstrak…</td></tr>
                <tr x-show="loadError"><td colspan="4" x-text="loadError" class="p-8 text-center text-sm text-error" role="alert"></td></tr>
                <template x-for="item in abstracts" :key="item.id"><tr class="hover:bg-surface-container-low/50 transition">
                    <td class="py-4 px-6"><div class="font-bold text-on-surface font-display flex items-baseline gap-2"><span class="font-mono text-xs text-primary" x-text="'#' + item.id"></span><span x-text="item.title"></span></div></td>
                    <td class="py-4 px-6 text-on-surface-variant text-xs font-medium" x-text="item.area"></td>
                    <td class="py-4 px-6"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold" :class="item.status === 'Diajukan' ? 'bg-[#fff3c4] text-[#745b00]' : 'bg-[#c8e6c9] text-emerald-900'" x-text="item.status"></span></td>
                    <td class="py-4 px-6 text-right"><a :href="'{{ url('/reviewer/abstracts') }}/' + item.id" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-[#F5C518] text-[#17261D] font-bold text-xs"><span class="material-symbols-outlined text-[16px]">rate_review</span><span>Tinjau</span></a></td>
                </tr></template>
                <tr x-show="!loading && !loadError && abstracts.length === 0"><td colspan="4" class="p-12 text-center text-sm text-on-surface-variant">Belum ada abstrak dalam antrean.</td></tr>
            </tbody>
        </table></div>
        <div class="p-4 flex items-center justify-between text-xs text-on-surface-variant"><span x-text="`Menampilkan ${abstracts.length} dari ${total} abstrak`"></span><div class="flex items-center gap-2"><button @click="changePage(page - 1)" :disabled="page <= 1 || loading" class="px-3 py-2 rounded-lg border disabled:opacity-40">Sebelumnya</button><span x-text="`${page} / ${lastPage}`"></span><button @click="changePage(page + 1)" :disabled="page >= lastPage || loading" class="px-3 py-2 rounded-lg border disabled:opacity-40">Berikutnya</button></div></div>
    </div>
</div>
@endsection
