@extends('layouts.portal', [
    'title' => __('asic-admin.templates_title') . ' — ASIC 2027',
    'role' => 'admin',
    'roleName' => 'Super Admin',
    'nav' => $adminNav
])

@section('content')
<div class="flex flex-col gap-6 max-w-[1400px] mx-auto w-full" x-data="{
    activeTab: 'loa',
    chairName: '[Chairperson Name, Title TBA]',
    chairDesig: '[Dean / Committee Chair Title TBA]',
    chairNip: '[NIP TBA]',
    showStamp: true,
    zoomLevel: 100
}">

    {{-- Top Context Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-1.5 text-on-surface-variant font-medium">
            <span>Document Management</span>
            <span class="material-symbols-outlined text-[16px] text-outline">chevron_right</span>
            <span class="text-primary font-semibold">{{ __('asic-admin.template_customizer') }}</span>
        </div>
        <div class="flex items-center gap-2 bg-surface-container-low px-3 py-1 rounded-full border border-surface-container-highest/60">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-container opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
            </span>
            <span class="text-on-surface-variant font-medium">{{ __('asic-admin.auto_rendering_active') }}</span>
            <span class="font-mono text-primary font-semibold bg-surface px-1.5 py-0.5 rounded text-[10px]">v2.4-ASIC</span>
        </div>
    </div>

    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 pb-2 border-b border-surface-container-highest/60">
        <div class="flex flex-col gap-1 max-w-3xl">
            <div class="flex items-center gap-2">
                <span class="text-xs text-primary uppercase font-bold bg-primary-fixed/30 px-2 py-0.5 rounded">
                    OFFICIAL DOCUMENTS & CERTIFICATES • ASIC 2027
                </span>
                <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                <span class="text-xs text-on-surface-variant">Faculty of Agriculture, Universitas Andalas</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-admin.templates_title') }}</h1>
            <p class="text-sm sm:text-base text-on-surface-variant">
                {{ __('asic-admin.templates_subtitle') }}
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button @click="chairName = '[Chairperson Name, Title TBA]'; chairDesig = '[Dean / Committee Chair Title TBA]'; chairNip = '[NIP TBA]'; $store.toast.show('Draft template fields reset.', 'info')" type="button" class="px-4 py-2 rounded-xl text-sm font-semibold text-on-surface bg-surface-container-low hover:bg-surface-container border border-surface-container-highest/60 transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">restore</span>
                <span>{{ __('asic-admin.reset_draft') }}</span>
            </button>
            <button @click="$store.toast.show('Document template configuration successfully saved and published.', 'success')" type="button" class="px-5 py-2 rounded-xl text-sm font-semibold bg-secondary-container text-on-secondary-container hover:brightness-95 shadow-xs transition-all active:scale-95 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">cloud_done</span>
                <span>{{ __('asic-admin.save_publish_template') }}</span>
            </button>
        </div>
    </div>

    {{-- 5 Tab Bar --}}
    <div class="bg-surface-container-low p-1 rounded-xl flex items-center gap-1 overflow-x-auto border border-surface-container-highest/60 text-xs sm:text-sm">
        <button @click="activeTab = 'loa'" :class="activeTab === 'loa' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'" class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg transition-all shrink-0">
            <span class="material-symbols-outlined text-[18px]">verified</span>
            <span>{{ __('asic-admin.tab_loa') }}</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-secondary-container text-on-secondary-container font-bold">READY</span>
        </button>
        <button @click="activeTab = 'rejection'" :class="activeTab === 'rejection' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'" class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg transition-all shrink-0">
            <span class="material-symbols-outlined text-[18px]">cancel_schedule_send</span>
            <span>{{ __('asic-admin.tab_rejection') }}</span>
        </button>
        <button @click="activeTab = 'invoice'" :class="activeTab === 'invoice' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'" class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg transition-all shrink-0">
            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
            <span>{{ __('asic-admin.tab_invoice') }}</span>
        </button>
        <button @click="activeTab = 'receipt'" :class="activeTab === 'receipt' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'" class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg transition-all shrink-0">
            <span class="material-symbols-outlined text-[18px]">payments</span>
            <span>{{ __('asic-admin.tab_receipt') }}</span>
        </button>
        <button @click="activeTab = 'certificate'" :class="activeTab === 'certificate' ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container'" class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg transition-all shrink-0">
            <span class="material-symbols-outlined text-[18px]">workspace_premium</span>
            <span>{{ __('asic-admin.tab_certificate') }}</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-surface-container-highest text-on-surface-variant font-bold">TBA</span>
        </button>
    </div>

    {{-- Signer Rules & Protocol Banner --}}
    <div class="bg-primary/10 rounded-xl p-4 flex items-start gap-3 border border-primary/20">
        <div class="p-1 rounded-lg bg-primary-container text-on-primary shrink-0 mt-0.5">
            <span class="material-symbols-outlined text-[20px]">policy</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 w-full text-xs sm:text-sm">
            <div>
                <span class="font-bold text-primary uppercase tracking-wider block mb-0.5">{{ __('asic-admin.signer_protocol_title') }}</span>
                <p class="text-on-surface leading-snug">
                    {{ __('asic-admin.signer_protocol_desc') }}
                </p>
            </div>
            <div class="flex items-center gap-1 shrink-0 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded bg-surface-container-lowest text-primary font-semibold shadow-xs border border-surface-container-highest/60">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    {{ __('asic-admin.auto_enforced') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Main Workspace Split-View: Left Controls + Right Live Canvas --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT PANEL: Editor & Configuration (5 cols) --}}
        <div class="lg:col-span-5 flex flex-col gap-6">

            {{-- CARD 1: Template File & Header Emblem --}}
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-2 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">upload_file</span>
                        <h2 class="text-base font-bold text-on-surface">{{ __('asic-admin.template_file_emblem') }}</h2>
                    </div>
                    <span class="text-xs bg-surface-container text-on-surface-variant px-2 py-0.5 rounded font-mono">DOCX/HTML Engine</span>
                </div>

                {{-- Drag and Drop Area --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs text-on-surface-variant font-medium">{{ __('asic-admin.master_layout_template') }}</label>
                    <div @click="$store.toast.show('Select official DOCX or HTML master template.', 'info')" class="group relative rounded-xl bg-surface-container-low/60 hover:bg-surface-container-low border-2 border-dashed border-outline-variant/60 transition-all p-5 flex flex-col items-center justify-center text-center cursor-pointer">
                        <div class="w-10 h-10 rounded-full bg-surface-container-lowest text-primary flex items-center justify-center mb-2 shadow-xs group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-[22px]">cloud_upload</span>
                        </div>
                        <span class="text-xs font-semibold text-on-surface">{{ __('asic-admin.drop_docx_template') }}</span>
                        <span class="text-[11px] text-on-surface-variant mt-0.5">(provided by the committee)</span>
                        <span class="text-xs text-primary font-medium mt-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">attach_file</span>
                            Current: ASIC_2027_LOA_MasterTemplate_v2.docx
                        </span>
                    </div>
                </div>

                {{-- Logo Module --}}
                <div class="bg-surface-container-low rounded-xl p-3.5 flex flex-col gap-2 border border-surface-container-highest/40">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-on-surface">{{ __('asic-admin.header_emblem_logo') }}</span>
                        <span class="text-primary-container font-medium">Universitas Andalas</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-14 rounded-lg bg-surface-container-lowest border border-surface-container-highest flex items-center justify-center p-2 shrink-0">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-primary text-[24px]">eco</span>
                                <span class="text-[8px] font-bold text-primary tracking-tighter uppercase font-mono">UNAND</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 w-full">
                            <button @click="$store.toast.show('Select image logo file (PNG/SVG, 400x120px).', 'info')" class="w-full px-3 py-1.5 rounded-lg text-xs font-semibold text-primary bg-primary-fixed/40 hover:bg-primary-fixed/70 transition-colors flex items-center justify-center gap-1.5" type="button">
                                <span class="material-symbols-outlined text-[16px]">add_photo_alternate</span>
                                <span>{{ __('asic-admin.upload_new_logo') }}</span>
                            </button>
                            <span class="text-[10px] text-on-surface-variant">Recommended: Transparent background, 300 DPI high-res.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: Signatory Details & Credentials --}}
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-2 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">badge</span>
                        <h2 class="text-base font-bold text-on-surface">{{ __('asic-admin.signatory_credentials') }}</h2>
                    </div>
                    <span class="text-xs text-on-primary-fixed-variant bg-primary-fixed px-2 py-0.5 rounded font-semibold">Pre-locked</span>
                </div>
                <div class="flex flex-col gap-3">
                    {{-- Signatory Role --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-on-surface-variant font-semibold">{{ __('asic-admin.assigned_signatory_role') }}</label>
                        <div class="relative">
                            <input class="w-full h-10 px-3 rounded-xl bg-surface-container-low text-xs sm:text-sm text-on-surface cursor-not-allowed pr-8 border border-surface-container-highest/60 focus:outline-none" readonly type="text" value="Conference Chairperson">
                            <span class="material-symbols-outlined text-on-surface-variant text-[16px] absolute right-2.5 top-2.5">lock</span>
                        </div>
                        <span class="text-[10px] text-on-surface-variant">Enforced via Institutional Governance Rule</span>
                    </div>
                    {{-- Chairperson Name --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-on-surface font-semibold flex items-center justify-between">
                            <span>{{ __('asic-admin.chairperson_name_degrees') }}</span>
                            <span class="text-secondary text-[11px]">Synced to Preview</span>
                        </label>
                        <input x-model="chairName" class="w-full h-10 px-3 rounded-xl bg-surface-container-lowest text-xs sm:text-sm text-on-surface border border-surface-container-highest shadow-xs focus:outline-none focus:ring-2 focus:ring-primary" placeholder="e.g. Prof. Dr. [Chairperson Name], M.Sc." type="text">
                    </div>
                    {{-- Designation --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-on-surface font-semibold">{{ __('asic-admin.official_designation') }}</label>
                        <input x-model="chairDesig" class="w-full h-10 px-3 rounded-xl bg-surface-container-lowest text-xs sm:text-sm text-on-surface border border-surface-container-highest shadow-xs focus:outline-none focus:ring-2 focus:ring-primary" type="text">
                    </div>
                    {{-- Staff ID (NIP) --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-on-surface font-semibold">{{ __('asic-admin.civil_servant_nip') }}</label>
                        <input x-model="chairNip" class="w-full h-10 px-3 rounded-xl bg-surface-container-lowest font-mono text-xs text-on-surface border border-surface-container-highest shadow-xs focus:outline-none focus:ring-2 focus:ring-primary" type="text">
                    </div>
                </div>
            </div>

            {{-- CARD 3: Digital Signature & Institutional Stamp --}}
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-2 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">draw</span>
                        <h2 class="text-base font-bold text-on-surface">{{ __('asic-admin.digital_signature_stamp') }}</h2>
                    </div>
                    <span class="text-xs text-primary font-semibold">{{ __('asic-admin.cryptographically_bound') }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Signature Upload Box --}}
                    <div class="bg-surface-container-low rounded-xl p-3 flex flex-col items-center justify-between gap-2 text-center border border-surface-container-highest/40">
                        <span class="text-xs font-semibold text-on-surface">{{ __('asic-admin.signature_asset') }}</span>
                        <div class="w-full h-20 bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60 flex flex-col items-center justify-center p-2 shadow-xs">
                            <span class="material-symbols-outlined text-outline text-[20px]">draw</span>
                            <span class="text-[10px] text-on-surface-variant mt-1">[Signature Placeholder TBA]</span>
                        </div>
                        <button @click="$store.toast.show('Select digital signature PNG asset.', 'info')" class="w-full py-1 px-2 rounded-lg text-xs font-semibold text-primary bg-surface-container-lowest hover:bg-surface-container border border-surface-container-highest/60 shadow-xs transition-colors flex items-center justify-center gap-1" type="button">
                            <span class="material-symbols-outlined text-[14px]">cached</span>
                            <span>{{ __('asic-admin.replace_signature') }}</span>
                        </button>
                    </div>

                    {{-- Official Stamp Upload Box --}}
                    <div class="bg-surface-container-low rounded-xl p-3 flex flex-col items-center justify-between gap-2 text-center border border-surface-container-highest/40">
                        <span class="text-xs font-semibold text-on-surface">{{ __('asic-admin.stamp_asset') }}</span>
                        <div class="w-full h-20 bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60 flex flex-col items-center justify-center p-2 shadow-xs">
                            <span class="material-symbols-outlined text-outline text-[20px]">verified</span>
                            <span class="text-[10px] text-on-surface-variant mt-1">[Official Stamp TBA]</span>
                        </div>
                        <button @click="$store.toast.show('Select faculty stamp PNG asset.', 'info')" class="w-full py-1 px-2 rounded-lg text-xs font-semibold text-primary bg-surface-container-lowest hover:bg-surface-container border border-surface-container-highest/60 shadow-xs transition-colors flex items-center justify-center gap-1" type="button">
                            <span class="material-symbols-outlined text-[14px]">cached</span>
                            <span>{{ __('asic-admin.replace_stamp') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Auto-stamp placement checkbox --}}
                <label class="flex items-center gap-3 p-3 bg-surface-container-low rounded-xl cursor-pointer hover:bg-surface-container transition-colors border border-surface-container-highest/40">
                    <input x-model="showStamp" class="w-4 h-4 text-primary rounded focus:ring-primary focus:ring-offset-0 cursor-pointer" type="checkbox">
                    <div class="flex flex-col text-xs">
                        <span class="font-semibold text-on-surface">{{ __('asic-admin.auto_stamp_placement') }}</span>
                        <span class="text-on-surface-variant">{{ __('asic-admin.auto_stamp_placement_desc') }}</span>
                    </div>
                </label>
            </div>

            {{-- CARD 4: Template Variables Guide --}}
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-highest/60 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">code</span>
                        <h2 class="text-base font-bold text-on-surface">{{ __('asic-admin.template_variables_guide') }}</h2>
                    </div>
                    <span class="text-xs text-on-surface-variant">Click to copy</span>
                </div>
                <p class="text-xs text-on-surface-variant">
                    {{ __('asic-admin.template_variables_desc') }}
                </p>
                <div class="flex flex-wrap gap-2 pt-2">
                    <button @click="navigator.clipboard.writeText('@{{participant_name}}'); $store.toast.show('Copied @{{participant_name}} to clipboard', 'info')" class="font-mono text-xs text-primary bg-primary-fixed/30 hover:bg-primary-fixed px-2.5 py-1 rounded-md transition-colors flex items-center gap-1" type="button">
                        <span>@{{participant_name}}</span>
                        <span class="material-symbols-outlined text-[14px]">content_copy</span>
                    </button>
                    <button @click="navigator.clipboard.writeText('@{{abstract_title}}'); $store.toast.show('Copied @{{abstract_title}} to clipboard', 'info')" class="font-mono text-xs text-primary bg-primary-fixed/30 hover:bg-primary-fixed px-2.5 py-1 rounded-md transition-colors flex items-center gap-1" type="button">
                        <span>@{{abstract_title}}</span>
                        <span class="material-symbols-outlined text-[14px]">content_copy</span>
                    </button>
                    <button @click="navigator.clipboard.writeText('@{{submission_id}}'); $store.toast.show('Copied @{{submission_id}} to clipboard', 'info')" class="font-mono text-xs text-primary bg-primary-fixed/30 hover:bg-primary-fixed px-2.5 py-1 rounded-md transition-colors flex items-center gap-1" type="button">
                        <span>@{{submission_id}}</span>
                        <span class="material-symbols-outlined text-[14px]">content_copy</span>
                    </button>
                    <button @click="navigator.clipboard.writeText('@{{conference_date}}'); $store.toast.show('Copied @{{conference_date}} to clipboard', 'info')" class="font-mono text-xs text-primary bg-primary-fixed/30 hover:bg-primary-fixed px-2.5 py-1 rounded-md transition-colors flex items-center gap-1" type="button">
                        <span>@{{conference_date}}</span>
                        <span class="material-symbols-outlined text-[14px]">content_copy</span>
                    </button>
                    <button @click="navigator.clipboard.writeText('@{{track_name}}'); $store.toast.show('Copied @{{track_name}} to clipboard', 'info')" class="font-mono text-xs text-primary bg-primary-fixed/30 hover:bg-primary-fixed px-2.5 py-1 rounded-md transition-colors flex items-center gap-1" type="button">
                        <span>@{{track_name}}</span>
                        <span class="material-symbols-outlined text-[14px]">content_copy</span>
                    </button>
                </div>
            </div>

        </div>

        {{-- RIGHT PANEL: Real-time Live Document Preview Canvas (7 cols) --}}
        <div class="lg:col-span-7 flex flex-col gap-4 lg:sticky lg:top-20">

            {{-- Live Control Header --}}
            <div class="flex items-center justify-between px-4 py-2 bg-surface-container-low rounded-xl border border-surface-container-highest/60 text-xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">visibility</span>
                    <span class="font-semibold text-on-surface">{{ __('asic-admin.live_preview_title') }}</span>
                    <span class="text-[10px] text-on-surface-variant bg-surface px-2 py-0.5 rounded-full border border-surface-container-highest/40">{{ __('asic-admin.a4_standard') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <button @click="zoomLevel = Math.max(75, zoomLevel - 10)" class="p-1 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors" title="Zoom Out" type="button">
                        <span class="material-symbols-outlined text-[18px]">zoom_out</span>
                    </button>
                    <span class="font-mono text-xs text-on-surface font-semibold" x-text="zoomLevel + '%'">100%</span>
                    <button @click="zoomLevel = Math.min(125, zoomLevel + 10)" class="p-1 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors" title="Zoom In" type="button">
                        <span class="material-symbols-outlined text-[18px]">zoom_in</span>
                    </button>
                    <div class="h-4 w-px bg-outline-variant/40 mx-1"></div>
                    <button onclick="window.print()" class="px-2.5 py-1 rounded-lg text-xs font-semibold text-primary hover:bg-primary-fixed/30 transition-colors flex items-center gap-1" type="button">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                        <span>{{ __('asic-admin.test_pdf') }}</span>
                    </button>
                </div>
            </div>

            {{-- Elevated Academic Letter Canvas (A4 Sheet) --}}
            <div class="bg-surface-container-lowest rounded-xl shadow-xl p-6 sm:p-10 border-t-4 border-t-primary min-h-[900px] flex flex-col justify-between text-on-surface relative select-text transition-all border-x border-b border-surface-container-highest/60" :style="'transform-origin: top center; transform: scale(' + (zoomLevel / 100) + ')'">

                {{-- Background Watermark --}}
                <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[380px]">agriculture</span>
                </div>

                <div class="flex flex-col gap-6 relative z-10">
                    {{-- 1. ACADEMIC LETTERHEAD (Kop Surat) --}}
                    <div class="flex items-center gap-4 pb-3 border-b-2 border-primary">
                        <div class="w-16 h-16 shrink-0 flex items-center justify-center bg-surface-container-low rounded-xl p-2 border border-surface-container-highest/60">
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="material-symbols-outlined text-primary text-[32px]">nature_people</span>
                                <span class="text-[8px] font-bold text-primary font-mono uppercase tracking-tighter">UNAND</span>
                            </div>
                        </div>
                        <div class="flex flex-col text-center flex-1">
                            <span class="text-[11px] text-on-surface uppercase tracking-wider font-semibold">
                                KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI
                            </span>
                            <span class="text-sm sm:text-base text-primary font-bold tracking-tight uppercase">
                                UNIVERSITAS ANDALAS • FACULTY OF AGRICULTURE
                            </span>
                            <span class="text-xs sm:text-sm text-on-surface font-semibold tracking-wide">
                                AGRIFOOD SYSTEM INTERNATIONAL CONFERENCE (ASIC 2027)
                            </span>
                            <p class="text-[10px] text-on-surface-variant leading-tight mt-1">
                                Secretariat: Kampus Limau Manis, Padang 25163, West Sumatra, Indonesia | Email: asic2027@agr.unand.ac.id | Web: https://asic.faperta.unand.ac.id
                            </p>
                        </div>
                        <div class="w-14 h-14 shrink-0 hidden sm:flex flex-col items-center justify-center bg-surface-container-low rounded-lg p-1 text-center border border-surface-container-highest/60">
                            <span class="material-symbols-outlined text-on-surface text-[22px]">qr_code_scanner</span>
                            <span class="text-[7px] text-on-surface-variant font-mono">ASIC-VERIFY</span>
                        </div>
                    </div>

                    {{-- 2. REFERENCE NUMBER & DATE --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs text-on-surface">
                        <div class="flex flex-col font-mono text-[11px]">
                            <span><strong>Ref. No:</strong> ASIC-2027/LOA/SCR-0482/IX</span>
                            <span><strong>Attachment:</strong> Conference Schedule & Payment Details</span>
                        </div>
                        <div class="sm:text-right text-xs"><span>Padang, {{ date('F d, Y') }}</span></div>
                    </div>

                    {{-- 3. ADDRESSEE BLOCK --}}
                    <div class="flex flex-col bg-surface-container-low/60 p-4 rounded-xl gap-0.5 border border-surface-container-highest/40">
                        <span class="text-[10px] text-on-surface-variant uppercase tracking-wider font-semibold">Recipient (Participant):</span>
                        <span class="text-sm sm:text-base font-bold text-on-surface">[Participant Name]</span>
                        <span class="text-xs text-on-surface-variant">[Institution / Department TBA]</span>
                        <div class="flex items-center gap-4 mt-1 pt-1.5 border-t border-outline-variant/30 text-[11px] font-mono">
                            <span class="text-primary font-bold">Paper ID: <span class="text-on-surface font-medium">ASIC-2027-ABS-0194</span></span>
                            <span class="text-on-surface-variant">Presentation Category: <strong class="text-on-surface">Oral Presentation</strong></span>
                        </div>
                    </div>

                    {{-- 4. FORMAL LETTER TITLE --}}
                    <div class="text-center py-1">
                        <h3 class="text-base sm:text-lg text-primary font-bold tracking-tight uppercase underline decoration-2 underline-offset-4">
                            LETTER OF ACCEPTANCE (ORAL PRESENTATION)
                        </h3>
                        <span class="text-xs text-on-surface-variant tracking-wider uppercase mt-1 block">
                            Agrifood System International Conference (ASIC 2027)
                        </span>
                    </div>

                    {{-- 5. LETTER BODY TEXT --}}
                    <div class="flex flex-col gap-3 text-xs sm:text-sm text-on-surface leading-relaxed text-justify">
                        <p>Dear <strong class="font-semibold text-primary">[Participant Name]</strong>,</p>
                        <p>
                            On behalf of the Scientific Review Committee of the <strong class="font-semibold">Agrifood System International Conference (ASIC 2027)</strong>, hosted by the Faculty of Agriculture, Universitas Andalas in Padang, West Sumatra, Indonesia, we are pleased to inform you that your abstract entitled:
                        </p>
                        <div class="bg-surface-container-low p-4 rounded-xl border-l-4 border-l-primary text-sm font-semibold text-primary text-center italic my-1 border border-surface-container-highest/40">
                            “[Abstract Title TBA]”
                        </div>
                        <p>
                            has undergone a rigorous double-blind peer-review evaluation by our international scientific panel and has been officially <strong class="text-primary font-bold bg-primary-fixed/40 px-1.5 py-0.5 rounded">ACCEPTED for Oral Presentation</strong> within the technical thematic session: <strong class="font-semibold">Agronomy & Climate-Resilient Cultivation</strong>.
                        </p>
                        <p>
                            Please proceed to settle the required conference registration fee (<span class="font-mono text-xs text-on-surface font-semibold">Invoice No: INV-ASIC27-0482</span>) before the early bird deadline of <strong class="font-semibold">7 days following receipt of this official letter</strong> to guarantee inclusion in the conference proceedings and indexed publication.
                        </p>
                    </div>
                </div>

                {{-- 6. SIGNATORY BLOCK --}}
                <div class="relative z-10 pt-6 flex justify-end">
                    <div class="w-72 flex flex-col items-start gap-1">
                        <span class="text-xs text-on-surface">Sincerely yours,</span>
                        <span class="text-xs font-bold text-primary">Chairperson, ASIC 2027 Organizing Committee</span>

                        {{-- Signature & Stamp Zone --}}
                        <div class="relative w-full h-24 my-1 flex items-center justify-center">
                            {{-- Signature Box --}}
                            <div class="w-full h-18 border border-dashed border-outline-variant/60 rounded-lg flex flex-col items-center justify-center text-center p-2 bg-surface-container-low/40">
                                <span class="material-symbols-outlined text-outline text-[20px]">draw</span>
                                <span class="text-[10px] text-on-surface-variant mt-0.5">[Digital Signature]</span>
                            </div>
                            {{-- Stamp Overlay --}}
                            <div x-show="showStamp" class="absolute -top-2 left-6 w-20 h-20 rounded-full border-2 border-primary/40 bg-primary/5 flex items-center justify-center pointer-events-none rotate-[-12deg]">
                                <span class="text-[9px] font-bold text-primary text-center leading-none uppercase">FAKULTAS<br>PERTANIAN<br>UNAND</span>
                            </div>
                        </div>

                        {{-- Signatory Mirrored Values --}}
                        <div class="flex flex-col border-t border-on-surface/40 pt-1 w-full text-xs">
                            <span class="font-bold text-on-surface" x-text="chairName">[Chairperson Name]</span>
                            <span class="font-mono text-[11px] text-on-surface-variant mt-0.5" x-text="'NIP: ' + chairNip">NIP: [NIP TBA]</span>
                            <span class="text-[11px] text-on-surface-variant leading-tight mt-0.5" x-text="chairDesig">[Dean / Committee Chair]</span>
                        </div>
                    </div>
                </div>

                {{-- Document Footer Code --}}
                <div class="relative z-10 pt-3 border-t border-outline-variant/30 flex items-center justify-between text-[10px] text-on-surface-variant font-mono">
                    <span>Doc ID: ASIC-2027-LOA-AUTO-GEN</span>
                    <span>Digitally Authenticated by ASIC Conference Engine</span>
                    <span>Page 1 of 1</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-on-surface-variant text-xs px-1">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-primary">info</span>
                    <span>{{ __('asic-admin.live_preview_hint') }}</span>
                </div>
                <button @click="$store.toast.show('Loading audit log for LOA template v2.4...', 'info')" class="text-primary font-semibold hover:underline" type="button">
                    View Sample Audit Log
                </button>
            </div>

        </div>

    </div>

</div>
@endsection
