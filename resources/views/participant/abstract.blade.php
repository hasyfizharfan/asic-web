@php
    $status = request()->query('status', 'accepted'); // submitted, under_review, accepted, rejected

    $statusConfig = [
        'submitted' => [
            'badge' => 'info',
            'label' => __('asic-status.submitted'),
            'icon' => 'schedule',
            'desc' => 'Your manuscript has been logged and is awaiting reviewer assignment.',
        ],
        'under_review' => [
            'badge' => 'amber',
            'label' => __('asic-status.under_review'),
            'icon' => 'pending',
            'desc' => 'Your manuscript is currently being evaluated by 2 scientific committee reviewers.',
        ],
        'revision' => [
            'badge' => 'warning',
            'label' => 'Revision Required',
            'icon' => 'edit_note',
            'desc' => 'The academic reviewer requested revisions on your abstract. Please review the comments below and upload your revised manuscript.',
        ],
        'accepted' => [
            'badge' => 'success',
            'label' => __('asic-status.accepted'),
            'icon' => 'check_circle',
            'desc' => 'Congratulations! Your abstract has been accepted for Oral Technical Session.',
        ],
        'rejected' => [
            'badge' => 'danger',
            'label' => __('asic-status.rejected'),
            'icon' => 'cancel',
            'desc' => 'We regret to inform you that your abstract was not accepted for this edition.',
        ],
    ];

    $currentConfig = $statusConfig[$status] ?? $statusConfig['accepted'];

    $abstract = [
        'id' => 'ASIC-2027-ABS-0089',
        'title' => 'Application of Biochar and Arbuscular Mycorrhizal Fungi in Restoring Acid Tropical Agricultural Soils',
        'topic' => 'Sustainable Food Production & Soil Health',
        'authors' => 'Prof. Dr. Ahmad Fauzi (1)*, Dr. Rahmat Hidayat (1), Dr. Kenji Tanaka (2)',
        'affiliations' => '(1) Department of Soil Science, Faculty of Agriculture, Universitas Andalas; (2) Kyoto University, Japan',
        'presentation_mode' => 'Oral Presentation (On-site / In-Person)',
        'submitted_at' => '12 April 2027, 14:22 WIB',
        'keywords' => 'Biochar, Mycorrhiza, Acid Soils, Soil Resilience, Ultisols',
        'body' => 'Acid tropical soils, particularly Ultisols and Oxisols in Southeast Asia, suffer from severe phosphorus deficiency, aluminum toxicity, and low organic carbon. This investigation evaluates the synergistic effect of pyrolyzed rice husk biochar combined with indigenous arbuscular mycorrhizal fungi (AMF) on maize growth and phosphorus uptake over two consecutive planting cycles in West Sumatra, Indonesia. The experimental design followed a randomized complete block with four replications across five amendment levels. Results demonstrate that biochar at 10 t/ha combined with 20 g AMF inoculum increased soil pH by 0.65 units, reduced exchangeable Al by 42%, and significantly boosted available phosphorus from 8.2 to 21.4 ppm. Furthermore, grain yield increased by 38.6% compared to untreated control plots. These findings suggest an accessible, high-impact soil amendment strategy for smallholder farmers in tropical farming regions.',
    ];
@endphp

@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="space-y-6">
    <!-- Header with 4-State Simulator Switcher -->
    <x-layout.page-header title="My Abstract Submission" subtitle="Review evaluation status, referee comments, and official decision letters">
        <!-- Status Switcher Query Pills -->
        <div class="flex items-center gap-1.5 p-1 bg-surface-container-low rounded-xl border border-surface-container-highest text-xs">
            <span class="text-[11px] font-bold text-on-surface-variant px-2 uppercase">Status Preview:</span>
            <a href="{{ route('fe.participant.abstract', ['status' => 'submitted']) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'submitted' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Submitted
            </a>
            <a href="{{ route('fe.participant.abstract', ['status' => 'under_review']) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'under_review' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Under Review
            </a>
            <a href="{{ route('fe.participant.abstract', ['status' => 'revision']) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'revision' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Revision Required
            </a>
            <a href="{{ route('fe.participant.abstract', ['status' => 'accepted']) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'accepted' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Accepted
            </a>
            <a href="{{ route('fe.participant.abstract', ['status' => 'rejected']) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'rejected' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Rejected
            </a>
        </div>
    </x-layout.page-header>

    <!-- Status Alert Banner -->
    <x-ui.alert :variant="$currentConfig['badge']" :title="$currentConfig['label']">
        <p>{{ $currentConfig['desc'] }}</p>
        @if($status === 'accepted')
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-ui.button size="sm" variant="success" href="{{ route('fe.participant.documents') }}" iconLeft="verified">
                    Download Official LoA
                </x-ui.button>
                <x-ui.button size="sm" variant="outline" href="{{ route('fe.participant.full-paper') }}" iconLeft="upload_file">
                    Upload Camera-Ready Paper
                </x-ui.button>
            </div>
        @elseif($status === 'revision')
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-ui.button size="sm" variant="primary" href="{{ route('fe.participant.full-paper') }}" iconLeft="upload_file">
                    Upload Revised Abstract (Unggah Revisi)
                </x-ui.button>
            </div>
        @endif
    </x-ui.alert>

    @if($status === 'revision')
        <!-- Reviewer Feedback Card -->
        <div class="p-6 bg-surface-container-lowest rounded-2xl border border-warning/40 shadow-xs space-y-3">
            <div class="flex items-center gap-2 text-warning font-bold text-sm">
                <span class="material-symbols-outlined">rate_review</span>
                <span>Catatan Evaluasi Reviewer (Reviewer Comments)</span>
            </div>
            <p class="text-xs text-on-surface leading-relaxed p-4 rounded-xl bg-surface-container-low border border-surface-container-highest">
                "The abstract background and field trial data are very promising. However, please explicitly articulate the statistical replication methodology (RCBD parameters) and specify the precise carrier formulation for the mycorrhizal inoculant. Please revise the abstract text and upload the updated version."
            </p>
            <div class="flex items-center justify-between text-xs text-on-surface-variant pt-1">
                <span>Evaluator: Academic Reviewer Panel</span>
                <span class="font-semibold text-error">Revision Window: 7 Days Remaining</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Abstract Manuscript Details -->
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="space-y-6">
                    <!-- Title & ID -->
                    <div class="space-y-2 border-b border-surface-container-highest pb-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-primary bg-primary-fixed/30 px-2.5 py-0.5 rounded-full border border-primary-fixed">
                                {{ $abstract['id'] }}
                            </span>
                            <span class="text-[11px] text-on-surface-variant font-mono">Submitted on {{ $abstract['submitted_at'] }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-on-surface font-display leading-snug">
                            {{ $abstract['title'] }}
                        </h2>
                    </div>

                    <!-- Meta Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="block font-semibold text-on-surface-variant uppercase text-[10px] tracking-wider">Research Track</span>
                            <span class="block font-bold text-on-surface mt-0.5">{{ $abstract['topic'] }}</span>
                        </div>
                        <div>
                            <span class="block font-semibold text-on-surface-variant uppercase text-[10px] tracking-wider">Presentation Mode</span>
                            <span class="block font-bold text-primary mt-0.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">co_present</span>
                                <span>{{ $abstract['presentation_mode'] }}</span>
                            </span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block font-semibold text-on-surface-variant uppercase text-[10px] tracking-wider">Authors & Affiliations</span>
                            <span class="block font-medium text-on-surface mt-0.5">{{ $abstract['authors'] }}</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">{{ $abstract['affiliations'] }}</span>
                        </div>
                    </div>

                    <!-- Abstract Body -->
                    <div class="border-t border-surface-container-highest pt-4">
                        <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider mb-2 font-display">Abstract Text</h3>
                        <p class="text-sm text-on-surface leading-relaxed text-justify bg-surface-container-low p-5 rounded-xl border border-surface-container-highest font-sans">
                            {{ $abstract['body'] }}
                        </p>
                    </div>

                    <!-- Keywords -->
                    <div class="flex items-center gap-2 pt-2">
                        <span class="text-xs font-bold text-on-surface-variant uppercase text-[10px] tracking-wider">Keywords:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(explode(', ', $abstract['keywords']) as $kw)
                                <span class="px-2 py-0.5 rounded-md bg-surface-container-high text-xs font-medium text-on-surface">
                                    {{ $kw }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-slot:footer>
                    <div class="w-full flex items-center justify-between text-xs">
                        <span class="text-on-surface-variant">Need to update title or co-author affiliation?</span>
                        <x-ui.button size="sm" variant="outline" iconLeft="edit" @click="$store.toast.add('Abstract edit window is open until review deadline', 'info')">
                            Request Modification
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </div>

        <!-- Right Column: Reviewer Feedback & Milestones -->
        <div class="space-y-6">
            <!-- Review Decision Box -->
            <x-ui.card title="Committee Evaluation" icon="rate_review">
                @if($status === 'accepted')
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-bold text-primary font-display">4.8 / 5.0</span>
                            <span class="text-xs text-on-surface-variant font-semibold">(Score: Highly Recommended)</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-tertiary-fixed/20 border border-tertiary-fixed text-xs space-y-2 text-on-surface">
                            <p class="font-bold text-primary">Reviewer 1 Comments:</p>
                            <p class="italic text-on-surface-variant">"Well-designed multi-season field experiment. The synergistic effect between biochar and AMF is clearly presented with robust phosphorus indices. Recommended for Oral session."</p>
                            <p class="font-bold text-primary mt-2">Reviewer 2 Comments:</p>
                            <p class="italic text-on-surface-variant">"Excellent methodology. In the camera-ready manuscript, please include soil temperature readings if recorded."</p>
                        </div>
                    </div>
                @elseif($status === 'rejected')
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-bold text-error font-display">2.2 / 5.0</span>
                            <span class="text-xs text-on-surface-variant font-semibold">(Score: Below threshold)</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-error-container/30 border border-error/20 text-xs space-y-1.5 text-on-surface">
                            <p class="font-bold text-error">Scientific Committee Feedback:</p>
                            <p class="text-on-surface-variant">"The study scope does not sufficiently fit the focus on sustainable agrifood resilience. Statistical controls for environmental replicates were not clearly stated in the methodology."</p>
                        </div>
                    </div>
                @elseif($status === 'under_review')
                    <div class="text-center py-6 space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-800 flex items-center justify-center animate-spin">
                            <span class="material-symbols-outlined text-[24px]">hourglass_top</span>
                        </div>
                        <p class="text-sm font-bold text-on-surface">Peer Review in Progress</p>
                        <p class="text-xs text-on-surface-variant max-w-xs mx-auto">
                            Two independent reviewers from the scientific committee are evaluating your submission. Results will be notified via email.
                        </p>
                    </div>
                @else
                    <div class="text-center py-6 space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-blue-100 text-blue-800 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[24px]">fact_check</span>
                        </div>
                        <p class="text-sm font-bold text-on-surface">Initial Screening</p>
                        <p class="text-xs text-on-surface-variant max-w-xs mx-auto">
                            Your manuscript is undergoing editorial screening for formatting, word count, and scientific scope.
                        </p>
                    </div>
                @endif
            </x-ui.card>

            <!-- Submission Stepper -->
            <x-ui.card title="Milestone Status" icon="checklist">
                <x-ui.timeline :steps="[
                    ['title' => 'Abstract Submitted', 'date' => '12 Apr', 'status' => 'completed', 'description' => 'Received by editorial office.'],
                    ['title' => 'Peer Review Phase', 'date' => '19 Apr', 'status' => in_array($status, ['under_review', 'accepted', 'rejected']) ? 'completed' : 'current', 'description' => 'Double-blind evaluation.'],
                    ['title' => 'Committee Decision', 'date' => in_array($status, ['accepted', 'rejected']) ? '24 Apr' : 'Pending', 'status' => in_array($status, ['accepted', 'rejected']) ? 'completed' : 'upcoming', 'description' => 'Official LoA announcement.'],
                    ['title' => 'Payment Settlement', 'date' => 'Due 30 Jun', 'status' => $status === 'accepted' ? 'current' : 'upcoming', 'description' => 'Early bird tariff slot.'],
                ]" />
            </x-ui.card>
        </div>
    </div>
</div>
@endsection

