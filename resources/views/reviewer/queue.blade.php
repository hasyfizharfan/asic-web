@extends('layouts.portal', [
    'role' => 'reviewer',
    'title' => __('asic-reviewer.queue_title') ?? 'Review Queue - ASIC 2027'
])

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    statusFilter: 'all',
    areaFilter: 'all',
    abstracts: [
        {
            id: 'ASIC27-001',
            title: 'Sustainable Rice Systems Under Climate Stress',
            area: 'Crop Production',
            status: 'awaiting',
            statusLabel: 'Awaiting review',
            date: '2027-08-01'
        },
        {
            id: 'ASIC27-002',
            title: 'Soil Health & Resilience in Tropical Lowlands',
            area: 'Soil Management',
            status: 'awaiting',
            statusLabel: 'Awaiting review',
            date: '2027-08-02'
        },
        {
            id: 'ASIC27-003',
            title: 'Agrifood Supply Chain Optimization in Highland Regions',
            area: 'Socio-Economic',
            status: 'reviewed',
            statusLabel: 'Reviewed',
            date: '2027-08-03'
        }
    ],
    get filteredAbstracts() {
        return this.abstracts.filter(item => {
            const matchSearch = this.search === '' || 
                item.title.toLowerCase().includes(this.search.toLowerCase()) || 
                item.id.toLowerCase().includes(this.search.toLowerCase());
            const matchStatus = this.statusFilter === 'all' || item.status === this.statusFilter;
            const matchArea = this.areaFilter === 'all' || item.area === this.areaFilter;
            return matchSearch && matchStatus && matchArea;
        });
    },
    get statAssigned() {
        return String(this.abstracts.length).padStart(2, '0');
    },
    get statAwaiting() {
        return String(this.abstracts.filter(a => a.status === 'awaiting').length).padStart(2, '0');
    },
    get statReviewed() {
        return String(this.abstracts.filter(a => a.status === 'reviewed').length).padStart(2, '0');
    }
}">

    <!-- Header Section (Matching Image 3 / r1_queue) -->
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-2">
        <div class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
            {{ __('asic-reviewer.queue_breadcrumb') ?? 'Scientific Review / Assigned Abstracts' }}
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-on-surface font-display tracking-tight">
                    {{ __('asic-reviewer.queue_title') ?? 'Abstrak Review Queue' }}
                </h1>
                <p class="text-xs sm:text-sm text-on-surface-variant mt-1">
                    {{ __('asic-reviewer.queue_lead') ?? 'Illustrative review assignments; real records will come from the system database.' }}
                </p>
            </div>

            <!-- Double-Blind Review Badge -->
            @if($blindReview ?? true)
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-fixed text-primary text-xs font-bold shrink-0 self-start sm:self-auto">
                    <span class="material-symbols-outlined text-[18px]">visibility_off</span>
                    <span>Double-Blind Active</span>
                </div>
            @endif
        </div>
    </div>

    <!-- 3 Stat Cards (Matching Image 3 / r1_queue) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Stat 1: Assigned Submissions -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container-highest shadow-xs space-y-1">
            <span class="text-xs font-semibold text-on-surface-variant">
                {{ __('asic-reviewer.stat_assigned') ?? 'Assigned Submissions' }}
            </span>
            <div class="text-3xl font-extrabold text-on-surface font-mono" x-text="statAssigned">
                03
            </div>
        </div>

        <!-- Stat 2: Awaiting Review -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container-highest shadow-xs space-y-1">
            <span class="text-xs font-semibold text-on-surface-variant">
                {{ __('asic-reviewer.stat_awaiting') ?? 'Awaiting Review' }}
            </span>
            <div class="text-3xl font-extrabold text-[#745b00] font-mono" x-text="statAwaiting">
                02
            </div>
        </div>

        <!-- Stat 3: Reviewed -->
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container-highest shadow-xs space-y-1">
            <span class="text-xs font-semibold text-on-surface-variant">
                {{ __('asic-reviewer.stat_reviewed') ?? 'Reviewed' }}
            </span>
            <div class="text-3xl font-extrabold text-primary font-mono" x-text="statReviewed">
                01
            </div>
        </div>
    </div>

    <!-- Table Card: My Assigned Abstracts (Matching Image 3 / r1_queue) -->
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container-highest shadow-xs overflow-hidden">
        <!-- Table Top Controls -->
        <div class="p-5 border-b border-surface-container-highest flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-on-surface font-display">
                {{ __('asic-reviewer.table_title') ?? 'My Assigned Abstracts' }}
            </h2>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Search input -->
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           x-model="search"
                           placeholder="{{ __('asic-reviewer.search_placeholder') ?? 'Cari berdasarkan judul atau ID...' }}" 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-xs text-on-surface focus:ring-2 focus:ring-primary focus:border-primary transition outline-none">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-on-surface-variant text-[18px]">search</span>
                </div>

                <!-- Status Filter -->
                <select x-model="statusFilter" class="px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none">
                    <option value="all">{{ __('asic-reviewer.filter_all_status') ?? 'Semua Status' }}</option>
                    <option value="awaiting">{{ __('asic-reviewer.filter_awaiting') ?? 'Awaiting Review' }}</option>
                    <option value="reviewed">{{ __('asic-reviewer.filter_reviewed') ?? 'Reviewed' }}</option>
                </select>

                <!-- Area Filter -->
                <select x-model="areaFilter" class="px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none">
                    <option value="all">{{ __('asic-reviewer.filter_all_areas') ?? 'Semua Bidang' }}</option>
                    <option value="Crop Production">Crop Production</option>
                    <option value="Soil Management">Soil Management</option>
                    <option value="Socio-Economic">Socio-Economic</option>
                </select>
            </div>
        </div>

        <!-- Desktop & Tablet Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs font-bold text-on-surface-variant uppercase tracking-wider border-b border-surface-container-highest">
                    <tr>
                        <th class="py-3.5 px-6">{{ __('asic-reviewer.th_submission') ?? 'Pengiriman' }}</th>
                        <th class="py-3.5 px-6">{{ __('asic-reviewer.th_area') ?? 'Research Area' }}</th>
                        <th class="py-3.5 px-6">{{ __('asic-reviewer.th_status') ?? 'Status' }}</th>
                        <th class="py-3.5 px-6 text-right">{{ __('asic-reviewer.th_action') ?? 'Action' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-highest">
                    <template x-for="item in filteredAbstracts" :key="item.id">
                        <tr class="hover:bg-surface-container-low/50 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-on-surface font-display flex items-baseline gap-2">
                                    <span class="font-mono text-xs text-primary font-bold" x-text="'#' + item.id"></span>
                                    <span x-text="item.title"></span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-on-surface-variant text-xs font-medium" x-text="item.area"></td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                      :class="item.status === 'reviewed' ? 'bg-[#c8e6c9] text-emerald-900' : 'bg-[#fff3c4] text-[#745b00]'"
                                      x-text="item.statusLabel">
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a :href="'{{ url('_fe/reviewer/abstracts') }}/' + item.id" 
                                   class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-[#F5C518] hover:bg-[#fed649] text-[#17261D] font-bold text-xs shadow-xs transition active:scale-95">
                                    <span class="material-symbols-outlined text-[16px]" x-text="item.status === 'reviewed' ? 'visibility' : 'rate_review'"></span>
                                    <span x-text="item.status === 'reviewed' ? '{{ __('asic-reviewer.btn_view_review') ?? 'Lihat Review' }}' : '{{ __('asic-reviewer.btn_review') ?? 'Tinjau' }}'"></span>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State (When no abstracts match or list is empty) -->
        <div x-show="filteredAbstracts.length === 0" class="p-12 text-center space-y-3" style="display: none;">
            <div class="w-14 h-14 rounded-2xl bg-surface-container-high text-on-surface-variant flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[32px]">folder_off</span>
            </div>
            <div class="font-bold text-base text-on-surface font-display">
                {{ __('asic-reviewer.empty_queue_title') ?? 'Belum ada abstrak yang ditugaskan' }}
            </div>
            <p class="text-xs text-on-surface-variant max-w-sm mx-auto leading-relaxed">
                {{ __('asic-reviewer.empty_queue_desc') ?? 'Abstrak yang ditugaskan oleh tim sekretariat akan muncul di halaman antrean ini.' }}
            </p>
        </div>
    </div>
</div>
@endsection

