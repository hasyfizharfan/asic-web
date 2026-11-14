@extends('layouts.portal', [
    'title' => __('asic-admin.overview_title') . ' — ASIC 2027',
    'role' => 'admin',
    'roleName' => 'Super Admin',
    'nav' => $adminNav
])

@section('content')
<div class="flex flex-col gap-6" x-data="{ activityFilter: 'all' }">

    {{-- Status Ribbon --}}
    <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-surface-container-low shadow-xs border border-surface-container-highest/60">
        <div class="flex items-center gap-3 min-w-0">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-secondary-container text-on-secondary-container shrink-0">
                <span class="material-symbols-outlined text-[18px]">verified</span>
            </span>
            <div class="flex flex-col min-w-0">
                <span class="font-semibold text-on-surface text-sm sm:text-base">{{ __('asic-admin.portal_synced') }}</span>
                <span class="text-xs sm:text-sm text-on-surface-variant truncate">{{ __('asic-admin.cycle_active') }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-highest text-on-surface font-mono text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                {{ __('asic-admin.system_live') }}
            </span>
            <span class="font-mono text-xs text-on-surface-variant hidden sm:inline">{{ __('asic-admin.timezone') }}</span>
        </div>
    </div>

    {{-- Page Header & Administrative Quick Actions --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-2 border-b border-surface-container-highest/60">
        <div class="flex flex-col max-w-2xl">
            <div class="flex items-center gap-1.5 text-primary text-xs font-semibold uppercase tracking-wider mb-1">
                <span class="material-symbols-outlined text-[16px]">analytics</span>
                <span>{{ __('asic-admin.command_center') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-admin.overview_title') }}</h1>
            <p class="text-sm sm:text-base text-on-surface-variant mt-1">
                {{ __('asic-admin.overview_subtitle') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button onclick="window.print()" type="button" class="group inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-lowest text-on-surface text-sm font-semibold shadow-xs hover:shadow-sm hover:bg-surface-container-low transition-all border border-surface-container-highest">
                <span class="material-symbols-outlined text-[18px] text-primary group-hover:scale-110 transition-transform">print</span>
                <span>{{ __('asic-admin.print_summary') }}</span>
            </button>
            <button @click="$store.toast.show('Exporting Executive Report (CSV)...', 'info')" type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-secondary-container text-on-secondary-container text-sm font-semibold shadow-xs hover:shadow-sm hover:brightness-95 transition-all">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>{{ __('asic-admin.export_report') }}</span>
            </button>
        </div>
    </div>

    {{-- 3 Read-only Summary Metric Cards (Bento Array) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Metric Card 1: Total Registrants --}}
        <div class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-6 shadow-xs border border-surface-container-highest/60 hover:shadow-sm transition-shadow flex flex-col justify-between">
            <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-primary/5 pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-surface-container-low text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">groups</span>
                        </span>
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-admin.total_registrants') }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed text-xs font-semibold">
                        <span class="material-symbols-outlined text-[14px]">trending_up</span>
                        {{ __('asic-admin.vs_target') }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-bold text-on-surface tracking-tight">TBA</span>
                    <span class="text-xs text-on-surface-variant">{{ __('asic-admin.enrolled') }}</span>
                </div>
            </div>
            <div class="mt-4 pt-3 bg-surface-container-low/50 -mx-6 -mb-6 px-6 pb-4 rounded-b-xl border-t border-surface-container-highest/40">
                <p class="text-xs text-on-surface-variant flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-primary">target</span>
                    {{ __('asic-admin.registrant_target') }}
                </p>
            </div>
        </div>

        {{-- Metric Card 2: Submitted Abstracts --}}
        <div class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-6 shadow-xs border border-surface-container-highest/60 hover:shadow-sm transition-shadow flex flex-col justify-between">
            <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-tertiary-fixed/20 pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-surface-container-low text-tertiary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">article</span>
                        </span>
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-admin.submitted_abstracts') }}</span>
                    </div>
                    <span class="font-mono text-xs text-tertiary px-2.5 py-0.5 rounded bg-tertiary-fixed/30 font-semibold">{{ __('asic-admin.peer_review') }}</span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-bold text-on-surface tracking-tight">TBA</span>
                    <span class="text-xs text-on-surface-variant">{{ __('asic-admin.manuscripts') }}</span>
                </div>
            </div>
            <div class="mt-4 pt-3 bg-surface-container-low/50 -mx-6 -mb-6 px-6 pb-4 rounded-b-xl border-t border-surface-container-highest/40">
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="flex flex-col py-1 rounded bg-surface-container-lowest border border-surface-container-highest/40">
                        <span class="text-secondary font-bold">TBA</span>
                        <span class="text-on-surface-variant text-[10px]">{{ __('asic-admin.under_review') }}</span>
                    </div>
                    <div class="flex flex-col py-1 rounded bg-surface-container-lowest border border-surface-container-highest/40">
                        <span class="text-primary font-bold">TBA</span>
                        <span class="text-on-surface-variant text-[10px]">{{ __('asic-admin.accepted') }}</span>
                    </div>
                    <div class="flex flex-col py-1 rounded bg-surface-container-lowest border border-surface-container-highest/40">
                        <span class="text-error font-bold">TBA</span>
                        <span class="text-on-surface-variant text-[10px]">{{ __('asic-admin.rejected') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metric Card 3: Total Payments Received --}}
        <div class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-6 shadow-xs border border-surface-container-highest/60 hover:shadow-sm transition-shadow flex flex-col justify-between">
            <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-secondary-container/20 pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-surface-container-low text-secondary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">payments</span>
                        </span>
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-admin.total_payments') }}</span>
                    </div>
                    <span class="font-mono text-xs text-on-surface-variant px-2.5 py-0.5 rounded bg-surface-container-high font-medium">{{ __('asic-admin.audited') }}</span>
                </div>
                <div class="mt-2 flex flex-col">
                    <span class="text-2xl font-bold text-on-surface tracking-tight">IDR TBA <span class="text-base text-on-surface-variant font-normal">/ USD TBA</span></span>
                </div>
            </div>
            <div class="mt-4 pt-3 bg-surface-container-low/50 -mx-6 -mb-6 px-6 pb-4 rounded-b-xl border-t border-surface-container-highest/40 flex items-center justify-between">
                <div class="flex items-center gap-1.5 text-xs text-on-surface">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <span>{{ __('asic-admin.verified') }}: <strong class="font-bold">TBA</strong></span>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-on-surface-variant">
                    <span class="w-2 h-2 rounded-full bg-secondary-container"></span>
                    <span>{{ __('asic-admin.pending') }}: <strong class="font-bold">TBA</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Visualizations Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Chart 1: Submission Trajectory (7 cols) --}}
        <div class="lg:col-span-7 rounded-xl bg-surface-container-lowest p-6 shadow-xs border border-surface-container-highest/60 flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-on-surface">{{ __('asic-admin.trajectory_title') }}</h2>
                        <p class="text-xs sm:text-sm text-on-surface-variant mt-0.5">{{ __('asic-admin.trajectory_desc') }}</p>
                    </div>
                    {{-- Legend --}}
                    <div class="flex items-center gap-3 text-xs shrink-0 font-medium">
                        <div class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-primary"></span>
                            <span class="text-on-surface">{{ __('asic-admin.track_oral') }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-secondary-container"></span>
                            <span class="text-on-surface">{{ __('asic-admin.track_poster') }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-surface-dim"></span>
                            <span class="text-on-surface-variant">{{ __('asic-admin.track_listener') }}</span>
                        </div>
                    </div>
                </div>

                {{-- SVG Visualization (Timeline) --}}
                <div class="w-full relative h-60 flex flex-col justify-end bg-surface-container-low/40 rounded-xl p-4 border border-surface-container-highest/40">
                    <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 540 180">
                        <defs>
                            <linearGradient id="oralGrad" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#005226" stop-opacity="0.3"></stop>
                                <stop offset="100%" stop-color="#005226" stop-opacity="0.0"></stop>
                            </linearGradient>
                            <linearGradient id="posterGrad" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#fdcc22" stop-opacity="0.35"></stop>
                                <stop offset="100%" stop-color="#fdcc22" stop-opacity="0.0"></stop>
                            </linearGradient>
                        </defs>
                        {{-- Guide horizontal ticks --}}
                        <line stroke="#E2E8F0" stroke-dasharray="3 3" stroke-width="1" x1="0" x2="540" y1="30" y2="30"></line>
                        <line stroke="#E2E8F0" stroke-dasharray="3 3" stroke-width="1" x1="0" x2="540" y1="80" y2="80"></line>
                        <line stroke="#E2E8F0" stroke-dasharray="3 3" stroke-width="1" x1="0" x2="540" y1="130" y2="130"></line>
                        {{-- Oral Presentation Area + Curve --}}
                        <path d="M 0 140 Q 90 120 180 90 T 360 45 T 540 20 L 540 170 L 0 170 Z" fill="url(#oralGrad)"></path>
                        <path d="M 0 140 Q 90 120 180 90 T 360 45 T 540 20" fill="none" stroke="#005226" stroke-width="2.5"></path>
                        {{-- Poster Presentation Area + Curve --}}
                        <path d="M 0 160 Q 90 145 180 125 T 360 85 T 540 60 L 540 170 L 0 170 Z" fill="url(#posterGrad)"></path>
                        <path d="M 0 160 Q 90 145 180 125 T 360 85 T 540 60" fill="none" stroke="#745b00" stroke-width="2"></path>
                        {{-- Listener Dots / Step Curve --}}
                        <path d="M 0 165 Q 90 160 180 150 T 360 130 T 540 115" fill="none" stroke="#707a6f" stroke-dasharray="4 4" stroke-width="1.5"></path>
                        {{-- Data Keypoints --}}
                        <circle cx="180" cy="90" fill="#005226" r="4" stroke="#ffffff" stroke-width="2"></circle>
                        <circle cx="360" cy="45" fill="#005226" r="4" stroke="#ffffff" stroke-width="2"></circle>
                        <circle cx="540" cy="20" fill="#005226" r="4" stroke="#ffffff" stroke-width="2"></circle>
                    </svg>
                    {{-- Timeline axis labels --}}
                    <div class="flex justify-between items-center text-on-surface-variant font-mono text-xs pt-2 border-t border-surface-container-highest/40 mt-1">
                        <span>Nov 2026</span>
                        <span>Dec 2026</span>
                        <span>Jan 2027</span>
                        <span>Feb 2027 (Mid)</span>
                        <span>Mar 2027 (Closing)</span>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between pt-3 border-t border-surface-container-highest/40">
                <span class="text-xs text-on-surface-variant">{{ __('asic-admin.early_bird_closes_in') }}</span>
                <a class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1" href="{{ route('fe.secretariat.abstracts.index') }}">
                    {{ __('asic-admin.detailed_submissions_log') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
        </div>

        {{-- Chart 2: Revenue Distribution (5 cols) --}}
        <div class="lg:col-span-5 rounded-xl bg-surface-container-lowest p-6 shadow-xs border border-surface-container-highest/60 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-lg font-bold text-on-surface">{{ __('asic-admin.revenue_dist_title') }}</h2>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant font-medium">Channels</span>
                </div>
                <p class="text-xs text-on-surface-variant mb-4">{{ __('asic-admin.revenue_dist_desc') }}</p>

                {{-- Donut Chart & Stat Clusters --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 my-auto py-2">
                    {{-- SVG Donut Chart --}}
                    <div class="relative w-36 h-36 shrink-0 flex items-center justify-center">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                            {{-- Background Ring --}}
                            <circle cx="60" cy="60" fill="none" r="48" stroke="#f0eded" stroke-width="16"></circle>
                            {{-- Bank Transfer --}}
                            <circle cx="60" cy="60" fill="none" r="48" stroke="#005226" stroke-dasharray="301.59" stroke-dashoffset="175" stroke-linecap="round" stroke-width="16"></circle>
                            {{-- Virtual Account --}}
                            <circle class="origin-center rotate-45" cx="60" cy="60" fill="none" r="48" stroke="#fdcc22" stroke-dasharray="301.59" stroke-dashoffset="217" stroke-linecap="round" stroke-width="16"></circle>
                            {{-- PayPal --}}
                            <circle class="origin-center rotate-[150deg]" cx="60" cy="60" fill="none" r="48" stroke="#3e6900" stroke-dasharray="301.59" stroke-dashoffset="247" stroke-linecap="round" stroke-width="16"></circle>
                            {{-- Pending --}}
                            <circle class="origin-center rotate-[230deg]" cx="60" cy="60" fill="none" r="48" stroke="#bfc9bd" stroke-dasharray="301.59" stroke-dashoffset="265" stroke-linecap="round" stroke-width="16"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                            <span class="text-sm font-bold text-on-surface leading-tight">ASIC</span>
                            <span class="text-[10px] uppercase font-semibold text-on-surface-variant tracking-wider">Gateway</span>
                        </div>
                    </div>
                    {{-- Legend List --}}
                    <div class="flex flex-col gap-1.5 w-full max-w-[200px]">
                        <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-surface-container-low transition-colors text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                                <span class="text-on-surface font-medium">{{ __('asic-admin.channel_bank') }}</span>
                            </div>
                            <span class="font-mono font-semibold text-on-surface-variant">TBA</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-surface-container-low transition-colors text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-secondary-container"></span>
                                <span class="text-on-surface font-medium">{{ __('asic-admin.channel_va') }}</span>
                            </div>
                            <span class="font-mono font-semibold text-on-surface-variant">TBA</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-surface-container-low transition-colors text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-tertiary-container"></span>
                                <span class="text-on-surface font-medium">{{ __('asic-admin.channel_paypal') }}</span>
                            </div>
                            <span class="font-mono font-semibold text-on-surface-variant">TBA</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-surface-container-low transition-colors text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-outline-variant"></span>
                                <span class="text-on-surface font-medium">{{ __('asic-admin.channel_pending') }}</span>
                            </div>
                            <span class="font-mono font-semibold text-secondary">TBA</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 bg-surface-container-low/50 rounded-lg p-3 flex items-center justify-between text-xs text-on-surface-variant border border-surface-container-highest/40">
                <span>{{ __('asic-admin.banking_partners') }}</span>
                <span class="material-symbols-outlined text-[18px] text-primary">account_balance</span>
            </div>
        </div>
    </div>

    {{-- Recent System Activities Panel --}}
    <div class="rounded-xl bg-surface-container-lowest p-6 shadow-xs border border-surface-container-highest/60 flex flex-col gap-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-surface-container-highest/40">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-surface-container-low text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">history</span>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-on-surface">{{ __('asic-admin.recent_activities') }}</h2>
                    <p class="text-xs sm:text-sm text-on-surface-variant">{{ __('asic-admin.recent_activities_desc') }}</p>
                </div>
            </div>
            {{-- Activity Filter Tabs --}}
            <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-container-low self-start md:self-auto">
                <button @click="activityFilter = 'all'" :class="activityFilter === 'all' ? 'bg-surface-container-lowest text-on-surface shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium'" class="px-3 py-1.5 rounded-lg text-xs transition-all" type="button">{{ __('asic-admin.filter_all') }}</button>
                <button @click="activityFilter = 'registration'" :class="activityFilter === 'registration' ? 'bg-surface-container-lowest text-on-surface shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium'" class="px-3 py-1.5 rounded-lg text-xs transition-all" type="button">{{ __('asic-admin.filter_registration') }}</button>
                <button @click="activityFilter = 'payment'" :class="activityFilter === 'payment' ? 'bg-surface-container-lowest text-on-surface shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium'" class="px-3 py-1.5 rounded-lg text-xs transition-all" type="button">{{ __('asic-admin.filter_payment') }}</button>
                <button @click="activityFilter = 'review'" :class="activityFilter === 'review' ? 'bg-surface-container-lowest text-on-surface shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium'" class="px-3 py-1.5 rounded-lg text-xs transition-all" type="button">{{ __('asic-admin.filter_review') }}</button>
            </div>
        </div>

        {{-- Feed List with Alpine Filter --}}
        <div class="flex flex-col gap-2">
            {{-- Item 1: Abstract Submission --}}
            <div x-show="activityFilter === 'all' || activityFilter === 'review'" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl hover:bg-surface-container-low/70 transition-colors border border-transparent hover:border-surface-container-highest/60">
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-surface-container-high text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">upload_file</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <p class="text-sm text-on-surface leading-snug">
                            <strong class="font-semibold text-primary">[Participant Name]</strong>
                            <span class="text-on-surface-variant">([Institution])</span> submitted abstract 
                            <span class="font-semibold text-on-surface">"[Abstract Title TBA]"</span>
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-secondary-container/40 text-on-secondary-container">Abstract Upload</span>
                            <span class="text-xs text-on-surface-variant">Track: Agronomy & Food Security</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 sm:self-center self-end">
                    <span class="text-xs text-on-surface-variant whitespace-nowrap">10 mins ago</span>
                    <a href="{{ route('fe.secretariat.abstracts.show', ['id' => 'ABS-0104']) }}" class="p-1 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors" title="Inspect">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                </div>
            </div>

            {{-- Item 2: Payment Verified --}}
            <div x-show="activityFilter === 'all' || activityFilter === 'payment'" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl hover:bg-surface-container-low/70 transition-colors border border-transparent hover:border-surface-container-highest/60">
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-primary-fixed text-on-primary-fixed flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">task_alt</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <p class="text-sm text-on-surface leading-snug">
                            Payment of <strong class="font-semibold text-on-surface">IDR TBA</strong> verified for 
                            <span class="font-mono text-xs font-semibold text-primary">Invoice No. TBA</span> by Treasurer
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-container-high text-primary">Finance Cleared</span>
                            <span class="text-xs text-on-surface-variant">Channel: Bank Nagari Virtual Account</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 sm:self-center self-end">
                    <span class="text-xs text-on-surface-variant whitespace-nowrap">35 mins ago</span>
                    <a href="{{ route('fe.treasurer.payments.show', ['id' => 'PAY-0104']) }}" class="p-1 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors" title="Inspect">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                </div>
            </div>

            {{-- Item 3: LOA Generated --}}
            <div x-show="activityFilter === 'all' || activityFilter === 'review'" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl hover:bg-surface-container-low/70 transition-colors border border-transparent hover:border-surface-container-highest/60">
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <p class="text-sm text-on-surface leading-snug">
                            Letter of Acceptance (LOA) generated for <strong class="font-semibold text-on-surface">[Participant Name]</strong>
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-container-high text-tertiary">LOA Dispatch</span>
                            <span class="text-xs text-on-surface-variant">Automatic Digital Signature: Dean Approval</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 sm:self-center self-end">
                    <span class="text-xs text-on-surface-variant whitespace-nowrap">1 hour ago</span>
                    <a href="{{ route('fe.admin.templates') }}" class="p-1 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors" title="Inspect">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                </div>
            </div>

            {{-- Item 4: International Registration --}}
            <div x-show="activityFilter === 'all' || activityFilter === 'registration'" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl hover:bg-surface-container-low/70 transition-colors border border-transparent hover:border-surface-container-highest/60">
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-surface-container-high text-secondary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">public</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <p class="text-sm text-on-surface leading-snug">
                            <strong class="font-semibold text-on-surface">[Participant Name]</strong> registered under <span class="font-semibold text-secondary">International Presenter track</span>
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-secondary-container/30 text-on-secondary-container">Track Enrollment</span>
                            <span class="text-xs text-on-surface-variant">Early-bird tariff applied</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 sm:self-center self-end">
                    <span class="text-xs text-on-surface-variant whitespace-nowrap">2 hours ago</span>
                    <a href="{{ route('fe.admin.users') }}" class="p-1 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors" title="Inspect">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
