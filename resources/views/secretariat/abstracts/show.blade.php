@extends('layouts.portal', [
    'role' => 'secretariat',
    'roleName' => 'Secretariat Admin',
    'nav' => $secNav,
    'title' => __('asic-secretariat.detail.page_title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    decision: 'accept',
    memo: 'Paper recommended by Track Chair for Session Room 2A.',
    applyDecision(type) {
        this.decision = type;
        let title = '';
        let msg = '';
        if (type === 'accept') {
            title = 'Decision Dispatched: ACCEPT';
            msg = 'LOA & Invoice Ref: LOA/ASIC2027/0104 issued to author.contact@institution.ac.id';
        } else if (type === 'revision') {
            title = 'Revision Request Sent';
            msg = 'Notice for resubmission with 7-day window sent to corresponding author.';
        } else {
            title = 'Rejection Letter Dispatched';
            msg = 'Official rejection notification and reviewers synthesis dispatched.';
        }
        $store.toast.show(title + ' — ' + msg, type === 'reject' ? 'warning' : 'success');
    }
}" class="flex flex-col w-full">

    {{-- Breadcrumb & Top Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2 text-xs text-on-surface-variant font-medium">
                <a href="{{ route('fe.secretariat.abstracts.index') }}" class="hover:text-primary transition flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">folder_open</span>
                    <span>{{ __('asic-nav.abstracts') }}</span>
                </a>
                <span class="text-outline-variant font-mono">/</span>
                <span class="font-mono text-xs px-2 py-0.5 rounded bg-surface-container-highest text-on-surface font-semibold">#ABS-2027-0104</span>
                <span class="text-outline-variant font-mono">/</span>
                <span class="text-on-surface font-semibold">{{ __('asic-secretariat.detail.breadcrumb') }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">{{ __('asic-secretariat.detail.page_title') }}</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-surface-container text-primary text-[11px] font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    Secretariat Master-Detail
                </span>
            </div>
        </div>

        {{-- Back to Abstract List CTA --}}
        <a href="{{ route('fe.secretariat.abstracts.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container text-on-surface text-xs font-semibold shadow-xs border border-surface-container-high transition self-start md:self-auto group">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
            <span>{{ __('asic-secretariat.detail.back_to_list') }}</span>
        </a>
    </div>

    {{-- 2-Column Master-Detail Layout --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        {{-- LEFT COLUMN: Abstract Information (7 cols) --}}
        <div class="xl:col-span-7 flex flex-col gap-6">
            {{-- Primary Paper Header Card --}}
            <div class="p-6 bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Status: Under Review
                        </span>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-xs font-medium">
                            <span class="material-symbols-outlined text-[14px]">eco</span>
                            Track: Sustainable Crop Production & Soil Health
                        </span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant text-xs font-semibold">
                        <span class="material-symbols-outlined text-[15px] text-primary">podium</span>
                        Oral Presentation
                    </span>
                </div>

                <div class="pt-1">
                    <h2 class="text-lg sm:text-xl font-bold text-on-surface leading-snug">
                        Agroecological Optimization of Indigenous Legume Inoculants for Soil Health and Carbon Sequestration in West Sumatra Tropical Agroecosystems
                    </h2>
                </div>

                {{-- Submission Metadata Chips Bar --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-surface-container-low rounded-lg text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">tag</span>
                        <div>
                            <span class="text-on-surface-variant text-[10px] block">Submission ID</span>
                            <span class="font-mono font-bold text-on-surface">#ABS-2027-0104</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">event_available</span>
                        <div>
                            <span class="text-on-surface-variant text-[10px] block">Submission Date</span>
                            <span class="font-semibold text-on-surface">14 May 2027 • 16:42 WIB</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">group</span>
                        <div class="min-w-0">
                            <span class="text-on-surface-variant text-[10px] block">Assigned Reviewers</span>
                            <span class="font-semibold text-on-surface truncate block" title="Prof. Dr. Antonius & Dr. Elvira">Prof. Antonius, Dr. Elvira</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Authors & Affiliations Card --}}
            <div class="p-6 bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">badge</span>
                        <h3 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.detail.authors_section') }}</h3>
                    </div>
                    <span class="text-[11px] text-primary font-semibold uppercase tracking-wider bg-surface-container px-2.5 py-0.5 rounded">
                        {{ __('asic-secretariat.detail.verified_submitter') }}
                    </span>
                </div>

                <div class="flex flex-col gap-3 text-xs">
                    {{-- Corresponding Author --}}
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low/60 border border-surface-container-high">
                        <div class="w-8 h-8 rounded-full bg-primary-container text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1*</div>
                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-on-surface text-sm">Dr. Farhan Harahap</span>
                                <span class="px-2 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed text-[10px] font-semibold">Corresponding Author</span>
                            </div>
                            <span class="text-on-surface-variant text-xs mt-1">Department of Soil Science & Land Resource Management, Faculty of Agriculture, Universitas Andalas, Padang, Indonesia</span>
                            <div class="flex flex-wrap items-center gap-4 mt-2 text-on-surface-variant text-[11px]">
                                <span class="flex items-center gap-1 text-primary">
                                    <span class="material-symbols-outlined text-[14px]">alternate_email</span>
                                    author.contact@institution.ac.id
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-outline">call</span>
                                    +62 812-3456-7890
                                </span>
                                <span class="flex items-center gap-1 font-mono text-secondary">
                                    <span class="material-symbols-outlined text-[14px]">fingerprint</span>
                                    ORCID: 0000-0002-1234-5678
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Co-Author 1 --}}
                    <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-surface-container-low/40 transition">
                        <div class="w-8 h-8 rounded-full bg-surface-container text-on-surface-variant flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</div>
                        <div class="flex flex-col flex-1">
                            <span class="font-semibold text-on-surface text-xs">Prof. Dr. Ir. Melinda Surya Dewi</span>
                            <span class="text-on-surface-variant text-[11px]">Research Center for Sustainable Tropical Agriculture, National Research & Innovation Agency (BRIN), Bogor</span>
                        </div>
                    </div>

                    {{-- Co-Author 2 --}}
                    <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-surface-container-low/40 transition">
                        <div class="w-8 h-8 rounded-full bg-surface-container text-on-surface-variant flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</div>
                        <div class="flex flex-col flex-1">
                            <span class="font-semibold text-on-surface text-xs">Dr. Ir. Rudi Hartono, M.P.</span>
                            <span class="text-on-surface-variant text-[11px]">Faculty of Agriculture, Universitas Andalas</span>
                        </div>
                    </div>
                </div>

                {{-- Keywords --}}
                <div class="pt-2 border-t border-surface-container-high flex flex-col gap-2">
                    <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-secretariat.detail.keywords') }}</span>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="px-2.5 py-1 rounded-full bg-surface-container text-xs text-on-surface font-medium">Bio-fertilizer</span>
                        <span class="px-2.5 py-1 rounded-full bg-surface-container text-xs text-on-surface font-medium">Nitrogen Fixation</span>
                        <span class="px-2.5 py-1 rounded-full bg-surface-container text-xs text-on-surface font-medium">Tropical Agroforestry</span>
                        <span class="px-2.5 py-1 rounded-full bg-surface-container text-xs text-on-surface font-medium">Soil Microbiome</span>
                        <span class="px-2.5 py-1 rounded-full bg-surface-container text-xs text-on-surface font-medium">West Sumatra</span>
                    </div>
                </div>
            </div>

            {{-- Abstract Structured Manuscript Card --}}
            <div class="p-6 bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">menu_book</span>
                        <h3 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.detail.manuscript_title') }}</h3>
                    </div>
                    <span class="font-mono text-xs text-on-surface-variant bg-surface-container-low px-2.5 py-0.5 rounded">
                        {{ __('asic-secretariat.detail.words_count', ['count' => 248]) }}
                    </span>
                </div>

                <div class="space-y-4 text-xs text-on-surface-variant leading-relaxed">
                    <div class="p-4 rounded-lg bg-surface-container-low/50">
                        <span class="text-xs text-primary font-bold block mb-1">{{ __('asic-secretariat.detail.section_bg') }}</span>
                        <p>
                            Evaluation of native rhizobial strains isolated from undisturbed andosol and ultisol soils across the Mount Talang volcanic belt in West Sumatra. Conventional synthetic nitrogen fertilizers in humid tropical uplands suffer significant nutrient leaching, exacerbating acidification and nitrous oxide emissions. This study investigates the symbiotic capacity of four indigenous <em>Bradyrhizobium</em> strains to bolster biological nitrogen fixation (BNF) while advancing long-term soil organic carbon (SOC) persistence under continuous cassava-legume intercropping.
                        </p>
                    </div>

                    <div class="p-4 rounded-lg bg-surface-container-low/50">
                        <span class="text-xs text-primary font-bold block mb-1">{{ __('asic-secretariat.detail.section_method') }}</span>
                        <p>
                            A multi-locational randomized complete block trial (RCBD) with five replications was deployed over two consecutive monsoonal cropping cycles across Solok and Tanah Datar regencies (elevation 650–920 m a.s.l.). Treatments comprised non-inoculated controls, commercial synthetic N (120 kg N/ha), and single versus consortia inoculation of indigenous isolates (UNAND-Rz01 through UNAND-Rz04) co-formulated with biochar carriers. Acetylene reduction assays (ARA), shoot biomass accumulation, nodule occupancy via PCR-RFLP, and permanganate oxidizable carbon (POXC) were systematically quantified.
                        </p>
                    </div>

                    <div class="p-4 rounded-lg bg-surface-container-low/50">
                        <span class="text-xs text-primary font-bold block mb-1">{{ __('asic-secretariat.detail.section_results') }}</span>
                        <p>
                            Inoculated experimental plots exhibited a 34.2% increase in nodule density (<span class="font-mono">p &lt; 0.01</span>) and a 41.8% elevation in nitrogenase activity compared to untreated controls. Consortium treatment UNAND-Rz02+04 yielded dry biomass equivalent to full synthetic fertilizer treatment while reducing estimated carbon footprint by 58%. Concurrently, active soil carbon fractions increased by 18.6% across 24 months, indicating favorable stabilization kinetics.
                        </p>
                    </div>

                    <div class="p-4 rounded-lg bg-surface-container-low/50">
                        <span class="text-xs text-primary font-bold block mb-1">{{ __('asic-secretariat.detail.section_conclusion') }}</span>
                        <p>
                            Indigenous rhizobial strains offer viable, low-cost bio-enhancement for smallholder farming communities throughout humid tropical archipelagos. Adoption of custom inoculant regimes mitigates reliance on chemical fertilizer subsidies while restoring degraded volcanic slope soils.
                        </p>
                    </div>
                </div>

                {{-- Supplementary File Attachment --}}
                <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container text-xs border border-surface-container-high">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">picture_as_pdf</span>
                        <span class="text-on-surface font-semibold">ABS-0104_Original_Draft_Manuscript.pdf</span>
                        <span class="font-mono text-on-surface-variant text-[11px]">(1.84 MB)</span>
                    </div>
                    <button @click="$store.toast.show('Downloading draft PDF...', 'info')" 
                            class="inline-flex items-center gap-1 text-primary hover:text-primary-container text-xs font-bold transition">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        <span>{{ __('asic-secretariat.detail.download_pdf') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Secretariat Decision & Official Document Preview (5 cols) --}}
        <div class="xl:col-span-5 flex flex-col gap-6 sticky top-20">
            {{-- Reviewer Assignment & Feedback Panel (Role Baru: Reviewer Abstrak) --}}
            <div class="p-6 bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-4"
                 x-data="{ assignedReviewer: 'sarah', assignSuccess: false }">
                <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[22px] text-primary">rate_review</span>
                        <h3 class="text-sm font-bold text-on-surface">Reviewer Akademik</h3>
                    </div>
                    <span class="text-[11px] text-primary font-semibold px-2 py-0.5 rounded bg-primary-fixed">Scientific Board</span>
                </div>

                {{-- Assign Reviewer Action --}}
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-on-surface-variant">Tugaskan Reviewer (Assign Reviewer)</label>
                    <div class="flex gap-2">
                        <select x-model="assignedReviewer" class="flex-1 px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-xs text-on-surface outline-none focus:border-primary">
                            <option value="sarah">Dr. Sarah Jenkins, Ph.D. (Crop Science)</option>
                            <option value="fauzi">Prof. Dr. Ir. Ahmad Fauzi (Agronomy)</option>
                            <option value="rina">Dr. Ir. Rina Marlina, M.Sc. (Soil Science)</option>
                        </select>
                        <button type="button" 
                                @click="assignSuccess = true; $store.toast.show('Reviewer berhasil ditugaskan!', 'success')"
                                class="px-3.5 py-2 rounded-lg bg-primary hover:bg-primary-container text-white text-xs font-bold transition">
                            Tugaskan
                        </button>
                    </div>
                </div>

                {{-- Reviewer Evaluation Synthesis Card --}}
                <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-high space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Hasil Review (Evaluasi)</span>
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#c8e6c9] text-emerald-900">
                            Rekomendasi: Accept
                        </span>
                    </div>
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        "Metodologi dan signifikansi temuan sangat kuat untuk ketahanan agrifood tropis. Direkomendasikan untuk presentasi oral di sesi utama."
                    </p>
                    <div class="flex items-center justify-between text-[11px] text-outline pt-1 border-t border-surface-container-highest">
                        <span>Reviewer: Dr. Sarah Jenkins</span>
                        <span>Status: Selesai Dinilai</span>
                    </div>
                </div>
            </div>

            {{-- Decision Control Panel Card --}}
            <div class="p-6 bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[22px] text-primary">gavel</span>
                        <h3 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.detail.decision_title') }}</h3>
                    </div>
                    <span class="text-[11px] text-primary font-semibold px-2 py-0.5 rounded bg-primary-fixed">Live Workflow</span>
                </div>

                <div class="p-3 rounded-lg bg-surface-container-low flex items-start gap-2.5 text-xs text-on-surface-variant">
                    <span class="material-symbols-outlined text-[18px] text-primary shrink-0 mt-0.5">info</span>
                    <p>{{ __('asic-secretariat.detail.decision_notice') }}</p>
                </div>

                {{-- Action Buttons Flow --}}
                <div class="flex flex-col gap-2.5 pt-1">
                    {{-- Accept Button --}}
                    <button @click="applyDecision('accept')" 
                            :class="decision === 'accept' ? 'ring-2 ring-primary ring-offset-2' : ''"
                            class="w-full text-left p-4 rounded-xl bg-[#F5C518] hover:bg-[#FAD54D] active:bg-[#EAB308] text-[#0F3D24] shadow-sm hover:shadow transition group flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#0F3D24]/10 flex items-center justify-center mt-0.5 shrink-0 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[20px]">task_alt</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold tracking-tight">{{ __('asic-secretariat.detail.btn_accept') }}</span>
                                <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">send</span>
                            </div>
                            <span class="text-[11px] text-[#0F3D24]/80 mt-0.5 leading-snug">{{ __('asic-secretariat.detail.btn_accept_desc') }}</span>
                        </div>
                    </button>

                    {{-- Request Revision Button --}}
                    <button @click="applyDecision('revision')" 
                            :class="decision === 'revision' ? 'ring-2 ring-primary ring-offset-2' : ''"
                            class="w-full text-left p-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface shadow-xs transition flex items-start gap-3 group">
                        <div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center mt-0.5 shrink-0 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[20px]">edit_note</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold">{{ __('asic-secretariat.detail.btn_revision') }}</span>
                                <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant mt-0.5 leading-snug">{{ __('asic-secretariat.detail.btn_revision_desc') }}</span>
                        </div>
                    </button>

                    {{-- Reject Button --}}
                    <button @click="applyDecision('reject')" 
                            :class="decision === 'reject' ? 'ring-2 ring-error ring-offset-2' : ''"
                            class="w-full text-left p-4 rounded-xl bg-red-50 hover:bg-red-100 text-error border border-red-200 transition flex items-start gap-3 group">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mt-0.5 shrink-0 text-error">
                            <span class="material-symbols-outlined text-[20px]">cancel</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-error">{{ __('asic-secretariat.detail.btn_reject') }}</span>
                                <span class="material-symbols-outlined text-[18px] text-error group-hover:translate-x-1 transition-transform">close</span>
                            </div>
                            <span class="text-[11px] text-red-700 mt-0.5 leading-snug">{{ __('asic-secretariat.detail.btn_reject_desc') }}</span>
                        </div>
                    </button>
                </div>

                {{-- Secretariat Note Field --}}
                <div class="flex flex-col gap-1.5 pt-2 border-t border-surface-container-high">
                    <label class="text-xs font-semibold text-on-surface-variant">{{ __('asic-secretariat.detail.memo_label') }}</label>
                    <input type="text" 
                           x-model="memo"
                           class="px-3.5 py-2 text-on-surface bg-surface-container-low rounded-lg text-xs shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20"
                           placeholder="{{ __('asic-secretariat.detail.memo_placeholder') }}">
                </div>
            </div>

            {{-- Letter Preview Box --}}
            <div class="p-6 bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">description</span>
                        <h3 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.detail.official_preview') }}</h3>
                    </div>
                    <span class="text-xs text-on-surface-variant bg-surface-container px-2 py-0.5 rounded flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                        Live Render
                    </span>
                </div>

                {{-- Formal Letterhead Paper Container --}}
                <div class="p-5 rounded-xl bg-surface-container-lowest shadow-sm border border-surface-container-high flex flex-col gap-4">
                    {{-- Letterhead Header --}}
                    <div class="flex items-center gap-3 pb-3 bg-surface-container-low/50 p-3 rounded-lg border border-surface-container-high">
                        <img src="{{ asset('images/asic/logo.svg') }}" alt="Seal" class="h-10 w-10 object-contain shrink-0">
                        <div class="flex flex-col">
                            <span class="text-xs leading-tight font-bold text-on-surface uppercase tracking-wide">Faculty of Agriculture • Universitas Andalas</span>
                            <span class="text-[11px] leading-tight text-primary font-semibold">Organizing Committee of ASIC 2027</span>
                            <span class="text-[10px] text-on-surface-variant">Limau Manis Campus, Padang, West Sumatra 25163, Indonesia</span>
                        </div>
                    </div>

                    {{-- Document Meta & Reference --}}
                    <div class="flex justify-between items-start text-on-surface-variant text-xs">
                        <div class="flex flex-col">
                            <span class="font-bold text-on-surface font-mono" x-show="decision === 'accept'">Ref: LOA/ASIC2027/0104/ORAL</span>
                            <span class="font-bold text-on-surface font-mono" x-show="decision === 'revision'">Ref: REV/ASIC2027/0104</span>
                            <span class="font-bold text-on-surface font-mono" x-show="decision === 'reject'">Ref: REJ/ASIC2027/0104</span>
                            <span class="text-[11px]">Padang, August 2027</span>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-surface-container text-primary font-bold">OFFICIAL NOTICE</span>
                            <span class="text-[10px] text-outline">Category: Oral Presentation</span>
                        </div>
                    </div>

                    {{-- Subject Line --}}
                    <div class="p-2.5 rounded bg-surface-container-low text-on-surface">
                        <span class="text-xs font-bold block" x-show="decision === 'accept'">Subject: Official Letter of Acceptance (LOA) & Participant Invitation</span>
                        <span class="text-xs font-bold block" x-show="decision === 'revision'">Subject: Notice for Abstract Manuscript Revision</span>
                        <span class="text-xs font-bold block" x-show="decision === 'reject'">Subject: Official Notification of Non-Acceptance</span>
                    </div>

                    {{-- Body Excerpt --}}
                    <div class="text-xs text-on-surface leading-relaxed space-y-2">
                        <p>Dear <strong>Dr. Farhan Harahap</strong> (and Co-authors),</p>
                        
                        <div x-show="decision === 'accept'">
                            <p class="text-justify">
                                On behalf of the Scientific Review Committee of ASIC 2027, we are pleased to inform you that your submitted abstract has been officially <strong>ACCEPTED</strong> for <strong>Oral Presentation</strong>:
                            </p>
                            <p class="font-semibold text-primary italic pl-3 py-1 bg-surface-container-low/70 rounded my-2 text-[11px]">
                                "Agroecological Optimization of Indigenous Legume Inoculants for Soil Health and Carbon Sequestration in West Sumatra Tropical Agroecosystems"
                            </p>
                            <div class="p-3 rounded-lg bg-surface-container-high/60 flex flex-col gap-1 text-[11px] my-2">
                                <div class="flex justify-between items-center font-bold">
                                    <span>Conference Fee (Domestic Early Bird):</span>
                                    <span class="font-mono text-primary">IDR 1,750,000 / USD 180</span>
                                </div>
                                <div class="flex justify-between items-center text-on-surface-variant text-[10px]">
                                    <span>Proforma Invoice: INV-ASIC27-0104</span>
                                    <span>Due: TBA</span>
                                </div>
                            </div>
                        </div>

                        <div x-show="decision === 'revision'">
                            <p class="text-justify">
                                The Scientific Review Committee has completed the preliminary peer-evaluation. We invite you to submit a revised version addressing the reviewer synthesis comments within 7 business days.
                            </p>
                        </div>

                        <div x-show="decision === 'reject'">
                            <p class="text-justify">
                                Thank you for your submission. After extensive review against our theme criteria and editorial capacity, we regret to inform you that your abstract cannot be accommodated for oral presentation at ASIC 2027.
                            </p>
                        </div>
                    </div>

                    {{-- Signatory Stamp Block --}}
                    <div class="pt-2 border-t border-surface-container-high">
                        <div class="p-3 rounded-lg border border-dashed border-outline/40 bg-surface-container-low/60 flex items-center gap-3 text-center justify-center">
                            <span class="material-symbols-outlined text-[20px] text-primary">verified</span>
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-on-surface">[Signed by Chairperson, ASIC 2027 Committee]</span>
                                <span class="text-[10px] text-outline">Organizing Committee Secretariat • Universitas Andalas</span>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Hash and Print Preview --}}
                    <div class="pt-1 flex items-center justify-between text-[11px] text-on-surface-variant">
                        <span class="font-mono flex items-center gap-1 text-[10px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            Hash: UNAND-2027-LOA-9F01A
                        </span>
                        <button @click="window.print()" class="text-primary hover:underline font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">print</span>
                            <span>{{ __('asic-secretariat.detail.print_preview') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

