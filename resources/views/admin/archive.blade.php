@extends('layouts.portal', [
    'title' => __('asic-admin.archive_title') . ' — ASIC 2027',
    'role' => 'admin',
    'roleName' => 'Super Admin',
    'nav' => $adminNav
])

@section('content')
<div class="flex flex-col gap-6" x-data="{
    showDrawer: {{ request('drawer') === 'open' ? 'true' : 'false' }},
    searchQuery: '',
    publicAccess: true
}">

    {{-- Top Utility Context Bar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-surface-container-highest/60">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 text-on-surface-variant text-xs font-medium">
                <span>Repository & Records</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-primary font-semibold">Conference Archive</span>
            </div>
            <div class="flex items-baseline gap-2 mt-0.5">
                <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-admin.archive_title') }}</h1>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant font-mono text-xs">v2.4-stable</span>
            </div>
            <p class="text-xs sm:text-sm text-on-surface-variant max-w-3xl">
                {{ __('asic-admin.archive_subtitle') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                <input x-model="searchQuery" class="h-10 w-64 sm:w-72 pl-9 pr-3 rounded-xl bg-surface-container-lowest text-on-surface placeholder:text-on-surface-variant/60 text-xs shadow-xs border border-surface-container-highest/60 focus:outline-none focus:ring-2 focus:ring-primary/40" placeholder="{{ __('asic-admin.search_archive_placeholder') }}" type="text">
            </div>
            <div class="relative">
                <select class="h-10 px-3 pr-8 rounded-xl bg-surface-container-lowest text-on-surface text-xs font-semibold shadow-xs border border-surface-container-highest/60 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <option>{{ __('asic-admin.all_editions') }}</option>
                    <option>ASIC 2025 (3rd Edition)</option>
                    <option>ASIC 2023 (2nd Edition)</option>
                    <option>ASIC 2021 (1st Edition)</option>
                </select>
                <span class="material-symbols-outlined pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">expand_more</span>
            </div>
            <button @click="showDrawer = true" type="button" class="h-10 px-4 rounded-xl bg-secondary-container hover:bg-secondary-fixed text-on-secondary-container text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-all active:scale-95">
                <span class="material-symbols-outlined text-[18px] font-bold">add</span>
                <span>{{ __('asic-admin.add_past_edition') }}</span>
            </button>
        </div>
    </div>

    {{-- Metric / Summary Statistics Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Stat 1 --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col justify-between relative overflow-hidden group hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.stat_archived_editions') }}</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">history_edu</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-on-surface tracking-tight">3 Conferences</div>
                <p class="text-xs text-on-surface-variant mt-0.5">ASIC 2021, 2023, 2025 bi-annual</p>
            </div>
            <div class="mt-3 pt-2 border-t border-surface-container-highest/40 flex items-center gap-1 text-primary text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                <span>100% Curated & Validated</span>
            </div>
            <div class="absolute right-0 bottom-0 w-20 h-20 bg-primary/5 rounded-tl-full pointer-events-none"></div>
        </div>

        {{-- Stat 2 --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col justify-between relative overflow-hidden group hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.stat_indexed_papers') }}</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">menu_book</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-on-surface tracking-tight">482 Manuscripts</div>
                <p class="text-xs text-on-surface-variant mt-0.5">Scopus & SINTA Tier-1 cataloged</p>
            </div>
            <div class="mt-3 pt-2 border-t border-surface-container-highest/40 flex items-center gap-1 text-on-surface-variant text-xs">
                <span class="material-symbols-outlined text-[16px] text-tertiary">trending_up</span>
                <span><strong class="text-on-surface font-semibold">+14.2%</strong> papers vs inaugural</span>
            </div>
            <div class="absolute right-0 bottom-0 w-20 h-20 bg-tertiary/5 rounded-tl-full pointer-events-none"></div>
        </div>

        {{-- Stat 3 --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col justify-between relative overflow-hidden group hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.stat_repository_impact') }}</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">downloading</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-on-surface tracking-tight">24.8k Reads</div>
                <p class="text-xs text-on-surface-variant mt-0.5">Open access citations & downloads</p>
            </div>
            <div class="mt-3 pt-2 border-t border-surface-container-highest/40 flex items-center gap-1 text-primary text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">public</span>
                <span>64 Countries reached</span>
            </div>
            <div class="absolute right-0 bottom-0 w-20 h-20 bg-primary/5 rounded-tl-full pointer-events-none"></div>
        </div>

        {{-- Stat 4 --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col justify-between relative overflow-hidden group hover:shadow-sm transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.stat_storage') }}</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">cloud_done</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-on-surface tracking-tight">18.4 GB</div>
                <p class="text-xs text-on-surface-variant mt-0.5">Proceedings PDF, Keynotes & Photos</p>
            </div>
            <div class="mt-3 pt-2 border-t border-surface-container-highest/40 flex flex-col gap-1 text-xs text-on-surface-variant">
                <div class="flex items-center justify-between">
                    <span>Capacity: 25.0 GB</span>
                    <span class="text-primary font-semibold">73.6% used</span>
                </div>
                <div class="w-full bg-surface-container rounded-full h-1 overflow-hidden">
                    <div class="bg-primary h-1 rounded-full" style="width: 73.6%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Stage: 2-Column Responsive Workspace --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        {{-- Left / Center: Past Seminar Records Table & Gallery Previews (8 cols) --}}
        <div class="xl:col-span-8 flex flex-col gap-6">

            {{-- Panel Header & Batch Action --}}
            <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-highest/60 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-surface-container text-primary font-semibold">
                        <span class="material-symbols-outlined text-[20px]">folder_special</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-on-surface">{{ __('asic-admin.historical_catalog') }}</h2>
                        <span class="text-xs text-on-surface-variant">{{ __('asic-admin.historical_catalog_desc') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
                    <button @click="$store.toast.show('Exporting Batch Crossref DOI schema...', 'info')" class="h-9 px-3 rounded-lg bg-surface-container text-on-surface font-semibold hover:bg-surface-container-high transition-colors flex items-center gap-1 border border-surface-container-highest/40">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                        <span>{{ __('asic-admin.batch_doi_export') }}</span>
                    </button>
                    <button @click="$store.toast.show('Generating full archive manifest (JSON)...', 'info')" class="h-9 px-3 rounded-lg bg-surface-container text-on-surface font-semibold hover:bg-surface-container-high transition-colors flex items-center gap-1 border border-surface-container-highest/40">
                        <span class="material-symbols-outlined text-[16px]">ios_share</span>
                        <span>{{ __('asic-admin.archive_manifest') }}</span>
                    </button>
                </div>
            </div>

            {{-- Structured Table Card --}}
            <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-highest/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface-variant text-xs uppercase tracking-wider font-semibold border-b border-surface-container-highest/60">
                                <th class="py-3 px-4">{{ __('asic-admin.col_edition_year') }}</th>
                                <th class="py-3 px-4">{{ __('asic-admin.col_theme') }}</th>
                                <th class="py-3 px-4">{{ __('asic-admin.col_date_venue') }}</th>
                                <th class="py-3 px-4">{{ __('asic-admin.col_proceedings_doi') }}</th>
                                <th class="py-3 px-3 text-center">{{ __('asic-admin.col_papers') }}</th>
                                <th class="py-3 px-4">{{ __('asic-admin.col_media') }}</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container/60 text-xs">
                            {{-- Row 1: ASIC 2025 --}}
                            <tr class="hover:bg-surface-container-low/50 transition-colors group">
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex items-start gap-2">
                                        <span class="px-2 py-0.5 rounded-lg bg-primary-fixed-dim/30 text-on-primary-fixed-variant text-xs font-bold">2025</span>
                                        <div>
                                            <div class="font-bold text-on-surface group-hover:text-primary transition-colors">ASIC 2025</div>
                                            <div class="text-on-surface-variant text-[11px]">3rd Edition</div>
                                            <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-semibold text-primary px-1.5 py-0.5 rounded bg-primary-fixed/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Indexed
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    <span class="font-semibold text-on-surface block leading-tight">Agrifood Transformation in Post-Pandemic Tropical Agriculture</span>
                                    <span class="text-on-surface-variant text-[11px] line-clamp-1 mt-1">Climate-smart practices, precision agrotechnology, & smallholder economic resiliency.</span>
                                </td>
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-on-surface">
                                        <span class="material-symbols-outlined text-[14px] text-on-surface-variant">calendar_today</span>
                                        <span>Nov 14–16, 2025</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-on-surface-variant text-[11px] mt-0.5">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                                        <span>Padang, West Sumatra</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono text-[11px] text-on-surface font-semibold">AIP Conf. Proc. Vol. 2940</div>
                                    <a class="inline-flex items-center gap-0.5 text-primary hover:underline font-mono text-[11px] mt-0.5" href="#" target="_blank">
                                        <span>10.1063/5.0182455</span>
                                        <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                    </a>
                                    <div class="text-[10px] text-on-surface-variant mt-0.5 font-mono">ISBN: 978-0-7354-4821-6</div>
                                </td>
                                <td class="py-3.5 px-3 align-top text-center whitespace-nowrap">
                                    <span class="font-bold text-on-surface text-sm">178</span>
                                    <span class="block text-[10px] text-on-surface-variant">Full papers</span>
                                </td>
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-on-surface font-semibold">
                                        <span class="material-symbols-outlined text-[16px] text-primary">photo_library</span>
                                        <span>48 Photos</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-[11px] text-on-surface-variant mt-0.5">
                                        <span class="material-symbols-outlined text-[14px] text-secondary">videocam</span>
                                        <span>3 Keynote VODs</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="$store.toast.show('Editing ASIC 2025 archive...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center transition-colors" title="Edit Edition">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="$store.toast.show('Opening ASIC 2025 gallery...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-primary flex items-center justify-center transition-colors" title="Manage Gallery">
                                            <span class="material-symbols-outlined text-[18px]">collections</span>
                                        </button>
                                        <button @click="$store.toast.show('Downloading proceedings catalog...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant flex items-center justify-center transition-colors" title="Download Catalog">
                                            <span class="material-symbols-outlined text-[18px]">download</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Row 2: ASIC 2023 --}}
                            <tr class="hover:bg-surface-container-low/50 transition-colors group">
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex items-start gap-2">
                                        <span class="px-2 py-0.5 rounded-lg bg-surface-container-high text-on-surface text-xs font-bold">2023</span>
                                        <div>
                                            <div class="font-bold text-on-surface group-hover:text-primary transition-colors">ASIC 2023</div>
                                            <div class="text-on-surface-variant text-[11px]">2nd Edition</div>
                                            <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-semibold text-primary px-1.5 py-0.5 rounded bg-primary-fixed/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Indexed
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    <span class="font-semibold text-on-surface block leading-tight">Digital Agrotechnology & Smallholder Farming Resilience</span>
                                    <span class="text-on-surface-variant text-[11px] line-clamp-1 mt-1">Smart irrigation, supply chain provenance & soil health monitoring.</span>
                                </td>
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-on-surface">
                                        <span class="material-symbols-outlined text-[14px] text-on-surface-variant">calendar_today</span>
                                        <span>Oct 20–22, 2023</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-on-surface-variant text-[11px] mt-0.5">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                                        <span>Convention Hall Unand</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono text-[11px] text-on-surface font-semibold">IOP Conf. Ser.: Earth Environ.</div>
                                    <a class="inline-flex items-center gap-0.5 text-primary hover:underline font-mono text-[11px] mt-0.5" href="#" target="_blank">
                                        <span>10.1088/1755-1315/1211</span>
                                        <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                    </a>
                                    <div class="text-[10px] text-on-surface-variant mt-0.5 font-mono">ISSN: 1755-1315</div>
                                </td>
                                <td class="py-3.5 px-3 align-top text-center whitespace-nowrap">
                                    <span class="font-bold text-on-surface text-sm">164</span>
                                    <span class="block text-[10px] text-on-surface-variant">Full papers</span>
                                </td>
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-on-surface font-semibold">
                                        <span class="material-symbols-outlined text-[16px] text-primary">photo_library</span>
                                        <span>36 Photos</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-[11px] text-on-surface-variant mt-0.5">
                                        <span class="material-symbols-outlined text-[14px] text-secondary">videocam</span>
                                        <span>2 Keynote VODs</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="$store.toast.show('Editing ASIC 2023 archive...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center transition-colors" title="Edit Edition">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="$store.toast.show('Opening ASIC 2023 gallery...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-primary flex items-center justify-center transition-colors" title="Manage Gallery">
                                            <span class="material-symbols-outlined text-[18px]">collections</span>
                                        </button>
                                        <button @click="$store.toast.show('Downloading proceedings catalog...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant flex items-center justify-center transition-colors" title="Download Catalog">
                                            <span class="material-symbols-outlined text-[18px]">download</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Row 3: ASIC 2021 --}}
                            <tr class="hover:bg-surface-container-low/50 transition-colors group">
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex items-start gap-2">
                                        <span class="px-2 py-0.5 rounded-lg bg-surface-container-high text-on-surface text-xs font-bold">2021</span>
                                        <div>
                                            <div class="font-bold text-on-surface group-hover:text-primary transition-colors">ASIC 2021</div>
                                            <div class="text-on-surface-variant text-[11px]">Inaugural Edition</div>
                                            <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-semibold text-primary px-1.5 py-0.5 rounded bg-primary-fixed/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span> Indexed
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    <span class="font-semibold text-on-surface block leading-tight">Food Security & Sustainable Agribusiness Systems</span>
                                    <span class="text-on-surface-variant text-[11px] line-clamp-1 mt-1">Post-harvest biotechnology, indigenous food systems & regenerative farming.</span>
                                </td>
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-on-surface">
                                        <span class="material-symbols-outlined text-[14px] text-on-surface-variant">calendar_today</span>
                                        <span>Sep 15–16, 2021</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-on-surface-variant text-[11px] mt-0.5">
                                        <span class="material-symbols-outlined text-[14px]">videocam</span>
                                        <span>Virtual Conference</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono text-[11px] text-on-surface font-semibold">E3S Web of Conferences 306</div>
                                    <a class="inline-flex items-center gap-0.5 text-primary hover:underline font-mono text-[11px] mt-0.5" href="#" target="_blank">
                                        <span>10.1051/e3sconf/2021306</span>
                                        <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                    </a>
                                    <div class="text-[10px] text-on-surface-variant mt-0.5 font-mono">eISSN: 2267-1242</div>
                                </td>
                                <td class="py-3.5 px-3 align-top text-center whitespace-nowrap">
                                    <span class="font-bold text-on-surface text-sm">140</span>
                                    <span class="block text-[10px] text-on-surface-variant">Full papers</span>
                                </td>
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-on-surface font-semibold">
                                        <span class="material-symbols-outlined text-[16px] text-primary">photo_library</span>
                                        <span>52 Photos</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-[11px] text-on-surface-variant mt-0.5">
                                        <span class="material-symbols-outlined text-[14px] text-secondary">mic</span>
                                        <span>Keynote Transcripts</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="$store.toast.show('Editing ASIC 2021 archive...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center transition-colors" title="Edit Edition">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="$store.toast.show('Opening ASIC 2021 gallery...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-primary flex items-center justify-center transition-colors" title="Manage Gallery">
                                            <span class="material-symbols-outlined text-[18px]">collections</span>
                                        </button>
                                        <button @click="$store.toast.show('Downloading proceedings catalog...', 'info')" class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface-variant flex items-center justify-center transition-colors" title="Download Catalog">
                                            <span class="material-symbols-outlined text-[18px]">download</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer / Pagination --}}
                <div class="p-4 bg-surface-container-low/60 border-t border-surface-container-highest/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-on-surface-variant">
                    <div class="flex items-center gap-2">
                        <span>Showing 3 of 3 historical editions</span>
                        <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                        <span>All proceedings synchronized with Crossref metadata</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button class="px-2.5 py-1 rounded bg-surface-container hover:bg-surface-container-high text-on-surface disabled:opacity-50" disabled>Previous</button>
                        <button class="px-2.5 py-1 rounded bg-primary text-on-primary font-bold">1</button>
                        <button class="px-2.5 py-1 rounded bg-surface-container hover:bg-surface-container-high text-on-surface disabled:opacity-50" disabled>Next</button>
                    </div>
                </div>
            </div>

            {{-- Photo Gallery Showcase Section --}}
            <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-highest/60 p-5 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-2 border-b border-surface-container-highest/40">
                    <div>
                        <h3 class="text-base font-bold text-on-surface">{{ __('asic-admin.featured_galleries') }}</h3>
                        <p class="text-xs text-on-surface-variant">{{ __('asic-admin.featured_galleries_desc') }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-xs font-semibold">136 Curated Media</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Card 1 --}}
                    <div class="rounded-xl overflow-hidden group shadow-xs border border-surface-container-highest/60 bg-surface-container-low flex flex-col">
                        <div class="h-36 w-full bg-cover bg-center group-hover:scale-105 transition-transform duration-300" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDwTC00XUsEYLnR8HlWc6bpbxAVaBcP7NUMpPzx1b4k3HHfgNVq553Bz37qWKUvKpntlnR2U-YnZ54-mx8fPGjlsldioM621RuNYN2vxSpFdHpvvDwgWWAYkTQBYdfIOMV0G41PxXaOLxZ6IAJHqLs8F0cBT55T6uoQaE5RvFt75uAmRx8vitMrxeOHxLkDM0HUocTZDOzBvntJWbkbFJJQ6G0UNIGOsTeh3WPjBrKZ')"></div>
                        <div class="p-3 bg-surface-container-lowest flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs text-on-surface-variant">
                                    <span class="text-primary font-bold">ASIC 2025</span>
                                    <span>48 assets</span>
                                </div>
                                <h4 class="text-xs sm:text-sm font-semibold text-on-surface mt-1 truncate">Plenary Session & MoU Signing</h4>
                                <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-1">Keynote presentation session</p>
                            </div>
                        </div>
                    </div>
                    {{-- Card 2 --}}
                    <div class="rounded-xl overflow-hidden group shadow-xs border border-surface-container-highest/60 bg-surface-container-low flex flex-col">
                        <div class="h-36 w-full bg-cover bg-center group-hover:scale-105 transition-transform duration-300" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuASZlyqz05xbs0jtuLVfcTym9r5S8MqBQvGAwGasbnMgr2lTN_8yx6Dj_qvqbM6L_EQ2bnpKlO6ioENLbtyrq1csg2hSHqoztaBwDMzFki1LoKZkasAZCi58xV4WmpAy4cAhw5WxkrR6r1zR0CFDM94VCmNw_PnJB2KRXb4JtfM_vPC8cr3QXTpr7xMRWFG3RKxx8bHVFo_JtjrsAD92SwaNCG4K6x_VfRS20TSzly7')"></div>
                        <div class="p-3 bg-surface-container-lowest flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs text-on-surface-variant">
                                    <span class="text-primary font-bold">ASIC 2023</span>
                                    <span>36 assets</span>
                                </div>
                                <h4 class="text-xs sm:text-sm font-semibold text-on-surface mt-1 truncate">Agritech Exhibition & Demos</h4>
                                <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-1">Field demonstration of IoT smart drip irrigation sensors.</p>
                            </div>
                        </div>
                    </div>
                    {{-- Card 3 --}}
                    <div class="rounded-xl overflow-hidden group shadow-xs border border-surface-container-highest/60 bg-surface-container-low flex flex-col">
                        <div class="h-36 w-full bg-cover bg-center group-hover:scale-105 transition-transform duration-300" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCUHAAPTcE_fMyB4bdn-t1UoVSG-q17svwFSs4O2g71QSDdEfvTYMYxKib05t-4PXytFEVoH9W2LttVK5xgWg1GqdjyK95yYjwHJD5OPnss-79scODo04BYb5FWhxWgnpeG94BMwuXvWIzdLA1eJKmBc1kH2r9D45rYQehleNmpMzEgaW8TqNcD6OwuY4CvZhgroSsuPkWjTo8LP_gnjY2vr_vtmQoo0tMCRERMfV7D')"></div>
                        <div class="p-3 bg-surface-container-lowest flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs text-on-surface-variant">
                                    <span class="text-primary font-bold">ASIC 2021</span>
                                    <span>52 assets</span>
                                </div>
                                <h4 class="text-xs sm:text-sm font-semibold text-on-surface mt-1 truncate">Virtual Gala & Award Ceremony</h4>
                                <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-1">Best presenter announcements and scientific committee tribute.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right: Integrated Add / Edit Past Seminar Edition Workspace Form (4 cols) --}}
        <div class="xl:col-span-4 flex flex-col gap-6">
            <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-highest/60 p-5 relative overflow-hidden flex flex-col gap-4">
                <div class="flex items-center justify-between pb-2 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-[18px]">edit_note</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-on-surface">{{ __('asic-admin.record_past_edition') }}</h3>
                            <p class="text-xs text-on-surface-variant">{{ __('asic-admin.record_past_edition_desc') }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-surface-container text-primary text-xs font-semibold">Active Form</span>
                </div>

                <form @submit.prevent="$store.toast.show('Archival seminar record saved successfully.', 'success')" class="flex flex-col gap-4 text-xs sm:text-sm">
                    {{-- Year & Series --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-on-surface">Conference Year</label>
                            <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/40" type="number" value="2027">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-on-surface">Series Designation</label>
                            <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/40" type="text" value="4th International Conf.">
                        </div>
                    </div>

                    {{-- Main Theme --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-on-surface">Conference Main Theme</label>
                        <textarea class="p-3 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none" rows="2">Sustainable Tropical Agrosystems and Resilient Global Food Supply</textarea>
                    </div>

                    {{-- Publisher & ISBN/ISSN --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-on-surface">{{ __('asic-admin.proceedings_publisher') }}</label>
                            <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/40" type="text" value="AIP Publishing LLC">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-semibold text-on-surface">ISBN / ISSN</label>
                            <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface font-mono text-xs border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/40" placeholder="978-0-xxxx-xxxx-x" type="text" value="978-0-7354-5100-2">
                        </div>
                    </div>

                    {{-- DOI Prefix --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.doi_canonical') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[16px]">link</span>
                            <input class="h-10 pl-9 pr-3 rounded-xl bg-surface-container-low text-on-surface font-mono text-xs w-full border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/40" type="text" value="https://doi.org/10.1063/5.asic2027">
                        </div>
                    </div>

                    {{-- PDF Upload Component --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.proceedings_pdf') }}</label>
                        <div class="p-3 rounded-xl bg-surface-container-low flex items-center justify-between gap-3 border border-surface-container-highest/40 hover:bg-surface-container transition-colors">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <div class="w-9 h-9 rounded-lg bg-surface-container-lowest flex items-center justify-center text-error shrink-0 border border-surface-container-highest">
                                    <span class="material-symbols-outlined text-[22px]">picture_as_pdf</span>
                                </div>
                                <div class="flex flex-col min-w-0 text-xs">
                                    <span class="font-semibold text-on-surface truncate">ASIC_2025_Full_Proceedings.pdf</span>
                                    <span class="text-on-surface-variant text-[10px]">42.8 MB • Uploaded & Digitized</span>
                                </div>
                            </div>
                            <button @click="$store.toast.show('Select new proceedings PDF file...', 'info')" class="text-primary hover:text-on-surface p-1 rounded transition-colors" title="Replace file" type="button">
                                <span class="material-symbols-outlined text-[18px]">sync</span>
                            </button>
                        </div>
                    </div>

                    {{-- Gallery Multi-image Dropzone --}}
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="font-semibold text-on-surface">{{ __('asic-admin.gallery_documentation') }}</label>
                            <span class="text-xs text-primary font-medium">3 of 50 staged</span>
                        </div>
                        <div @click="$store.toast.show('Select high-res photos to upload...', 'info')" class="rounded-xl p-4 bg-surface-container-low/70 border-2 border-dashed border-outline-variant/60 hover:bg-surface-container-low transition-colors text-center cursor-pointer flex flex-col items-center justify-center">
                            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-1">
                                <span class="material-symbols-outlined text-[20px]">cloud_upload</span>
                            </div>
                            <p class="text-xs font-semibold text-on-surface">Drop images here or <span class="text-primary underline">browse</span></p>
                            <p class="text-[10px] text-on-surface-variant mt-0.5">Supports JPG, PNG, WEBP up to 50MB per batch</p>
                        </div>
                        {{-- Thumbnail Management Strip --}}
                        <div class="grid grid-cols-3 gap-2 mt-1">
                            <div class="relative rounded-lg overflow-hidden group bg-surface-container h-16 shadow-xs border border-surface-container-highest/60">
                                <img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD7aAPhAjQZgjhEgp1W0SKKdbMh39ricstivU4cKnVdQBVQQkvw24-t0agIfKtb-nF7M37fQEqjs6Y39tcUu-nqwltccPAFRNYI6neJ3gQd6299PCdGglDYW-CvtJCiE2Hq1AxV0cns-l3R4b4zuC8_un6tf0QbMxmsHCGneBNcl9LQJ9wcPxWGZlXPC4pVlNjMqI8bW8eUrVieOKjUk2QUedT9-Sa96sVIFPjOHc1Z" alt="Keynote">
                                <div class="absolute bottom-0 inset-x-0 bg-surface-container-lowest/90 px-1 py-0.5 text-[9px] text-on-surface truncate">Keynote #1</div>
                            </div>
                            <div class="relative rounded-lg overflow-hidden group bg-surface-container h-16 shadow-xs border border-surface-container-highest/60">
                                <img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCN42TrPc3TToBv7oUTs9TisPkunUXCuXz1D8PCUkpLcfLrk7gUqsa-LCj7AYECXHeE-R_M2AXaomJFAr5AK84qMsm-qPi8EkxOUUKLNBuyc_GG6M8gZGQz_9BC5AMyFPzw7qpkmV_faBj7LnR10su2qTsWGb4sc8Dc0yb0ESl25UU0HYgZqWZ89xn5QL8ilFGE8UjNnnOwkyFJBHwXgI65-HAptu9nEBem5zNl7_Sp" alt="Networking">
                                <div class="absolute bottom-0 inset-x-0 bg-surface-container-lowest/90 px-1 py-0.5 text-[9px] text-on-surface truncate">Networking</div>
                            </div>
                            <div class="relative rounded-lg overflow-hidden group bg-surface-container h-16 shadow-xs border border-surface-container-highest/60">
                                <img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA5Wt-KUoZ1esCbB2ITBLlYO8d-tsKsDC-dsZBuqMKVeJTLyshxiyx2CVukrwN5sMOUVP8yjeEXlL_DxQwC3LRzvDbQyZgHdzeXqqtB4vT_9hPf8hrk864gurQ3QsPlmdX04Gx5J8dAWASgKo54S4JU1VKvVd9xW0MzccUBDxZ7zDKuWEqkDUFGpaJBXWhtU65MLPRA8YerEXlnr1Yq94DtEHOF6ugOT-3cFTXfH5AC" alt="Opening">
                                <div class="absolute bottom-0 inset-x-0 bg-surface-container-lowest/90 px-1 py-0.5 text-[9px] text-on-surface truncate">Opening</div>
                            </div>
                        </div>
                    </div>

                    {{-- Visibility Toggle --}}
                    <div class="p-3 rounded-xl bg-surface-container-low flex items-center justify-between border border-surface-container-highest/40">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">public</span>
                            <div>
                                <span class="text-xs font-semibold text-on-surface block leading-tight">{{ __('asic-admin.public_archive_access') }}</span>
                                <span class="text-[11px] text-on-surface-variant">{{ __('asic-admin.public_archive_access_desc') }}</span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input x-model="publicAccess" class="sr-only peer" type="checkbox">
                            <div class="w-10 h-6 bg-outline-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-surface-container-lowest after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-surface-container-lowest after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    {{-- Bottom Action Buttons --}}
                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button class="h-10 px-4 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold transition-colors border border-surface-container-highest/60" type="button">
                            Discard
                        </button>
                        <button class="h-10 px-5 rounded-xl bg-secondary-container hover:bg-secondary-fixed text-on-secondary-container text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-transform active:scale-95" type="submit">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>{{ __('asic-admin.save_archive_record') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Metadata Standard Note --}}
            <div class="bg-primary-container text-on-primary rounded-xl p-5 shadow-xs flex flex-col gap-2">
                <div class="flex items-center gap-2 text-primary-fixed">
                    <span class="material-symbols-outlined text-[20px]">menu_book</span>
                    <span class="text-xs uppercase tracking-wider font-bold">Metadata Standard Note</span>
                </div>
                <p class="text-xs text-on-primary/90 leading-relaxed">
                    {{ __('asic-admin.metadata_note') }}
                </p>
                <div class="mt-1 flex items-center gap-3 text-primary-fixed-dim text-xs font-mono">
                    <span>OAI-PMH Compliant</span>
                    <span>•</span>
                    <span>Dublin Core Standard</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Sliding Drawer: Add Past Seminar Edition --}}
    <div x-show="showDrawer" x-cloak class="fixed inset-0 z-50 overflow-hidden" @keydown.window.escape="showDrawer = false">
        {{-- Backdrop --}}
        <div x-show="showDrawer" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showDrawer = false" class="absolute inset-0 bg-primary-fixed-dim/20 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="showDrawer" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="w-screen max-w-lg bg-surface-container-lowest shadow-2xl flex flex-col justify-between border-l border-surface-container-highest">
                
                {{-- Drawer Header --}}
                <div class="p-5 bg-primary text-on-primary flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[24px]">archive</span>
                        <div>
                            <h3 class="text-base font-bold leading-tight">{{ __('asic-admin.add_past_edition') }}</h3>
                            <span class="text-primary-fixed-dim text-xs">Faculty of Agriculture ASIC Archiving Engine</span>
                        </div>
                    </div>
                    <button @click="showDrawer = false" class="w-8 h-8 rounded-full bg-primary-container text-on-primary flex items-center justify-center hover:bg-tertiary-container transition-colors" type="button">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                {{-- Drawer Body --}}
                <div class="p-5 overflow-y-auto flex-1 flex flex-col gap-4 text-xs sm:text-sm">
                    <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container-highest/60 text-xs">
                        <span class="font-bold text-on-surface-variant uppercase block mb-1">Quick Instructions</span>
                        <p class="text-on-surface">Enter the past conference edition series to generate a persistent record. Upload the definitive published proceedings file along with photo media archives.</p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-on-surface">Conference Edition Name</label>
                        <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/40 focus:outline-none" placeholder="e.g. ASIC 2019 - Inaugural Pilot" type="text">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-on-surface">Hosting Location & Dates</label>
                        <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/40 focus:outline-none" placeholder="e.g. Nov 2-4, 2019 • Pangeran Beach Hotel, Padang" type="text">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-on-surface">Total Presented Manuscripts</label>
                        <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/40 focus:outline-none" placeholder="120" type="number">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-on-surface">Digital Object Identifier (DOI)</label>
                        <input class="h-10 px-3 rounded-xl bg-surface-container-low text-on-surface font-mono text-xs border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/40 focus:outline-none" placeholder="10.1063/..." type="text">
                    </div>

                    <div @click="$store.toast.show('Select Master Proceedings PDF file (up to 250MB)...', 'info')" class="rounded-xl p-5 bg-surface-container-low border-2 border-dashed border-outline-variant/60 text-center flex flex-col items-center justify-center cursor-pointer hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-primary text-[28px] mb-1">file_upload</span>
                        <span class="text-xs font-semibold text-on-surface">Select Master Proceedings PDF</span>
                        <span class="text-[11px] text-on-surface-variant">Up to 250MB (Single or multi-part archive)</span>
                    </div>
                </div>

                {{-- Drawer Footer --}}
                <div class="p-4 bg-surface-container-low flex items-center justify-end gap-3 border-t border-surface-container-highest/60">
                    <button @click="showDrawer = false" class="h-10 px-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold transition-colors" type="button">
                        Cancel
                    </button>
                    <button @click="showDrawer = false; $store.toast.show('Archive record committed successfully.', 'success')" class="h-10 px-5 rounded-xl bg-secondary-container hover:bg-secondary-fixed text-on-secondary-container text-xs font-semibold transition-transform active:scale-95 shadow-xs" type="button">
                        {{ __('asic-admin.commit_archive_record') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
