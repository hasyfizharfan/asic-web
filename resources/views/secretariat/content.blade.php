@extends('layouts.portal', [
    'role' => 'secretariat',
    'roleName' => 'Secretariat Admin',
    'nav' => $secNav,
    'title' => __('asic-secretariat.content.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    activeTab: 'speakers',
    drawerOpen: false,
    drawerMode: 'new', // 'new' or 'edit'
    searchQuery: '',
    speakerForm: {
        name: '',
        institution: '',
        role: 'Keynote Speaker 1',
        topic: '',
        status: 'published'
    },
    openDrawer(mode, speaker = null) {
        this.drawerMode = mode;
        if (speaker) {
            this.speakerForm = { ...speaker };
        } else {
            this.speakerForm = {
                name: '',
                institution: '',
                role: 'Keynote Speaker',
                topic: '',
                status: 'published'
            };
        }
        this.drawerOpen = true;
    },
    closeDrawer() {
        this.drawerOpen = false;
    },
    saveEntry() {
        this.closeDrawer();
        $store.toast.show('Entry saved successfully.', 'success');
    },
    publishChanges() {
        $store.toast.show('All content updates deployed to public landing gateway.', 'success');
    }
}" class="flex flex-col w-full gap-6 relative">

    {{-- Slide-over CMS Editor Drawer --}}
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
         class="fixed inset-y-0 right-0 w-full sm:w-[460px] bg-surface-container-lowest shadow-2xl z-50 flex flex-col justify-between overflow-y-auto"
         style="display: none;">
        
        <div class="p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-surface-container-high pb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">edit_document</span>
                    <span class="text-sm font-bold text-on-surface" x-text="drawerMode === 'new' ? 'Add New Speaker / Content Entry' : 'Edit Entry Dossier'"></span>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-full hover:bg-surface-container-low flex items-center justify-center text-on-surface-variant transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="flex flex-col gap-3 text-xs">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-on-surface">Speaker Full Name & Title</label>
                    <input type="text" x-model="speakerForm.name" placeholder="e.g. Prof. Dr. Ir. Melinda Surya Dewi"
                           class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-xs outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-on-surface">Institution & Affiliation</label>
                    <input type="text" x-model="speakerForm.institution" placeholder="e.g. Kyoto University / UNAND"
                           class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-xs outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-on-surface">Conference Role / Session Track</label>
                    <select x-model="speakerForm.role" class="w-full px-3 py-2 bg-surface-container-low rounded-lg text-xs outline-none border border-transparent">
                        <option value="Keynote Speaker 1">Keynote Speaker 1 (Plenary)</option>
                        <option value="Keynote Speaker 2">Keynote Speaker 2 (Plenary)</option>
                        <option value="Invited Speaker">Invited Track Speaker</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-on-surface">Presentation Topic / Title</label>
                    <textarea rows="3" x-model="speakerForm.topic" placeholder="e.g. Advances in Climate-Resilient Tropical Crops"
                              class="w-full p-3 bg-surface-container-low rounded-lg text-xs outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition"></textarea>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-on-surface">Publication Status</label>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="published" x-model="speakerForm.status" class="accent-primary">
                            <span>Published (Live)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="draft" x-model="speakerForm.status" class="accent-primary">
                            <span>Draft (Internal)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-surface-container-low border-t border-surface-container-high flex items-center justify-end gap-2">
            <button @click="closeDrawer()" class="px-4 py-2 rounded-lg bg-surface-container-lowest text-on-surface text-xs font-semibold hover:bg-surface transition border border-surface-container-high">
                Cancel
            </button>
            <button @click="saveEntry()" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary/90 transition shadow-xs">
                Save & Apply
            </button>
        </div>
    </div>

    {{-- Top Action & Page Identity Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="flex flex-col gap-1 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] tracking-wider uppercase text-primary px-2 py-0.5 rounded bg-secondary-container font-semibold">CMS Engine • v2.4</span>
                <span class="text-xs text-on-surface-variant flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Public Gateway Active
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">{{ __('asic-secretariat.content.title') }}</h1>
            <p class="text-xs text-on-surface-variant">
                {{ __('asic-secretariat.content.subtitle') }}
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0 self-start md:self-auto">
            <button @click="openDrawer('new')" 
                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-white text-xs font-bold shadow-xs hover:bg-secondary/90 active:scale-95 transition cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>{{ __('asic-secretariat.content.btn_add_entry') }}</span>
            </button>
            <a href="{{ route('fe.home') }}" target="_blank"
               class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-surface-container-lowest text-on-surface text-xs font-semibold shadow-xs border border-surface-container-high hover:bg-surface-container transition">
                <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                <span>{{ __('asic-secretariat.content.btn_view_site') }}</span>
            </a>
        </div>
    </div>

    {{-- Tabbed Content Section --}}
    <div class="flex flex-col gap-4">
        {{-- Tab Navigation --}}
        <div class="flex items-center gap-1 bg-surface-container-lowest p-1 rounded-xl shadow-xs border border-surface-container-high overflow-x-auto text-xs">
            <button @click="activeTab = 'speakers'"
                    :class="activeTab === 'speakers' ? 'bg-primary text-white font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">record_voice_over</span>
                <span>{{ __('asic-secretariat.content.tab_speakers') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 text-white font-mono ml-1">4</span>
            </button>
            <button @click="activeTab = 'schedule'"
                    :class="activeTab === 'schedule' ? 'bg-primary text-white font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                <span>{{ __('asic-secretariat.content.tab_schedule') }}</span>
            </button>
            <button @click="activeTab = 'fees'"
                    :class="activeTab === 'fees' ? 'bg-primary text-white font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">payments</span>
                <span>{{ __('asic-secretariat.content.tab_fees') }}</span>
            </button>
            <button @click="activeTab = 'faq'"
                    :class="activeTab === 'faq' ? 'bg-primary text-white font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">quiz</span>
                <span>{{ __('asic-secretariat.content.tab_faq') }}</span>
            </button>
            <button @click="activeTab = 'pages'"
                    :class="activeTab === 'pages' ? 'bg-primary text-white font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">web</span>
                <span>{{ __('asic-secretariat.content.tab_pages') }}</span>
            </button>
        </div>

        {{-- Subheader Toolbar --}}
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 text-xs">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1">
                {{-- Search Bar --}}
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="{{ __('asic-secretariat.content.search_entries', ['section' => 'Speakers']) }}"
                           class="w-full pl-9 pr-4 py-2 bg-surface-container-low text-on-surface placeholder:text-on-surface-variant rounded-lg text-xs outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
                </div>

                {{-- Filter Track --}}
                <div class="relative">
                    <select class="appearance-none bg-surface-container-low text-on-surface text-xs px-3 py-2 pr-8 rounded-lg outline-none cursor-pointer border border-transparent">
                        <option value="all">{{ __('asic-secretariat.content.all_tracks') }}</option>
                        <option value="keynote">Keynote Sessions</option>
                        <option value="plenary">Plenary Sessions</option>
                        <option value="invited">Invited Track Speakers</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[16px]">expand_more</span>
                </div>

                {{-- Status Filter --}}
                <div class="relative">
                    <select class="appearance-none bg-surface-container-low text-on-surface text-xs px-3 py-2 pr-8 rounded-lg outline-none cursor-pointer border border-transparent">
                        <option value="all-status">{{ __('asic-secretariat.content.any_status') }}</option>
                        <option value="published">Published</option>
                        <option value="draft">Drafts Only</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[16px]">expand_more</span>
                </div>
            </div>

            {{-- Action Button Group --}}
            <div class="flex items-center gap-2 justify-end">
                <button @click="$store.toast.show('Drag-and-drop ordering enabled for table rows.', 'info')" 
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-surface-container-low text-on-surface text-xs hover:bg-surface-container transition">
                    <span class="material-symbols-outlined text-[16px]">reorder</span>
                    <span>{{ __('asic-secretariat.content.reorder') }}</span>
                </button>
                <button @click="publishChanges()" 
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary/90 shadow-xs transition">
                    <span class="material-symbols-outlined text-[16px]">publish</span>
                    <span>{{ __('asic-secretariat.content.publish_all') }}</span>
                </button>
            </div>
        </div>

        {{-- Main Academic CMS Data Table --}}
        <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-semibold uppercase tracking-wider h-11 border-b border-surface-container-high">
                            <th class="py-3 pl-4 pr-2 w-16 text-center">{{ __('asic-secretariat.content.col_order') }}</th>
                            <th class="py-3 px-4 min-w-[240px]">{{ __('asic-secretariat.content.col_speaker') }}</th>
                            <th class="py-3 px-4 min-w-[150px]">{{ __('asic-secretariat.content.col_role') }}</th>
                            <th class="py-3 px-4 min-w-[240px]">{{ __('asic-secretariat.content.col_topic') }}</th>
                            <th class="py-3 px-4 w-28">{{ __('asic-secretariat.content.col_status') }}</th>
                            <th class="py-3 px-4 w-36">{{ __('asic-secretariat.content.col_modified') }}</th>
                            <th class="py-3 px-4 w-28 text-right">{{ __('asic-secretariat.content.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high text-on-surface">
                        {{-- Row 1 --}}
                        <tr class="hover:bg-surface-container-low/50 transition group">
                            <td class="py-3 pl-4 pr-2 text-center text-on-surface-variant cursor-grab">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-outline opacity-40 group-hover:opacity-100">drag_indicator</span>
                                    <span class="font-mono text-[11px] text-outline">01</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center font-bold shrink-0">
                                        <span class="material-symbols-outlined text-[16px]">person</span>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-on-surface truncate">Prof. Dr. Ir. Melinda Surya Dewi</span>
                                        <span class="text-[11px] text-on-surface-variant truncate">BRIN & Universitas Andalas</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    Keynote Speaker 1
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-on-surface line-clamp-1">Biotechnology in Tropical Agriculture & Resilient Crop Systems</span>
                                    <span class="font-mono text-[10px] text-outline">Hall A • 24 Aug 2027 • 09:30 WIB</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                    {{ __('asic-secretariat.content.published') }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col font-mono text-[10px] text-on-surface-variant">
                                    <span>18 May 2027</span>
                                    <span class="text-outline">11:20 WIB</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openDrawer('edit', {
                                        name: 'Prof. Dr. Ir. Melinda Surya Dewi',
                                        institution: 'BRIN & Universitas Andalas',
                                        role: 'Keynote Speaker 1',
                                        topic: 'Biotechnology in Tropical Agriculture & Resilient Crop Systems',
                                        status: 'published'
                                    })" class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition" title="Edit">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <button @click="$store.toast.show('Opening preview modal...', 'info')" class="p-1.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition" title="Preview">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2 --}}
                        <tr class="hover:bg-surface-container-low/50 transition group">
                            <td class="py-3 pl-4 pr-2 text-center text-on-surface-variant cursor-grab">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-outline opacity-40 group-hover:opacity-100">drag_indicator</span>
                                    <span class="font-mono text-[11px] text-outline">02</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-surface-container text-on-surface-variant flex items-center justify-center font-bold shrink-0">
                                        <span class="material-symbols-outlined text-[16px]">person</span>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-on-surface truncate">Prof. Kenji Takahashi, Ph.D.</span>
                                        <span class="text-[11px] text-on-surface-variant truncate">Kyoto University, Japan</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                    Keynote Speaker 2
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-on-surface line-clamp-1">Precision Biochar Synthesis and Carbon Sequestration</span>
                                    <span class="font-mono text-[10px] text-outline">Hall A • 24 Aug 2027 • 11:00 WIB</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                    {{ __('asic-secretariat.content.published') }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col font-mono text-[10px] text-on-surface-variant">
                                    <span>19 May 2027</span>
                                    <span class="text-outline">14:05 WIB</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openDrawer('edit', {
                                        name: 'Prof. Kenji Takahashi, Ph.D.',
                                        institution: 'Kyoto University, Japan',
                                        role: 'Keynote Speaker 2',
                                        topic: 'Precision Biochar Synthesis and Carbon Sequestration',
                                        status: 'published'
                                    })" class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition" title="Edit">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <button @click="$store.toast.show('Opening preview modal...', 'info')" class="p-1.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition" title="Preview">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 3 --}}
                        <tr class="hover:bg-surface-container-low/50 transition group">
                            <td class="py-3 pl-4 pr-2 text-center text-on-surface-variant cursor-grab">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-outline opacity-40 group-hover:opacity-100">drag_indicator</span>
                                    <span class="font-mono text-[11px] text-outline">03</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-surface-container text-on-surface-variant flex items-center justify-center font-bold shrink-0">
                                        <span class="material-symbols-outlined text-[16px]">person</span>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-on-surface truncate">Dr. Maria Santos</span>
                                        <span class="text-[11px] text-on-surface-variant truncate">IRRI Philippines</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-highest text-on-surface-variant">
                                    Invited Speaker
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-on-surface line-clamp-1">Climate Adaptation in Lowland Monsoon Rice</span>
                                    <span class="font-mono text-[10px] text-outline">Hall B • 25 Aug 2027 • 09:00 WIB</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="material-symbols-outlined text-[13px]">edit_note</span>
                                    {{ __('asic-secretariat.content.draft') }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col font-mono text-[10px] text-on-surface-variant">
                                    <span>20 May 2027</span>
                                    <span class="text-outline">09:12 WIB</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openDrawer('edit', {
                                        name: 'Dr. Maria Santos',
                                        institution: 'IRRI Philippines',
                                        role: 'Invited Speaker',
                                        topic: 'Climate Adaptation in Lowland Monsoon Rice',
                                        status: 'draft'
                                    })" class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition" title="Edit">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <button @click="$store.toast.show('Opening preview modal...', 'info')" class="p-1.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition" title="Preview">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

