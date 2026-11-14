@extends('layouts.portal', [
    'title' => __('asic-admin.permissions_title') . ' — ASIC 2027',
    'role' => 'admin',
    'roleName' => 'Super Admin',
    'nav' => $adminNav
])

@section('content')
<div class="flex flex-col gap-6" x-data="{
    secA: true,
    secB: true,
    secC: true,
    secD: true,
    allExpanded: true,
    toggleAll() {
        this.allExpanded = !this.allExpanded;
        this.secA = this.allExpanded;
        this.secB = this.allExpanded;
        this.secC = this.allExpanded;
        this.secD = this.allExpanded;
    }
}">

    {{-- Breadcrumb & Status Indicator --}}
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-1.5 text-on-surface-variant">
            <span>Access Control</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-primary font-semibold">{{ __('asic-admin.permissions_title') }}</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-mono text-xs">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            <span>{{ __('asic-admin.policy_version') }}</span>
        </div>
    </div>

    {{-- Page Header Section --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 bg-surface-container-lowest p-6 rounded-xl shadow-xs border border-surface-container-highest/60">
        <div class="flex flex-col gap-1 max-w-2xl">
            <span class="text-xs text-secondary font-bold uppercase tracking-wider">
                Access Control Policy • ASIC 2027 Committee
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-primary tracking-tight">
                {{ __('asic-admin.permissions_title') }}
            </h1>
            <p class="text-sm text-on-surface-variant">
                {{ __('asic-admin.permissions_subtitle') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button @click="$store.toast.show('Exporting Policy Audit Log (PDF)...', 'info')" type="button" class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-surface-container-low text-on-surface hover:bg-surface-container border border-surface-container-highest/60 transition-all">
                <span class="material-symbols-outlined text-[18px]">download_for_offline</span>
                <span>{{ __('asic-admin.audit_log_export') }}</span>
            </button>
            <button @click="$store.toast.show('{{ __('asic-admin.policy_saved_toast') }}: {{ __('asic-admin.policy_saved_toast_desc') }}', 'success')" type="button" class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-secondary-container text-on-secondary-container shadow-xs hover:brightness-95 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                <span>{{ __('asic-admin.save_policy_changes') }}</span>
            </button>
        </div>
    </div>

    {{-- Role Summary Cards (4 Columns) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Participant --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col justify-between gap-4 relative overflow-hidden group hover:shadow-sm transition-all">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-secondary-fixed/20 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-full bg-surface-container text-on-surface-variant text-xs font-medium">External</span>
                    <span class="material-symbols-outlined text-outline-variant text-[20px]">person_outline</span>
                </div>
                <span class="text-lg font-bold text-on-surface mt-1">Participant</span>
                <span class="text-xs text-on-surface-variant">Standard Attendee / Author</span>
            </div>
            <div class="flex items-baseline justify-between pt-1 border-t border-surface-container-highest/40">
                <div class="flex flex-col">
                    <span class="text-2xl font-bold text-primary">8</span>
                    <span class="text-xs text-on-surface-variant">Features (Self-service)</span>
                </div>
                <span class="font-mono text-xs text-on-surface-variant/70">Tier 0</span>
            </div>
        </div>

        {{-- Secretariat --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col justify-between gap-4 relative overflow-hidden group hover:shadow-sm transition-all">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-primary-fixed/30 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold">Internal Staff</span>
                    <span class="material-symbols-outlined text-primary text-[20px]">edit_note</span>
                </div>
                <span class="text-lg font-bold text-on-surface mt-1">Secretariat</span>
                <span class="text-xs text-on-surface-variant">Editorial & Academic Committee</span>
            </div>
            <div class="flex items-baseline justify-between pt-1 border-t border-surface-container-highest/40">
                <div class="flex flex-col">
                    <span class="text-2xl font-bold text-primary">24</span>
                    <span class="text-xs text-on-surface-variant">Features (Review & Verify)</span>
                </div>
                <span class="font-mono text-xs text-on-surface-variant/70">Tier 1</span>
            </div>
        </div>

        {{-- Treasurer --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col justify-between gap-4 relative overflow-hidden group hover:shadow-sm transition-all">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-secondary-fixed/40 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-full bg-secondary-fixed/40 text-on-secondary-fixed-variant text-xs font-semibold">Internal Staff</span>
                    <span class="material-symbols-outlined text-secondary text-[20px]">account_balance_wallet</span>
                </div>
                <span class="text-lg font-bold text-on-surface mt-1">Treasurer</span>
                <span class="text-xs text-on-surface-variant">Financial Desk & Audit</span>
            </div>
            <div class="flex items-baseline justify-between pt-1 border-t border-surface-container-highest/40">
                <div class="flex flex-col">
                    <span class="text-2xl font-bold text-secondary">16</span>
                    <span class="text-xs text-on-surface-variant">Features (Invoice & Settlement)</span>
                </div>
                <span class="font-mono text-xs text-on-surface-variant/70">Tier 2</span>
            </div>
        </div>

        {{-- Admin --}}
        <div class="bg-primary text-on-primary p-5 rounded-xl shadow-xs flex flex-col justify-between gap-4 relative overflow-hidden group hover:shadow-sm transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-tertiary-fixed/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-full bg-tertiary-container text-on-tertiary-container text-xs font-semibold">System Admin</span>
                    <span class="material-symbols-outlined text-tertiary-fixed text-[20px]">shield</span>
                </div>
                <span class="text-lg font-bold text-on-primary mt-1">Admin</span>
                <span class="text-xs text-primary-fixed-dim">Super Administrative Control</span>
            </div>
            <div class="flex items-baseline justify-between pt-1 border-t border-primary-container">
                <div class="flex flex-col">
                    <span class="text-2xl font-bold text-tertiary-fixed">All</span>
                    <span class="text-xs text-primary-fixed-dim">Full System Control</span>
                </div>
                <span class="font-mono text-xs text-tertiary-fixed/80">Tier 3 (Root)</span>
            </div>
        </div>
    </div>

    {{-- Quick Filter / Legend Strip --}}
    <div class="flex flex-wrap items-center justify-between gap-4 bg-surface-container-low px-4 py-2.5 rounded-xl border border-surface-container-highest/60 text-xs">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-on-surface-variant uppercase tracking-wider font-semibold">Access Badges:</span>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant font-semibold">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span> {{ __('asic-admin.badge_granted') }}
                </span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-secondary-fixed/50 text-on-secondary-fixed-variant font-semibold">
                    <span class="material-symbols-outlined text-[14px]">visibility</span> {{ __('asic-admin.badge_view_only') }}
                </span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px]">remove</span> {{ __('asic-admin.badge_no_access') }}
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="toggleAll()" class="text-primary font-semibold hover:underline flex items-center gap-1" type="button">
                <span class="material-symbols-outlined text-[16px]" x-text="allExpanded ? 'unfold_less' : 'unfold_more'"></span>
                <span x-text="allExpanded ? '{{ __('asic-admin.collapse_all') }}' : '{{ __('asic-admin.expand_all') }}'"></span>
            </button>
        </div>
    </div>

    {{-- Permissions Matrix Table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-highest/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant text-xs uppercase tracking-wider font-semibold border-b border-surface-container-highest/60">
                        <th class="py-3 px-4 w-72" scope="col">Feature / Module</th>
                        <th class="py-3 px-4 w-80" scope="col">Scope Description</th>
                        <th class="py-3 px-4 text-center w-32" scope="col">Participant</th>
                        <th class="py-3 px-4 text-center w-32" scope="col">Secretariat</th>
                        <th class="py-3 px-4 text-center w-32" scope="col">Treasurer</th>
                        <th class="py-3 px-4 text-center w-32" scope="col">Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">

                    {{-- SECTION A: USER & IDENTITY MANAGEMENT --}}
                    <tr @click="secA = !secA" class="bg-surface-container-high/60 cursor-pointer select-none group border-t border-b border-surface-container-highest/60">
                        <td class="py-2.5 px-4" colspan="6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px] text-primary transition-transform duration-200" :class="secA ? 'rotate-0' : '-rotate-90'">expand_more</span>
                                    <span class="font-bold text-primary">{{ __('asic-admin.sec_a_title') }}</span>
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-surface-container-lowest font-mono text-[10px] text-on-surface-variant border border-surface-container-highest/40">4 Sub-features</span>
                                </div>
                                <span class="text-xs text-on-surface-variant group-hover:text-primary">Click to toggle</span>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="secA" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Profile registration & reset</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Allows password recovery, basic contact updates, and affiliated institution records.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secA" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Registrant list & directory</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">View conference attendee rosters, verified affiliations, and dietary/presentation requirements.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-secondary-fixed/50 text-on-secondary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">visibility</span> View only</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secA" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Add/invite staff accounts</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Send onboarding invitations for desk operators, peer reviewers, and student volunteers.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secA" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Manage roles & deactivation</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Modify role permissions, elevate users, or suspend unauthorized sessions.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>

                    {{-- SECTION B: ABSTRACT & MANUSCRIPT REVIEW --}}
                    <tr @click="secB = !secB" class="bg-surface-container-high/60 cursor-pointer select-none group border-t border-b border-surface-container-highest/60">
                        <td class="py-2.5 px-4" colspan="6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px] text-primary transition-transform duration-200" :class="secB ? 'rotate-0' : '-rotate-90'">expand_more</span>
                                    <span class="font-bold text-primary">{{ __('asic-admin.sec_b_title') }}</span>
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-surface-container-lowest font-mono text-[10px] text-on-surface-variant border border-surface-container-highest/40">4 Sub-features</span>
                                </div>
                                <span class="text-xs text-on-surface-variant group-hover:text-primary">Click to toggle</span>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="secB" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Submit abstract & revisions</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Upload initial proposal, camera-ready PDFs, author biographies, and presentation slides.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secB" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Assign reviewers & track progress</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Dispatch papers to thematic tracks (Crop Science, Agribusiness, Food Tech) and monitor scores.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secB" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Issue LOA & Rejection notices</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Generate signed digital acceptance letters, revision directives, and feedback packets.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secB" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Download blind manuscript packages</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Batch export stripped manuscripts for double-blind review panel evaluations.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>

                    {{-- SECTION C: FINANCIAL & PAYMENT GATEWAY --}}
                    <tr @click="secC = !secC" class="bg-surface-container-high/60 cursor-pointer select-none group border-t border-b border-surface-container-highest/60">
                        <td class="py-2.5 px-4" colspan="6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px] text-primary transition-transform duration-200" :class="secC ? 'rotate-0' : '-rotate-90'">expand_more</span>
                                    <span class="font-bold text-primary">{{ __('asic-admin.sec_c_title') }}</span>
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-surface-container-lowest font-mono text-[10px] text-on-surface-variant border border-surface-container-highest/40">4 Sub-features</span>
                                </div>
                                <span class="text-xs text-on-surface-variant group-hover:text-primary">Click to toggle</span>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="secC" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Upload payment slip & checkout</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Select registration category (Presenter / Non-Presenter / Student) and submit transaction slip.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secC" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Verify bank transfers & settlements</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Match incoming payments against Bank Nagari & BNI virtual account settlement logs.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secC" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Issue official e-Receipt & invoices</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Generate stamped institutional invoices with University VAT identification and QR signature.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secC" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Aggregate conference revenue ledger</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Inspect real-time treasury inflows, early-bird tallies, and sponsorship remittances.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>

                    {{-- SECTION D: SYSTEM & DOCUMENT TEMPLATES --}}
                    <tr @click="secD = !secD" class="bg-surface-container-high/60 cursor-pointer select-none group border-t border-b border-surface-container-highest/60">
                        <td class="py-2.5 px-4" colspan="6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px] text-primary transition-transform duration-200" :class="secD ? 'rotate-0' : '-rotate-90'">expand_more</span>
                                    <span class="font-bold text-primary">{{ __('asic-admin.sec_d_title') }}</span>
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-surface-container-lowest font-mono text-[10px] text-on-surface-variant border border-surface-container-highest/40">3 Sub-features</span>
                                </div>
                                <span class="text-xs text-on-surface-variant group-hover:text-primary">Click to toggle</span>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="secD" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Modify document templates</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Edit HTML/LaTeX layouts for LOA, certificate of attendance, and receipt headers.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-secondary-fixed/50 text-on-secondary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">visibility</span> View only</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-secondary-fixed/50 text-on-secondary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">visibility</span> View only</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secD" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Website settings & deadlines</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Configure submission cutoffs, early-bird thresholds, announcement banners, and venue specs.</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>
                    <tr x-show="secD" class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-on-surface">Proceedings archive & repository</td>
                        <td class="py-3 px-4 text-xs text-on-surface-variant">Manage indexed DOI releases, Scopus metadata schemas, and prior seminar catalogues (2021-2025).</td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs"><span class="material-symbols-outlined text-[14px]">remove</span> No Access</span></td>
                        <td class="py-3 px-4 text-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-fixed/40 text-on-primary-fixed-variant text-xs font-semibold"><span class="material-symbols-outlined text-[14px]">check</span> Granted</span></td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- Table Footer / Audit Note --}}
        <div class="p-4 bg-surface-container-low/60 border-t border-surface-container-highest/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-on-surface-variant text-xs">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-primary">verified_user</span>
                <span>{{ __('asic-admin.security_compliance') }}</span>
            </div>
            <div class="font-mono text-on-surface-variant/80">Last updated: [Date & Time TBA] by Super Admin</div>
        </div>
    </div>

</div>
@endsection
