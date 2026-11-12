@extends('layouts.portal', [
    'title' => __('asic-admin.settings_title') . ' — ASIC 2027',
    'role' => 'admin',
    'roleName' => 'Super Admin',
    'nav' => $adminNav
])

@section('content')
<div class="flex flex-col gap-6" x-data="{
    waLink: 'https://chat.whatsapp.com/ASIC2027-Secretariat-TBA',
    enableSwitcher: true
}">

    {{-- Top Breadcrumb & Action Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-2 border-b border-surface-container-highest/60">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 text-on-surface-variant text-xs uppercase tracking-wider">
                <span>System Settings</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-primary font-semibold">Website & Conference Configuration</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-admin.settings_title') }}</h1>
            <p class="text-sm sm:text-base text-on-surface-variant max-w-2xl">
                {{ __('asic-admin.settings_subtitle') }}
            </p>
        </div>
        <div class="flex items-center gap-2 self-start md:self-end shrink-0">
            <button @click="$store.toast.show('Settings changes discarded.', 'info')" class="px-4 py-2 rounded-xl text-sm font-semibold text-on-surface bg-surface-container-low hover:bg-surface-container border border-surface-container-highest/60 transition-colors shadow-xs flex items-center gap-1.5" type="button">
                <span class="material-symbols-outlined text-[18px]">undo</span>
                <span>{{ __('asic-admin.discard_changes') }}</span>
            </button>
            <button @click="$store.toast.show('Website settings updated and synchronized across all portals.', 'success')" class="px-5 py-2 rounded-xl text-sm font-semibold text-on-secondary-container bg-secondary-container hover:bg-secondary-fixed-dim transition-colors shadow-xs flex items-center gap-1.5 active:scale-95" type="button">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                <span>{{ __('asic-admin.save_settings') }}</span>
            </button>
        </div>
    </div>

    {{-- Alert / Status Notice Banner --}}
    <div class="p-4 rounded-xl bg-surface-container-lowest shadow-xs border border-surface-container-highest/60 flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-secondary-container/20 text-on-secondary-container flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">info</span>
        </div>
        <div class="flex flex-col gap-1 flex-1 text-xs sm:text-sm">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-on-surface">{{ __('asic-admin.config_status') }}</span>
                <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-semibold">Key Deadlines TBA</span>
                <span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[11px] font-semibold">{{ __('asic-admin.approval_required') }}</span>
            </div>
            <p class="text-on-surface-variant">
                Changes made to public dates, payment details, or communication channels will immediately propagate to the registration frontend once published.
            </p>
        </div>
        <div class="hidden sm:flex items-center gap-1 text-on-surface-variant font-mono text-xs">
            <span>V2.7-CONFIG</span>
        </div>
    </div>

    {{-- Bento Grid Layout: 2 Columns for Large Screens --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: General Info & Deadlines (7 Cols) --}}
        <div class="lg:col-span-7 flex flex-col gap-6">

            {{-- 1. Conference General Information --}}
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">corporate_fare</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-bold text-on-surface">{{ __('asic-admin.sec_general_info') }}</span>
                            <span class="text-xs text-on-surface-variant">{{ __('asic-admin.sec_general_info_desc') }}</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-primary font-semibold">SEC-01</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
                    {{-- Event Name --}}
                    <div class="md:col-span-2 flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.full_conf_name') }}</label>
                        <input class="h-11 px-4 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" type="text" value="Agrifood System International Conference (ASIC 2027)">
                    </div>
                    {{-- Short Name --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.short_acronym') }}</label>
                        <input class="h-11 px-4 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" type="text" value="ASIC 2027">
                    </div>
                    {{-- Conference Dates --}}
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="font-semibold text-on-surface">{{ __('asic-admin.conf_dates') }}</label>
                            <span class="px-2 py-0.2 rounded bg-secondary-container/30 text-on-secondary-container text-[10px] font-semibold">Date TBA</span>
                        </div>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-4 pr-10 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" placeholder="TBA" type="text" value="TBA (Mid 2027)">
                            <span class="material-symbols-outlined absolute right-3 text-on-surface-variant pointer-events-none text-[20px]">calendar_month</span>
                        </div>
                    </div>
                    {{-- Host Institution --}}
                    <div class="md:col-span-2 flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.host_institution') }}</label>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-10 pr-4 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" type="text" value="Faculty of Agriculture, Universitas Andalas, Padang, Indonesia">
                            <span class="material-symbols-outlined absolute left-3 text-primary text-[20px]">school</span>
                        </div>
                    </div>
                    {{-- Venue / Location --}}
                    <div class="md:col-span-2 flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.venue_mode') }}</label>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-10 pr-4 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" type="text" value="TBA - Kampus Limau Manis, Universitas Andalas / Hybrid TBA">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">pin_drop</span>
                        </div>
                    </div>
                    {{-- Central Theme --}}
                    <div class="md:col-span-2 flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.central_theme') }}</label>
                        <textarea class="p-4 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner resize-none text-xs sm:text-sm" rows="2">Sustainable Food Systems, Climate Resilience, and Agricultural Innovation in the Tropics</textarea>
                    </div>
                </div>
            </section>

            {{-- 2. Submission & Payment Deadlines --}}
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">timer</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-bold text-on-surface">{{ __('asic-admin.sec_deadlines') }}</span>
                            <span class="text-xs text-on-surface-variant">{{ __('asic-admin.sec_deadlines_desc') }}</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-primary font-semibold">SEC-02</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Early Bird --}}
                    <div class="flex flex-col gap-1 p-4 bg-surface-container-low rounded-xl border border-surface-container-highest/40">
                        <span class="text-xs text-on-surface-variant uppercase font-semibold">{{ __('asic-admin.early_bird_cutoff') }}</span>
                        <div class="flex items-center justify-between my-1">
                            <span class="text-xl sm:text-2xl font-bold text-on-surface">TBA</span>
                            <span class="material-symbols-outlined text-secondary text-[22px]">event_upcoming</span>
                        </div>
                        <span class="text-[11px] text-on-surface-variant">Discounted registration window</span>
                    </div>
                    {{-- Abstract Submission --}}
                    <div class="flex flex-col gap-1 p-4 bg-surface-container-low rounded-xl border border-surface-container-highest/40">
                        <span class="text-xs text-on-surface-variant uppercase font-semibold">{{ __('asic-admin.abstract_submission_cutoff') }}</span>
                        <div class="flex items-center justify-between my-1">
                            <span class="text-xl sm:text-2xl font-bold text-on-surface">TBA</span>
                            <span class="material-symbols-outlined text-primary text-[22px]">post_add</span>
                        </div>
                        <span class="text-[11px] text-on-surface-variant">Initial abstract intake period</span>
                    </div>
                    {{-- Camera-Ready --}}
                    <div class="flex flex-col gap-1 p-4 bg-surface-container-low rounded-xl border border-surface-container-highest/40">
                        <span class="text-xs text-on-surface-variant uppercase font-semibold">{{ __('asic-admin.camera_ready_cutoff') }}</span>
                        <div class="flex items-center justify-between my-1">
                            <span class="text-xl sm:text-2xl font-bold text-on-surface">TBA</span>
                            <span class="material-symbols-outlined text-on-surface text-[22px]">assignment_turned_in</span>
                        </div>
                        <span class="text-[11px] text-on-surface-variant">Final revised paper inclusion</span>
                    </div>
                </div>

                {{-- Strict Policy Callout: 7 Days Payment --}}
                <div class="p-4 rounded-xl bg-surface-container-high flex flex-col md:flex-row md:items-center justify-between gap-4 border border-surface-container-highest/60">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center font-bold text-sm shrink-0">
                            7d
                        </div>
                        <div class="flex flex-col gap-1 text-xs sm:text-sm">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-on-surface">{{ __('asic-admin.payment_deadline_rule') }}</span>
                                <span class="px-2 py-0.2 rounded bg-secondary text-on-secondary text-[10px] font-bold uppercase tracking-wider">7 Days Mandatory</span>
                            </div>
                            <p class="text-on-surface-variant">
                                {{ __('asic-admin.payment_deadline_rule_desc') }}
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center">
                        <button @click="$store.toast.show('Payment rule set to 7-day cutoff from LOA dispatch date.', 'info')" class="px-4 py-2 bg-surface-container-lowest text-primary text-xs font-semibold rounded-lg shadow-xs hover:bg-surface-container transition-colors border border-surface-container-highest/60" type="button">
                            {{ __('asic-admin.configure_rule') }}
                        </button>
                    </div>
                </div>
            </section>

            {{-- 3. Communication & Secretariat Channels --}}
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">forum</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-bold text-on-surface">{{ __('asic-admin.sec_channels') }}</span>
                            <span class="text-xs text-on-surface-variant">{{ __('asic-admin.sec_channels_desc') }}</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-primary font-semibold">SEC-03</span>
                </div>

                <div class="flex flex-col gap-4 text-xs sm:text-sm">
                    {{-- WhatsApp Support Desk --}}
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="font-semibold text-on-surface flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary-fixed-dim"></span>
                                {{ __('asic-admin.wa_support_desk') }}
                            </label>
                            <span class="text-[11px] text-primary font-medium">Auto-synced to site footer</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1 flex items-center">
                                <input x-model="waLink" class="w-full h-11 pl-10 pr-4 rounded-xl bg-surface-container-low text-on-surface font-mono text-xs border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" readonly type="text">
                                <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">chat</span>
                            </div>
                            <button @click="navigator.clipboard.writeText(waLink); $store.toast.show('WhatsApp link copied to clipboard.', 'info')" class="h-11 px-4 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold flex items-center gap-1.5 transition-colors border border-surface-container-highest/60" title="Copy link" type="button">
                                <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                <span class="hidden sm:inline">Copy</span>
                            </button>
                            <button @click="$store.toast.show('Displaying WhatsApp Community QR code.', 'info')" class="h-11 px-4 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold flex items-center gap-1.5 transition-colors border border-surface-container-highest/60" type="button">
                                <span class="material-symbols-outlined text-[18px]">qr_code_2</span>
                                <span class="hidden sm:inline">QR Preview</span>
                            </button>
                        </div>
                    </div>
                    {{-- Google Group --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-secondary"></span>
                            {{ __('asic-admin.google_group') }}
                        </label>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-10 pr-4 rounded-xl bg-surface-container-low text-on-surface font-mono text-xs border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" type="text" value="https://groups.google.com/a/unand.ac.id/g/asic2027-participants-tba">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">groups</span>
                        </div>
                    </div>
                    {{-- Secretariat Email --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-outline"></span>
                            {{ __('asic-admin.secretariat_email') }}
                        </label>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-10 pr-4 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner" type="email" value="asic2027@agr.unand.ac.id">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px]">mail</span>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        {{-- RIGHT COLUMN: Payment Gateways & Language Defaults (5 Cols) --}}
        <div class="lg:col-span-5 flex flex-col gap-6">

            {{-- 4. Payment Gateway & Merchant Configuration --}}
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">payments</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-bold text-on-surface">{{ __('asic-admin.sec_gateways') }}</span>
                            <span class="text-xs text-on-surface-variant">{{ __('asic-admin.sec_gateways_desc') }}</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-primary font-semibold">SEC-04</span>
                </div>

                {{-- Committee Payment QR Upload --}}
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between text-xs">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.qris_committee_qr') }}</label>
                        <span class="px-2 py-0.5 rounded bg-primary/10 text-primary font-semibold">Live Preview</span>
                    </div>
                    <div class="p-4 bg-surface-container-low rounded-xl flex flex-col items-center justify-center gap-3 border border-surface-container-highest/40">
                        <div class="w-40 h-40 bg-surface-container-lowest p-3 rounded-xl shadow-xs border border-surface-container-highest flex flex-col items-center justify-between">
                            <div class="w-full h-full flex flex-col items-center justify-center bg-surface-container-high/40 rounded-lg p-2 text-center">
                                <span class="material-symbols-outlined text-[64px] text-primary">qr_code_2</span>
                                <span class="text-xs font-bold text-on-surface tracking-tight">QRIS - BANK NAGARI</span>
                                <span class="font-mono text-[9px] text-on-surface-variant leading-tight">NMID: ID102027ASICUNAND</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-center text-center gap-1 text-xs">
                            <div class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-primary text-[16px]">check_circle</span>
                                <span class="font-semibold text-on-surface">Indonesian Standard QRIS / Bank Nagari</span>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-surface-container-highest text-on-surface-variant text-[11px]">
                                Committee payment QR upload (PNG/JPG, min 500x500px)
                            </span>
                        </div>
                        <div class="flex items-center gap-2 w-full">
                            <button @click="$store.toast.show('Select new QRIS graphic (PNG/JPG).', 'info')" class="flex-1 py-2 px-3 bg-surface-container-lowest hover:bg-surface-container text-on-surface rounded-xl text-xs font-semibold shadow-xs border border-surface-container-highest/60 transition-colors flex items-center justify-center gap-1.5" type="button">
                                <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                                <span>{{ __('asic-admin.replace_qr') }}</span>
                            </button>
                            <button @click="$store.toast.show('Downloading QRIS asset...', 'info')" class="py-2 px-3 bg-surface-container-lowest hover:bg-surface-container text-on-surface rounded-xl text-xs shadow-xs border border-surface-container-highest/60 transition-colors flex items-center justify-center" type="button">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Bank Account Info --}}
                <div class="flex flex-col gap-1.5 text-xs sm:text-sm">
                    <label class="font-semibold text-on-surface">{{ __('asic-admin.institutional_accounts') }}</label>
                    <div class="p-4 bg-surface-container-low rounded-xl flex flex-col gap-2 border border-surface-container-highest/40">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-primary text-on-primary flex items-center justify-center font-bold text-[10px]">BN</div>
                                <span class="font-semibold text-on-surface text-xs">Bank Nagari / BNI Syariah Virtual Account</span>
                            </div>
                            <span class="text-xs text-secondary font-semibold">TBA</span>
                        </div>
                        <div class="flex items-center justify-between bg-surface-container-lowest p-2.5 rounded-lg border border-surface-container-highest text-xs">
                            <span class="font-mono text-on-surface">Bank Nagari / BNI TBA - Account No. TBA</span>
                            <span class="text-[11px] text-on-surface-variant">Rectorate VA</span>
                        </div>
                        <span class="text-[11px] text-on-surface-variant">Official escrow account governed by Universitas Andalas Financial Bureau.</span>
                    </div>
                </div>

                {{-- International Gateway (PayPal) --}}
                <div class="flex flex-col gap-1.5 text-xs sm:text-sm">
                    <label class="font-semibold text-on-surface">{{ __('asic-admin.paypal_gateway') }}</label>
                    <div class="p-4 bg-surface-container-low rounded-xl flex flex-col gap-3 border border-surface-container-highest/40">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary text-[20px]">credit_card</span>
                                <span class="font-bold text-on-surface text-xs">PayPal Merchant Gateway</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[10px] font-semibold">
                                Pending Approval
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[11px] text-on-surface-variant">Merchant Identifier</span>
                            <div class="px-3 py-2 rounded bg-surface-container-lowest font-mono text-xs text-on-surface border border-surface-container-highest">
                                PayPal merchant: to be confirmed
                            </div>
                        </div>
                        <p class="text-[11px] text-on-surface-variant">
                            Foreign remittances and credit card processing remain in staging mode until merchant KYC validation by PayPal Business is certified.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 5. Localization & Language Defaults --}}
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-xs border border-surface-container-highest/60 flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-surface-container-highest/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">translate</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-bold text-on-surface">{{ __('asic-admin.sec_localization') }}</span>
                            <span class="text-xs text-on-surface-variant">{{ __('asic-admin.sec_localization_desc') }}</span>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-primary font-semibold">SEC-05</span>
                </div>

                <div class="flex flex-col gap-4 text-xs sm:text-sm">
                    {{-- Master Language --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.master_language') }}</label>
                        <div class="relative flex items-center">
                            <select class="w-full h-11 pl-10 pr-8 rounded-xl bg-surface-container-low text-on-surface appearance-none border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner text-xs sm:text-sm">
                                <option selected>English (EN) - Primary</option>
                                <option>Bahasa Indonesia (ID) - Secondary</option>
                            </select>
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px] pointer-events-none">language</span>
                            <span class="material-symbols-outlined absolute right-3 text-on-surface-variant text-[20px] pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    {{-- Public Portal Default --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-on-surface">{{ __('asic-admin.public_default_locale') }}</label>
                        <div class="relative flex items-center">
                            <select class="w-full h-11 pl-10 pr-8 rounded-xl bg-surface-container-low text-on-surface appearance-none border border-surface-container-highest/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary shadow-inner text-xs sm:text-sm">
                                <option selected>English (EN)</option>
                                <option>Bahasa Indonesia (ID)</option>
                            </select>
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[20px] pointer-events-none">public</span>
                            <span class="material-symbols-outlined absolute right-3 text-on-surface-variant text-[20px] pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    {{-- Dual-Language Switcher Toggle --}}
                    <div class="p-4 bg-surface-container-low rounded-xl flex items-center justify-between border border-surface-container-highest/40">
                        <div class="flex flex-col gap-0.5">
                            <span class="font-semibold text-on-surface text-xs sm:text-sm">{{ __('asic-admin.enable_switcher') }}</span>
                            <span class="text-[11px] text-on-surface-variant">{{ __('asic-admin.enable_switcher_desc') }}</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input x-model="enableSwitcher" class="sr-only peer" type="checkbox">
                            <div class="w-11 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-surface peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-surface-container-lowest after:border-surface-container-low after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>
                </div>
            </section>

        </div>

    </div>

    {{-- Institutional Footer Bar --}}
    <div class="p-4 rounded-xl bg-surface-container-lowest shadow-xs border border-surface-container-highest/60 flex flex-col md:flex-row items-center justify-between gap-3 text-on-surface-variant text-xs">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">verified_user</span>
            <span>Last updated by <strong class="text-on-surface">[Administrator Name] (Super Admin)</strong> on [Date & Time TBA]</span>
        </div>
        <div class="flex items-center gap-4 text-xs font-mono">
            <span>Environment: Production Staging</span>
            <span class="w-1.5 h-1.5 rounded-full bg-outline-variant"></span>
            <span>Version: ASIC-v2.7-Release</span>
        </div>
    </div>

</div>
@endsection
