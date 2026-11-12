@extends('layouts.portal', [
    'title' => __('asic-admin.users_title') . ' — ASIC 2027',
    'role' => 'admin',
    'roleName' => 'Super Admin',
    'nav' => $adminNav
])

@section('content')
<div class="flex flex-col gap-6" x-data="{
    showAddModal: {{ request('modal') === 'add' ? 'true' : 'false' }},
    roleFilter: 'all',
    searchQuery: ''
}">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-2 border-b border-surface-container-highest/60">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs text-primary font-semibold tracking-wider uppercase">{{ __('asic-admin.access_control_engine') }}</span>
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-secondary-container"></span>
                <span class="text-xs text-on-surface-variant">ASIC 2027 Portal</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-admin.users_title') }}</h1>
            <p class="text-sm sm:text-base text-on-surface-variant max-w-2xl">
                {{ __('asic-admin.users_subtitle') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <div class="flex items-center gap-1.5 bg-surface-container-low px-3 py-1.5 rounded-xl border border-surface-container-highest/60 text-xs">
                <span class="material-symbols-outlined text-[16px] text-primary">security</span>
                <span class="text-on-surface font-medium">{{ __('asic-admin.super_admin_session') }}</span>
            </div>
            <button @click="showAddModal = true" type="button" class="flex items-center gap-1.5 bg-secondary-container hover:bg-secondary-fixed-dim text-on-secondary-container text-sm font-semibold px-4 py-2 rounded-xl shadow-xs transition-all active:scale-95">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>{{ __('asic-admin.add_staff_account') }}</span>
            </button>
        </div>
    </div>

    {{-- Directory Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Directory --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.total_directory') }}</span>
                <span class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold text-on-surface">1,248</span>
                <span class="text-xs text-primary font-semibold flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-[14px]">arrow_upward</span>+14 today
                </span>
            </div>
        </div>

        {{-- Secretariat & Reviewers --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.secretariat_reviewers') }}</span>
                <span class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">edit_note</span>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold text-on-surface">42</span>
                <span class="text-xs text-secondary font-semibold">8 Pending</span>
            </div>
        </div>

        {{-- Treasury Officers --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.treasury_officers') }}</span>
                <span class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold text-on-surface">6</span>
                <span class="text-xs text-on-surface-variant font-medium">Verified</span>
            </div>
        </div>

        {{-- Admin Escalation --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-admin.admin_escalation') }}</span>
                <span class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold text-on-surface">4</span>
                <span class="text-xs text-primary font-semibold">Full Access</span>
            </div>
        </div>
    </div>

    {{-- Directory Table Card --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-highest/60 overflow-hidden flex flex-col">
        {{-- Search and Role Filter Bar --}}
        <div class="p-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 bg-surface-container-low/40 border-b border-surface-container-highest/60">
            <div class="flex items-center bg-surface-container-lowest rounded-xl shadow-xs px-3 py-1.5 gap-2 flex-1 max-w-md border border-surface-container-highest/60 focus-within:ring-2 focus-within:ring-primary/20">
                <span class="material-symbols-outlined text-[20px] text-outline">search</span>
                <input x-model="searchQuery" class="w-full bg-transparent border-0 outline-none text-sm text-on-surface placeholder:text-outline-variant py-1" placeholder="{{ __('asic-admin.search_users_placeholder') }}" type="text">
            </div>
            <div class="flex items-center overflow-x-auto gap-2 pb-1 lg:pb-0 text-xs">
                <button @click="roleFilter = 'all'" :class="roleFilter === 'all' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'" class="px-3 py-1.5 rounded-xl shrink-0 transition-colors" type="button">
                    All (TBA)
                </button>
                <button @click="roleFilter = 'Participant'" :class="roleFilter === 'Participant' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'" class="px-3 py-1.5 rounded-xl shrink-0 transition-colors" type="button">
                    Participant (TBA)
                </button>
                <button @click="roleFilter = 'Secretariat'" :class="roleFilter === 'Secretariat' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'" class="px-3 py-1.5 rounded-xl shrink-0 transition-colors" type="button">
                    Secretariat (TBA)
                </button>
                <button @click="roleFilter = 'Treasurer'" :class="roleFilter === 'Treasurer' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'" class="px-3 py-1.5 rounded-xl shrink-0 transition-colors" type="button">
                    Treasurer (TBA)
                </button>
                <button @click="roleFilter = 'Admin'" :class="roleFilter === 'Admin' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'" class="px-3 py-1.5 rounded-xl shrink-0 transition-colors" type="button">
                    Admin (TBA)
                </button>
            </div>
        </div>

        {{-- Table Element --}}
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-surface-container text-xs text-on-surface-variant uppercase tracking-wider font-semibold border-b border-surface-container-highest/60">
                        <th class="py-3 px-4">{{ __('asic-admin.col_user_name') }}</th>
                        <th class="py-3 px-4">{{ __('asic-admin.col_email') }}</th>
                        <th class="py-3 px-4">{{ __('asic-admin.col_institution') }}</th>
                        <th class="py-3 px-4">{{ __('asic-admin.col_assigned_role') }}</th>
                        <th class="py-3 px-4">{{ __('asic-admin.col_status') }}</th>
                        <th class="py-3 px-4">{{ __('asic-admin.col_last_active') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('asic-admin.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container/60">
                    {{-- Row 1: Participant --}}
                    <tr x-show="roleFilter === 'all' || roleFilter === 'Participant'" class="hover:bg-surface-container-low transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-container/20 text-primary flex items-center justify-center text-xs font-bold shrink-0">
                                    PN
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-on-surface text-sm">[Participant Name]</span>
                                    <span class="text-xs text-on-surface-variant">ID: ASIC-REG-0104</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-on-surface">user1@institution.tba</td>
                        <td class="py-3.5 px-4 text-on-surface">[Institution]</td>
                        <td class="py-3.5 px-4">
                            <div class="relative inline-block w-36">
                                <select class="w-full appearance-none bg-surface-container-lowest rounded-xl text-xs text-on-surface px-3 py-1.5 pr-8 border border-surface-container-highest focus:outline-none focus:ring-2 focus:ring-primary shadow-xs cursor-pointer">
                                    <option selected value="Participant">Participant</option>
                                    <option value="Secretariat">Secretariat</option>
                                    <option value="Treasurer">Treasurer</option>
                                    <option value="Admin">Admin</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2 top-2 text-[16px] pointer-events-none text-outline">expand_more</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-primary-fixed text-on-primary-fixed font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                Active
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-on-surface-variant">10 mins ago</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Edit" type="button">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="$store.toast.show('Password reset email dispatched to participant.', 'info')" class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Reset Password" type="button">
                                    <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                </button>
                                <button @click="$store.toast.show('User session suspended.', 'error')" class="w-8 h-8 rounded-lg hover:bg-error-container/40 flex items-center justify-center text-error transition-colors" title="Deactivate" type="button">
                                    <span class="material-symbols-outlined text-[18px]">block</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 2: Secretariat --}}
                    <tr x-show="roleFilter === 'all' || roleFilter === 'Secretariat'" class="hover:bg-surface-container-low transition-colors bg-surface-container-lowest/60">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-tertiary-fixed-dim/40 text-tertiary flex items-center justify-center text-xs font-bold shrink-0">
                                    PN
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-on-surface text-sm">[Participant Name]</span>
                                    <span class="text-xs text-on-surface-variant">ID: ASIC-SEC-0021</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-on-surface">staff.sec@unand.ac.id</td>
                        <td class="py-3.5 px-4 text-on-surface">Universitas Andalas</td>
                        <td class="py-3.5 px-4">
                            <div class="relative inline-block w-36">
                                <select class="w-full appearance-none bg-surface-container-lowest rounded-xl text-xs text-on-surface px-3 py-1.5 pr-8 border border-surface-container-highest focus:outline-none focus:ring-2 focus:ring-primary shadow-xs cursor-pointer">
                                    <option value="Participant">Participant</option>
                                    <option selected value="Secretariat">Secretariat</option>
                                    <option value="Treasurer">Treasurer</option>
                                    <option value="Admin">Admin</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2 top-2 text-[16px] pointer-events-none text-outline">expand_more</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-primary-fixed text-on-primary-fixed font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                Active
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-on-surface-variant">Today, 08:42</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Edit" type="button">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="$store.toast.show('Password reset email dispatched.', 'info')" class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Reset Password" type="button">
                                    <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                </button>
                                <button @click="$store.toast.show('User session suspended.', 'error')" class="w-8 h-8 rounded-lg hover:bg-error-container/40 flex items-center justify-center text-error transition-colors" title="Deactivate" type="button">
                                    <span class="material-symbols-outlined text-[18px]">block</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 3: Treasurer --}}
                    <tr x-show="roleFilter === 'all' || roleFilter === 'Treasurer'" class="hover:bg-surface-container-low transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-secondary-container/40 text-on-secondary-fixed-variant flex items-center justify-center text-xs font-bold shrink-0">
                                    PN
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-on-surface text-sm">[Participant Name]</span>
                                    <span class="text-xs text-on-surface-variant">ID: ASIC-TRS-0008</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-on-surface">treasurer.asic@unand.ac.id</td>
                        <td class="py-3.5 px-4 text-on-surface">Universitas Andalas</td>
                        <td class="py-3.5 px-4">
                            <div class="relative inline-block w-36">
                                <select class="w-full appearance-none bg-surface-container-lowest rounded-xl text-xs text-on-surface px-3 py-1.5 pr-8 border border-surface-container-highest focus:outline-none focus:ring-2 focus:ring-primary shadow-xs cursor-pointer">
                                    <option value="Participant">Participant</option>
                                    <option value="Secretariat">Secretariat</option>
                                    <option selected value="Treasurer">Treasurer</option>
                                    <option value="Admin">Admin</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2 top-2 text-[16px] pointer-events-none text-outline">expand_more</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-primary-fixed text-on-primary-fixed font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                Active
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-on-surface-variant">Yesterday</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Edit" type="button">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="$store.toast.show('Password reset email dispatched.', 'info')" class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Reset Password" type="button">
                                    <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                </button>
                                <button @click="$store.toast.show('User session suspended.', 'error')" class="w-8 h-8 rounded-lg hover:bg-error-container/40 flex items-center justify-center text-error transition-colors" title="Deactivate" type="button">
                                    <span class="material-symbols-outlined text-[18px]">block</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 4: Admin --}}
                    <tr x-show="roleFilter === 'all' || roleFilter === 'Admin'" class="hover:bg-surface-container-low transition-colors bg-surface-container-lowest/60">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary text-on-primary flex items-center justify-center text-xs font-bold shrink-0">
                                    PN
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-on-surface text-sm">[Participant Name]</span>
                                    <span class="text-xs text-on-surface-variant">ID: ASIC-ADM-0002</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-on-surface">admin.portal@unand.ac.id</td>
                        <td class="py-3.5 px-4 text-on-surface">Universitas Andalas</td>
                        <td class="py-3.5 px-4">
                            <div class="relative inline-block w-36">
                                <select class="w-full appearance-none bg-surface-container-lowest rounded-xl text-xs text-on-surface px-3 py-1.5 pr-8 border border-surface-container-highest focus:outline-none focus:ring-2 focus:ring-primary shadow-xs cursor-pointer">
                                    <option value="Participant">Participant</option>
                                    <option value="Secretariat">Secretariat</option>
                                    <option value="Treasurer">Treasurer</option>
                                    <option selected value="Admin">Admin</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2 top-2 text-[16px] pointer-events-none text-outline">expand_more</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-primary-fixed text-on-primary-fixed font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                Active
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-on-surface-variant">Just now</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Edit" type="button">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="$store.toast.show('Password reset email dispatched.', 'info')" class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" title="Reset Password" type="button">
                                    <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                </button>
                                <button @click="$store.toast.show('Super Admin role cannot be suspended.', 'warning')" class="w-8 h-8 rounded-lg hover:bg-error-container/40 flex items-center justify-center text-error transition-colors" title="Deactivate" type="button">
                                    <span class="material-symbols-outlined text-[18px]">block</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 5: Invited Reviewer --}}
                    <tr x-show="roleFilter === 'all' || roleFilter === 'Secretariat'" class="hover:bg-surface-container-low transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center text-xs font-bold shrink-0">
                                    PN
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-on-surface text-sm">[Participant Name]</span>
                                    <span class="text-xs text-on-surface-variant">ID: ASIC-INV-9904</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-on-surface">invited.reviewer@tba.org</td>
                        <td class="py-3.5 px-4 text-on-surface">[Institution]</td>
                        <td class="py-3.5 px-4">
                            <div class="relative inline-block w-36">
                                <select class="w-full appearance-none bg-surface-container-lowest rounded-xl text-xs text-on-surface px-3 py-1.5 pr-8 border border-surface-container-highest focus:outline-none focus:ring-2 focus:ring-primary shadow-xs cursor-pointer">
                                    <option value="Participant">Participant</option>
                                    <option selected value="Secretariat">Secretariat</option>
                                    <option value="Treasurer">Treasurer</option>
                                    <option value="Admin">Admin</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2 top-2 text-[16px] pointer-events-none text-outline">expand_more</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-secondary-fixed text-on-secondary-fixed font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                Pending Invitation
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-on-surface-variant italic">Invited 3d ago</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button @click="$store.toast.show('Invitation resent to reviewer.', 'success')" class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-primary transition-colors" title="Resend Invitation" type="button">
                                    <span class="material-symbols-outlined text-[18px]">forward_to_inbox</span>
                                </button>
                                <button @click="$store.toast.show('Invitation revoked.', 'info')" class="w-8 h-8 rounded-lg hover:bg-error-container/40 flex items-center justify-center text-error transition-colors" title="Revoke" type="button">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Table Pagination Footer --}}
        <div class="p-4 bg-surface-container-low/30 border-t border-surface-container-highest/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-on-surface-variant">
            <span>Showing 5 of 1,248 registered directory records</span>
            <div class="flex items-center gap-2">
                <button class="w-8 h-8 rounded-lg bg-surface-container-lowest text-on-surface-variant hover:text-on-surface flex items-center justify-center shadow-xs border border-surface-container-highest/60" type="button">
                    <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                </button>
                <span class="px-2 font-semibold text-on-surface">Page 1 of 250</span>
                <button class="w-8 h-8 rounded-lg bg-surface-container-lowest text-on-surface-variant hover:text-on-surface flex items-center justify-center shadow-xs border border-surface-container-highest/60" type="button">
                    <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Add Staff Modal --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary-fixed-dim/20 backdrop-blur-sm transition-all duration-300">
        <div @click.away="showAddModal = false" class="bg-surface-container-lowest rounded-xl shadow-xl w-full max-w-lg p-6 sm:p-8 relative flex flex-col gap-4 border border-surface-container-highest transform transition-all">
            <div class="flex items-start justify-between">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-secondary-container"></span>
                        <span class="text-xs uppercase tracking-wider font-semibold text-primary">{{ __('asic-admin.modal_committee_onboarding') }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-on-surface tracking-tight">{{ __('asic-admin.modal_add_staff_title') }}</h2>
                    <p class="text-xs sm:text-sm text-on-surface-variant">{{ __('asic-admin.modal_add_staff_desc') }}</p>
                </div>
                <button @click="showAddModal = false" class="w-8 h-8 rounded-full bg-surface-container-low hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors" type="button">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form @submit.prevent="showAddModal = false; $store.toast.show('Staff account invitation dispatched successfully.', 'success')" class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase text-on-surface tracking-wider">{{ __('asic-admin.full_name') }}</label>
                    <input class="h-11 px-4 bg-surface-container-lowest rounded-xl text-sm text-on-surface border border-surface-container-highest shadow-xs focus:outline-none focus:ring-2 focus:ring-primary" placeholder="[Participant Name]" required type="text">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase text-on-surface tracking-wider">{{ __('asic-admin.official_email') }}</label>
                    <input class="h-11 px-4 bg-surface-container-lowest rounded-xl text-sm text-on-surface border border-surface-container-highest shadow-xs focus:outline-none focus:ring-2 focus:ring-primary" placeholder="e.g. name@unand.ac.id" required type="email">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase text-on-surface tracking-wider">{{ __('asic-admin.role_selector') }}</label>
                    <div class="relative">
                        <select class="h-11 w-full appearance-none px-4 bg-surface-container-lowest rounded-xl text-sm text-on-surface border border-surface-container-highest shadow-xs focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer pr-10">
                            <option value="Secretariat">Secretariat</option>
                            <option value="Treasurer">Treasurer</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-3 text-[20px] pointer-events-none text-outline">expand_more</span>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-surface-container-low flex items-start gap-3 border border-surface-container-highest/60">
                    <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">info</span>
                    <div class="flex flex-col text-xs">
                        <span class="font-semibold text-on-surface">{{ __('asic-admin.permissions_notice') }}</span>
                        <span class="text-on-surface-variant mt-0.5">
                            {{ __('asic-admin.permissions_notice_desc') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button @click="showAddModal = false" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-on-surface hover:bg-surface-container transition-colors" type="button">
                        Cancel
                    </button>
                    <button class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-secondary-container hover:bg-secondary-fixed-dim text-on-secondary-container shadow-xs transition-all active:scale-95" type="submit">
                        {{ __('asic-admin.send_invitation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
