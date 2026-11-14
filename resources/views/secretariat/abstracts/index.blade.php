@extends('layouts.portal', [
    'role' => 'secretariat',
    'roleName' => 'Secretariat Admin',
    'nav' => $secNav,
    'title' => __('asic-secretariat.abstracts.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    drawerOpen: false,
    activeTab: 'all',
    searchQuery: '',
    selectedAbstract: {
        id: '#ABS-2027-0104',
        rawId: 'ABS-2027-0104',
        title: 'Precision Nitrogen Delivery System in Tropical Rice Cultivation',
        track: 'Climate-smart Agriculture',
        author: 'Dr. Farhan Harahap',
        format: 'Oral'
    },
    openDrawer(item) {
        this.selectedAbstract = item;
        this.drawerOpen = true;
    },
    closeDrawer() {
        this.drawerOpen = false;
    }
}" class="flex flex-col w-full relative">

    {{-- Slide-over Fast Screening Drawer --}}
    <div x-show="drawerOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeDrawer()" 
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs" 
         style="display: none;"></div>

    <div x-show="drawerOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 w-full sm:w-[420px] bg-surface-container-lowest shadow-2xl z-50 flex flex-col justify-between overflow-y-auto"
         style="display: none;">
        
        <div class="p-6 flex flex-col gap-5">
            <div class="flex items-center justify-between border-b border-surface-container-high pb-4">
                <span class="text-xs uppercase tracking-wider text-primary font-bold font-mono">{{ __('asic-secretariat.abstracts.fast_screening') }}</span>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-full hover:bg-surface-container-low flex items-center justify-center text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="bg-surface-container-low p-4 rounded-xl flex flex-col gap-1">
                <span class="text-xs font-mono font-semibold text-primary" x-text="selectedAbstract.id">#ABS-2027-0104</span>
                <h4 class="text-base font-semibold text-on-surface line-clamp-2" x-text="selectedAbstract.title">Precision Nitrogen Delivery System in Tropical Rice Cultivation</h4>
                <span class="text-xs text-on-surface-variant mt-1" x-text="'Track: ' + selectedAbstract.track">Track: Climate-smart Agriculture</span>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-secretariat.abstracts.checklist_title') }}</span>
                <label class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-surface-container-low cursor-pointer transition">
                    <input type="checkbox" checked class="w-4 h-4 accent-primary rounded">
                    <span class="text-xs text-on-surface">{{ __('asic-secretariat.abstracts.check_words') }}</span>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-surface-container-low cursor-pointer transition">
                    <input type="checkbox" checked class="w-4 h-4 accent-primary rounded">
                    <span class="text-xs text-on-surface">{{ __('asic-secretariat.abstracts.check_structured') }}</span>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-surface-container-low cursor-pointer transition">
                    <input type="checkbox" class="w-4 h-4 accent-primary rounded">
                    <span class="text-xs text-on-surface">{{ __('asic-secretariat.abstracts.check_keywords') }}</span>
                </label>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant">{{ __('asic-secretariat.abstracts.internal_note') }}</label>
                <textarea rows="3" 
                          class="w-full rounded-lg bg-surface-container-low p-3 text-xs text-on-surface outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition"
                          placeholder="{{ __('asic-secretariat.abstracts.internal_note_placeholder') }}"></textarea>
            </div>
        </div>

        <div class="p-6 bg-surface-container-low border-t border-surface-container-high flex flex-col gap-2">
            <button @click="$store.toast.show('LoA dispatched to ' + selectedAbstract.author, 'success'); closeDrawer()" 
                    class="w-full py-2.5 px-4 rounded-lg bg-secondary text-white font-semibold text-xs hover:bg-secondary/90 transition shadow-sm text-center">
                {{ __('asic-secretariat.abstracts.issue_loa') }}
            </button>
            <a :href="'{{ url('secretariat/abstracts') }}/' + selectedAbstract.rawId" 
               class="w-full py-2.5 rounded-lg bg-surface-container-lowest text-primary font-semibold text-xs hover:bg-surface shadow-xs transition text-center border border-surface-container-high">
                {{ __('asic-secretariat.abstracts.open_dossier') }} →
            </a>
        </div>
    </div>

    {{-- Header Area & Target Telemetry --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-6">
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono uppercase font-bold tracking-wider text-primary">{{ __('asic-secretariat.faculty_unand') }}</span>
                <span class="w-1.5 h-1.5 rounded-full bg-outline-variant"></span>
                <span class="text-xs font-mono text-on-surface-variant">{{ __('asic-secretariat.live_sync') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-secretariat.abstracts.title') }}</h1>
            <p class="text-sm text-on-surface-variant max-w-2xl leading-relaxed">
                {{ __('asic-secretariat.abstracts.subtitle') }}
            </p>
        </div>

        {{-- Micro Pipeline Widget --}}
        <div class="hidden xl:flex items-center gap-4 bg-surface-container-lowest p-3 px-4 rounded-xl shadow-xs border border-surface-container-high">
            <div class="flex flex-col">
                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-secretariat.abstracts.target_metric') }}</span>
                <span class="text-base font-bold text-on-surface font-mono">342 / 400</span>
            </div>
            <div class="w-24 h-2 bg-surface-container-highest rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full" style="width: 85.5%;"></div>
            </div>
            <div class="flex items-center gap-1 text-xs text-primary font-semibold">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                <span>{{ __('asic-secretariat.abstracts.target_filled', ['pct' => '85.5']) }}</span>
            </div>
        </div>
    </div>

    {{-- Summary Metric Tiles --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        {{-- Total --}}
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-1 group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs text-on-surface-variant font-medium">{{ __('asic-secretariat.abstracts.total') }}</span>
                <span class="material-symbols-outlined text-primary text-[20px]">library_books</span>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold font-mono text-on-surface">342</span>
                <span class="text-[11px] text-primary font-semibold">+18 wk</span>
            </div>
            <div class="w-full bg-surface-container-high h-1 rounded-full mt-2 overflow-hidden">
                <div class="bg-primary h-full w-full rounded-full"></div>
            </div>
        </div>

        {{-- Submitted / New --}}
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-1 group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs text-on-surface-variant font-medium">{{ __('asic-secretariat.abstracts.new_submitted') }}</span>
                <span class="material-symbols-outlined text-amber-600 text-[20px]">inbox</span>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold font-mono text-on-surface">48</span>
                <span class="text-[11px] text-amber-700 font-semibold">{{ __('asic-secretariat.abstracts.pending_check') }}</span>
            </div>
            <div class="w-full bg-surface-container-high h-1 rounded-full mt-2 overflow-hidden">
                <div class="bg-amber-500 h-full w-[28%] rounded-full"></div>
            </div>
        </div>

        {{-- Under Review --}}
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-1 group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs text-on-surface-variant font-medium">{{ __('asic-secretariat.abstracts.under_review') }}</span>
                <span class="material-symbols-outlined text-blue-600 text-[20px]">rate_review</span>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold font-mono text-on-surface">86</span>
                <span class="text-[11px] text-blue-700 font-semibold">{{ __('asic-secretariat.abstracts.reviewers_count', ['count' => 24]) }}</span>
            </div>
            <div class="w-full bg-surface-container-high h-1 rounded-full mt-2 overflow-hidden">
                <div class="bg-blue-500 h-full w-[45%] rounded-full"></div>
            </div>
        </div>

        {{-- Accepted --}}
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-1 group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs text-on-surface-variant font-medium">{{ __('asic-secretariat.abstracts.accepted') }}</span>
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold font-mono text-on-surface">182</span>
                <span class="text-[11px] text-emerald-700 font-semibold">{{ __('asic-secretariat.abstracts.acceptance_rate', ['pct' => '53.2']) }}</span>
            </div>
            <div class="w-full bg-surface-container-high h-1 rounded-full mt-2 overflow-hidden">
                <div class="bg-emerald-600 h-full w-[65%] rounded-full"></div>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-1 group hover:shadow-md transition col-span-2 md:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs text-on-surface-variant font-medium">{{ __('asic-secretariat.abstracts.rejected') }}</span>
                <span class="material-symbols-outlined text-error text-[20px]">cancel</span>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold font-mono text-on-surface">26</span>
                <span class="text-[11px] text-error font-semibold">{{ __('asic-secretariat.abstracts.rejection_rate', ['pct' => '7.6']) }}</span>
            </div>
            <div class="w-full bg-surface-container-high h-1 rounded-full mt-2 overflow-hidden">
                <div class="bg-error h-full w-[12%] rounded-full"></div>
            </div>
        </div>
    </div>

    {{-- Filter & Action Command Bar --}}
    <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high mb-4 flex flex-col gap-4">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[260px]">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-outline">search</span>
                <input type="text" 
                       x-model="searchQuery" 
                       class="w-full h-10 pl-9 pr-8 bg-surface-container-low hover:bg-surface-container focus:bg-surface-container-lowest rounded-lg text-xs text-on-surface placeholder:text-outline-variant outline-none transition focus:ring-2 focus:ring-primary/20"
                       placeholder="{{ __('asic-secretariat.abstracts.search_placeholder') }}">
                <button x-show="searchQuery.length > 0" 
                        @click="searchQuery = ''" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>

            {{-- Dropdown Selectors Group --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <select class="h-10 pl-3 pr-8 rounded-lg bg-surface-container-low hover:bg-surface-container text-xs text-on-surface font-medium outline-none cursor-pointer appearance-none border border-transparent focus:border-primary/20">
                        <option value="all">{{ __('asic-secretariat.abstracts.all_topics') }}</option>
                        <option value="smart">Sustainable Agrifood Systems</option>
                        <option value="climate">Climate-smart Agriculture</option>
                        <option value="postharvest">Post-harvest Technology</option>
                        <option value="security">Food Security & Policy</option>
                    </select>
                    <span class="material-symbols-outlined pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">expand_more</span>
                </div>

                <div class="relative">
                    <select class="h-10 pl-3 pr-8 rounded-lg bg-surface-container-low hover:bg-surface-container text-xs text-on-surface font-medium outline-none cursor-pointer appearance-none border border-transparent focus:border-primary/20">
                        <option value="all">{{ __('asic-secretariat.abstracts.all_types') }}</option>
                        <option value="oral">Oral Presentation</option>
                        <option value="poster">Poster Presentation</option>
                    </select>
                    <span class="material-symbols-outlined pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">expand_more</span>
                </div>

                {{-- Secondary Outline Action Button: Export --}}
                <button @click="$store.toast.show('Exporting abstracts ledger (342 records)...', 'info')" 
                        class="h-10 px-4 rounded-lg bg-surface-container-low hover:bg-surface-container-high text-primary flex items-center gap-2 text-xs font-semibold transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>{{ __('asic-secretariat.abstracts.export_btn') }}</span>
                </button>
            </div>
        </div>

        {{-- Status Tabs Strip --}}
        <div class="flex items-center gap-2 overflow-x-auto pt-2 border-t border-surface-container-high">
            <button @click="activeTab = 'all'" 
                    :class="activeTab === 'all' ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition shadow-xs">
                All Statuses (342)
            </button>
            <button @click="activeTab = 'submitted'" 
                    :class="activeTab === 'submitted' ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                Submitted (48)
            </button>
            <button @click="activeTab = 'under_review'" 
                    :class="activeTab === 'under_review' ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                Under Review (86)
            </button>
            <button @click="activeTab = 'accepted'" 
                    :class="activeTab === 'accepted' ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                Accepted (182)
            </button>
            <button @click="activeTab = 'rejected'" 
                    :class="activeTab === 'rejected' ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                Rejected (26)
            </button>
        </div>
    </div>

    {{-- Main Submissions Table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant h-11 text-xs font-semibold uppercase tracking-wider border-b border-surface-container-high">
                        <th class="px-4 w-[140px]">{{ __('asic-secretariat.abstracts.col_id') }}</th>
                        <th class="px-4 min-w-[300px]">{{ __('asic-secretariat.abstracts.col_title') }}</th>
                        <th class="px-4 min-w-[200px]">{{ __('asic-secretariat.abstracts.col_author') }}</th>
                        <th class="px-4 w-[100px] text-center">{{ __('asic-secretariat.abstracts.col_format') }}</th>
                        <th class="px-4 w-[120px]">{{ __('asic-secretariat.abstracts.col_submitted') }}</th>
                        <th class="px-4 w-[130px] text-center">{{ __('asic-secretariat.abstracts.col_status') }}</th>
                        <th class="px-4 w-[140px] text-right">{{ __('asic-secretariat.abstracts.col_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high text-xs text-on-surface">
                    {{-- Row 1: Accepted --}}
                    <tr class="h-16 hover:bg-surface-container-low/60 transition group cursor-pointer"
                        @click="openDrawer({
                            id: '#ABS-2027-0104',
                            rawId: 'ABS-2027-0104',
                            title: 'Precision Nitrogen Delivery System in Tropical Rice Cultivation',
                            track: 'Climate-smart Agriculture',
                            author: 'Dr. Farhan Harahap',
                            format: 'Oral'
                        })">
                        <td class="px-4 font-mono font-bold text-primary whitespace-nowrap">
                            #ABS-2027-0104
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-semibold text-on-surface group-hover:text-primary transition line-clamp-1">
                                    Precision Nitrogen Delivery System in Tropical Rice Cultivation
                                </span>
                                <span class="text-[11px] text-on-surface-variant flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                    Track: Climate-smart Agriculture
                                </span>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="flex items-center gap-2.5">
                                <img class="w-7 h-7 rounded-full object-cover bg-surface-container" 
                                     src="{{ asset('images/asic/avatar.svg') }}" 
                                     alt="Author">
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-on-surface">Dr. Farhan Harahap</span>
                                    <span class="text-[10px] text-outline">Universitas Andalas</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                                Oral
                            </span>
                        </td>
                        <td class="px-4 text-[11px] text-on-surface-variant whitespace-nowrap">
                            Date: TBA
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="material-symbols-outlined text-[13px]">check</span>
                                Accepted
                            </span>
                        </td>
                        <td class="px-4 text-right whitespace-nowrap" @click.stop>
                            <a href="{{ route('fe.secretariat.abstracts.show', 'ABS-2027-0104') }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-low hover:bg-primary hover:text-white text-primary text-xs transition font-semibold shadow-xs">
                                <span>{{ __('asic-secretariat.abstracts.btn_review') }}</span>
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </a>
                        </td>
                    </tr>

                    {{-- Row 2: Under Review --}}
                    <tr class="h-16 hover:bg-surface-container-low/60 transition group cursor-pointer"
                        @click="openDrawer({
                            id: '#ABS-2027-0103',
                            rawId: 'ABS-2027-0103',
                            title: 'Enzymatic Hydrolysis for Post-harvest Cocoa Shell Waste Valorization',
                            track: 'Post-harvest Technology',
                            author: 'Prof. Siti Nurhaliza',
                            format: 'Poster'
                        })">
                        <td class="px-4 font-mono font-bold text-primary whitespace-nowrap">
                            #ABS-2027-0103
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-semibold text-on-surface group-hover:text-primary transition line-clamp-1">
                                    Enzymatic Hydrolysis for Post-harvest Cocoa Shell Waste Valorization
                                </span>
                                <span class="text-[11px] text-on-surface-variant flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                    Track: Post-harvest Technology
                                </span>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="flex items-center gap-2.5">
                                <img class="w-7 h-7 rounded-full object-cover bg-surface-container" 
                                     src="{{ asset('images/asic/avatar.svg') }}" 
                                     alt="Author">
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-on-surface">Prof. Siti Nurhaliza</span>
                                    <span class="text-[10px] text-outline">IPB University</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-surface-container-highest text-on-surface-variant">
                                Poster
                            </span>
                        </td>
                        <td class="px-4 text-[11px] text-on-surface-variant whitespace-nowrap">
                            Date: TBA
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                <span class="material-symbols-outlined text-[13px]">rate_review</span>
                                Under Review
                            </span>
                        </td>
                        <td class="px-4 text-right whitespace-nowrap" @click.stop>
                            <a href="{{ route('fe.secretariat.abstracts.show', 'ABS-2027-0103') }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-low hover:bg-primary hover:text-white text-primary text-xs transition font-semibold shadow-xs">
                                <span>{{ __('asic-secretariat.abstracts.btn_review') }}</span>
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </a>
                        </td>
                    </tr>

                    {{-- Row 3: Submitted / New --}}
                    <tr class="h-16 hover:bg-surface-container-low/60 transition group cursor-pointer"
                        @click="openDrawer({
                            id: '#ABS-2027-0102',
                            rawId: 'ABS-2027-0102',
                            title: 'Community Bio-economy and Micro-finance in Oil Palm Agroforestry',
                            track: 'Food Security & Policy',
                            author: 'Dr. Hendra Wijaya',
                            format: 'Oral'
                        })">
                        <td class="px-4 font-mono font-bold text-primary whitespace-nowrap">
                            #ABS-2027-0102
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-semibold text-on-surface group-hover:text-primary transition line-clamp-1">
                                    Community Bio-economy and Micro-finance in Oil Palm Agroforestry
                                </span>
                                <span class="text-[11px] text-on-surface-variant flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Track: Food Security & Policy
                                </span>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="flex items-center gap-2.5">
                                <img class="w-7 h-7 rounded-full object-cover bg-surface-container" 
                                     src="{{ asset('images/asic/avatar.svg') }}" 
                                     alt="Author">
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-on-surface">Dr. Hendra Wijaya</span>
                                    <span class="text-[10px] text-outline">Universitas Riau</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                                Oral
                            </span>
                        </td>
                        <td class="px-4 text-[11px] text-on-surface-variant whitespace-nowrap">
                            Date: TBA
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="material-symbols-outlined text-[13px]">inbox</span>
                                Submitted
                            </span>
                        </td>
                        <td class="px-4 text-right whitespace-nowrap" @click.stop>
                            <a href="{{ route('fe.secretariat.abstracts.show', 'ABS-2027-0102') }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-low hover:bg-primary hover:text-white text-primary text-xs transition font-semibold shadow-xs">
                                <span>{{ __('asic-secretariat.abstracts.btn_review') }}</span>
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </a>
                        </td>
                    </tr>

                    {{-- Row 4: Rejected --}}
                    <tr class="h-16 hover:bg-surface-container-low/60 transition group cursor-pointer"
                        @click="openDrawer({
                            id: '#ABS-2027-0099',
                            rawId: 'ABS-2027-0099',
                            title: 'Uncalibrated Synthetic Pesticide Trials on Highland Horticulture',
                            track: 'Climate-smart Agriculture',
                            author: 'Rian Pratama, M.Sc.',
                            format: 'Oral'
                        })">
                        <td class="px-4 font-mono font-bold text-primary whitespace-nowrap">
                            #ABS-2027-0099
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-semibold text-on-surface group-hover:text-primary transition line-clamp-1">
                                    Uncalibrated Synthetic Pesticide Trials on Highland Horticulture
                                </span>
                                <span class="text-[11px] text-on-surface-variant flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                    Track: Climate-smart Agriculture
                                </span>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="flex items-center gap-2.5">
                                <img class="w-7 h-7 rounded-full object-cover bg-surface-container" 
                                     src="{{ asset('images/asic/avatar.svg') }}" 
                                     alt="Author">
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-on-surface">Rian Pratama, M.Sc.</span>
                                    <span class="text-[10px] text-outline">Balai Litbang Pertanian</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                                Oral
                            </span>
                        </td>
                        <td class="px-4 text-[11px] text-on-surface-variant whitespace-nowrap">
                            Date: TBA
                        </td>
                        <td class="px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-800 border border-red-200">
                                <span class="material-symbols-outlined text-[13px]">cancel</span>
                                Rejected
                            </span>
                        </td>
                        <td class="px-4 text-right whitespace-nowrap" @click.stop>
                            <a href="{{ route('fe.secretariat.abstracts.show', 'ABS-2027-0099') }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-low hover:bg-primary hover:text-white text-primary text-xs transition font-semibold shadow-xs">
                                <span>{{ __('asic-secretariat.abstracts.btn_review') }}</span>
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-surface-container-low/50 border-t border-surface-container-high flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-on-surface-variant">
            <span>Showing 4 of 342 submissions</span>
            <div class="flex items-center gap-1">
                <button class="px-2.5 py-1 rounded bg-surface-container-lowest border border-surface-container-high text-on-surface font-medium disabled:opacity-40" disabled>Previous</button>
                <button class="px-2.5 py-1 rounded bg-primary text-white font-semibold">1</button>
                <button class="px-2.5 py-1 rounded bg-surface-container-lowest border border-surface-container-high text-on-surface font-medium hover:bg-surface-container">2</button>
                <button class="px-2.5 py-1 rounded bg-surface-container-lowest border border-surface-container-high text-on-surface font-medium hover:bg-surface-container">3</button>
                <button class="px-2.5 py-1 rounded bg-surface-container-lowest border border-surface-container-high text-on-surface font-medium hover:bg-surface-container">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection

