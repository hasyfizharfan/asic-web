@extends('layouts.portal', [
    'role' => 'secretariat',
    'roleName' => 'Secretariat Admin',
    'nav' => $secNav,
    'title' => __('asic-secretariat.reregistration.title') . ' — ASIC 2027'
])

@section('content')
@php
    $scanState = request('scan', 'verified'); // 'verified' or 'error'
@endphp

<div x-data="{
    supervisorModalOpen: false,
    supervisorPin: '',
    overrideReason: 'cash',
    kitIssued: false,
    badgePrinted: false,
    searchId: 'ASIC-2027-ORAL-0104',
    openSupervisor() {
        this.supervisorModalOpen = true;
    },
    closeSupervisor() {
        this.supervisorModalOpen = false;
        this.supervisorPin = '';
    },
    confirmOverride() {
        if (this.supervisorPin.length < 4) {
            $store.toast.show('Please enter a valid supervisor PIN (min 4 digits)', 'error');
            return;
        }
        this.closeSupervisor();
        $store.toast.show('Supervisor Override Authorized. Attendee badge unlocked.', 'success');
        window.location.href = '{{ route('fe.secretariat.reregistration', ['scan' => 'verified']) }}';
    }
}" class="flex flex-col w-full relative">

    {{-- State Switcher Bar for Demo / QA --}}
    <div class="mb-5 p-3 rounded-xl bg-surface-container-low border border-surface-container-high flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px] text-primary">tune</span>
                <span>Demo State Switcher:</span>
            </span>
            <div class="inline-flex rounded-lg bg-surface-container-lowest p-1 shadow-xs border border-surface-container-high">
                <a href="{{ route('fe.secretariat.reregistration', ['scan' => 'verified']) }}" 
                   class="px-3 py-1 rounded text-xs font-semibold transition {{ $scanState === 'verified' ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface' }}">
                    Verified (Normal Check-in)
                </a>
                <a href="{{ route('fe.secretariat.reregistration', ['scan' => 'error']) }}" 
                   class="px-3 py-1 rounded text-xs font-semibold transition {{ $scanState === 'error' ? 'bg-error text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface' }}">
                    Blocked / Error (Unpaid Check-in)
                </a>
            </div>
        </div>
        <div class="text-[11px] text-outline font-mono">
            Param: ?scan={{ $scanState }}
        </div>
    </div>

    {{-- Supervisor PIN Modal --}}
    <div x-show="supervisorModalOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeSupervisor()" 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;">
        
        <div @click.stop 
             class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-2xl border border-surface-container-high flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                <div class="flex items-center gap-2 text-error">
                    <span class="material-symbols-outlined text-[24px]">lock</span>
                    <span class="text-base font-bold text-on-surface">{{ __('asic-secretariat.reregistration.supervisor_modal_title') }}</span>
                </div>
                <button @click="closeSupervisor()" class="text-outline hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <p class="text-xs text-on-surface-variant leading-relaxed">
                {{ __('asic-secretariat.reregistration.supervisor_modal_desc') }}
            </p>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface uppercase">{{ __('asic-secretariat.reregistration.pin_label') }}</label>
                <input type="password" 
                       x-model="supervisorPin"
                       maxlength="6" 
                       placeholder="••••••" 
                       class="bg-surface-container-low px-4 py-3 rounded-lg text-center tracking-widest text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-primary border border-surface-container-high">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface uppercase">{{ __('asic-secretariat.reregistration.justification_label') }}</label>
                <select x-model="overrideReason" class="bg-surface-container-low px-3 py-2 rounded-lg text-xs text-on-surface focus:outline-none border border-surface-container-high">
                    <option value="cash">{{ __('asic-secretariat.reregistration.justification_cash') }}</option>
                    <option value="vip">{{ __('asic-secretariat.reregistration.justification_vip') }}</option>
                    <option value="sponsor">{{ __('asic-secretariat.reregistration.justification_sponsor') }}</option>
                    <option value="other">{{ __('asic-secretariat.reregistration.justification_other') }}</option>
                </select>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-surface-container-high">
                <button @click="closeSupervisor()" class="flex-1 py-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold transition">
                    {{ __('asic-secretariat.reregistration.btn_cancel') }}
                </button>
                <button @click="confirmOverride()" class="flex-1 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-semibold shadow-xs transition">
                    {{ __('asic-secretariat.reregistration.btn_confirm_override') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Top Status & Live Counters --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono text-primary uppercase font-bold tracking-widest">Secretariat Gate Operations</span>
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                <span class="text-xs text-on-surface-variant">Desk #02 (Auditorium Hallway)</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">{{ __('asic-secretariat.reregistration.title') }}</h1>
        </div>

        {{-- Live Counter Metrics --}}
        <div class="flex items-center gap-2 bg-surface-container-lowest p-1.5 rounded-xl shadow-xs border border-surface-container-high self-start md:self-auto">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-surface-container-low rounded-lg">
                <span class="material-symbols-outlined text-[18px] text-primary">how_to_reg</span>
                <div class="flex flex-col">
                    <span class="text-[10px] text-on-surface-variant leading-none">{{ __('asic-secretariat.reregistration.checked_in_counter') }}</span>
                    <span class="text-xs font-bold font-mono text-on-surface">428 / 650</span>
                </div>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 bg-surface-container-low rounded-lg">
                <span class="material-symbols-outlined text-[18px] text-secondary">badge</span>
                <div class="flex flex-col">
                    <span class="text-[10px] text-on-surface-variant leading-none">{{ __('asic-secretariat.reregistration.kits_issued_counter') }}</span>
                    <span class="text-xs font-bold font-mono text-on-surface">412 Units</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 px-2 text-primary">
                <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
                <span class="text-[11px] font-semibold">Live Sync</span>
            </div>
        </div>
    </div>

    {{-- 2-Column Responsive Workspace Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- LEFT COLUMN: Camera Feed & Scanner Controls (5 cols) --}}
        <div class="lg:col-span-5 flex flex-col gap-4">
            {{-- Video Scanner Module --}}
            <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden flex flex-col">
                {{-- Scanner Toolbar --}}
                <div class="bg-[#111c2d] px-4 py-2.5 flex items-center justify-between text-white text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-fixed-dim text-[18px]">videocam</span>
                        <select class="bg-transparent text-white text-xs focus:outline-none cursor-pointer pr-2">
                            <option class="text-on-surface bg-surface-container-lowest" value="cam1">Camera 1: HD USB Desk Cam</option>
                            <option class="text-on-surface bg-surface-container-lowest" value="cam2">Camera 2: Overhead Cam</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-1">
                        <button class="p-1 rounded hover:bg-white/10 text-outline-variant hover:text-white transition" title="Toggle Light">
                            <span class="material-symbols-outlined text-[18px]">flashlight_on</span>
                        </button>
                        <button class="p-1 rounded hover:bg-white/10 text-outline-variant hover:text-white transition" title="Refresh">
                            <span class="material-symbols-outlined text-[18px]">cached</span>
                        </button>
                    </div>
                </div>

                {{-- Camera Viewport Simulation --}}
                <div class="relative bg-[#0b1320] w-full aspect-[4/3] flex items-center justify-center overflow-hidden select-none">
                    {{-- Grid pattern --}}
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#8cd79b_1px,transparent_1px)] [background-size:16px_16px]"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/30 pointer-events-none"></div>

                    {{-- Scanning Reticle Target Box --}}
                    <div class="relative w-48 h-48 sm:w-56 sm:h-56 flex items-center justify-center">
                        <div class="absolute left-0 right-0 h-0.5 bg-primary-fixed-dim shadow-[0_0_12px_#a7f4b6] animate-[bounce_2.4s_ease-in-out_infinite]"></div>
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-primary-fixed rounded-tl-sm"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-primary-fixed rounded-tr-sm"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-primary-fixed rounded-bl-sm"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-primary-fixed rounded-br-sm"></div>
                        
                        <div class="w-10 h-10 rounded-full border border-primary-fixed/30 flex items-center justify-center">
                            <div class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim"></div>
                        </div>

                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/70 backdrop-blur-md px-3 py-1 rounded-full flex items-center gap-1.5 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-primary-fixed-dim animate-pulse"></span>
                            <span class="text-[10px] font-mono text-primary-fixed tracking-wider uppercase font-semibold">{{ __('asic-secretariat.reregistration.align_qr') }}</span>
                        </div>
                    </div>

                    {{-- Bottom Camera Info Overlay --}}
                    <div class="absolute bottom-2.5 left-4 right-4 flex items-center justify-between text-outline-variant font-mono text-[10px]">
                        <span class="text-white/80">FPS: 60.0 • 1080p</span>
                        <span class="text-primary-fixed-dim">Autofocus Locked</span>
                    </div>
                </div>

                {{-- Scanner Footer Action & Status --}}
                <div class="p-3 bg-surface-container-low flex items-center justify-between text-xs border-t border-surface-container-high">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                        <span class="text-on-surface font-semibold">Continuous Decode Ready</span>
                    </div>
                    <span class="text-[11px] text-on-surface-variant">Sensor: Low latency</span>
                </div>
            </div>

            {{-- Manual Reg ID Fallback Panel --}}
            <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">keyboard</span>
                    <label class="text-xs font-bold text-on-surface">{{ __('asic-secretariat.reregistration.manual_lookup') }}</label>
                </div>
                <p class="text-xs text-on-surface-variant mb-3 leading-relaxed">
                    {{ __('asic-secretariat.reregistration.manual_desc') }}
                </p>
                <div class="flex gap-2">
                    <div class="relative flex-grow">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[18px]">search</span>
                        <input type="text" 
                               x-model="searchId"
                               class="w-full pl-9 pr-3 py-2 bg-surface-container-low rounded-lg text-xs text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
                    </div>
                    <button @click="$store.toast.show('Querying participant record for ' + searchId, 'info')" 
                            class="px-4 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition flex items-center gap-1 shrink-0 shadow-xs">
                        <span>{{ __('asic-secretariat.reregistration.query_btn') }}</span>
                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                    </button>
                </div>
            </div>

            {{-- Recent Desk Activity Snippet --}}
            <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high">
                <div class="flex items-center justify-between mb-2 pb-2 border-b border-surface-container-high">
                    <span class="text-xs text-on-surface font-bold">{{ __('asic-secretariat.reregistration.prior_checkins') }}</span>
                    <span class="text-[11px] font-mono text-primary font-bold">Last 3</span>
                </div>
                <div class="flex flex-col gap-1.5 text-xs">
                    <div class="flex items-center justify-between py-1.5 px-2.5 rounded-lg bg-surface-container-low">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[16px]">check_circle</span>
                            <span class="font-medium text-on-surface">Prof. Dr. Ir. Melinda Surya Dewi</span>
                        </div>
                        <span class="font-mono text-[11px] text-on-surface-variant">08:11 WIB</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 px-2.5 rounded-lg bg-surface-container-low">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[16px]">check_circle</span>
                            <span class="font-medium text-on-surface">Dr. Ir. Rudi Hartono, M.P.</span>
                        </div>
                        <span class="font-mono text-[11px] text-on-surface-variant">08:08 WIB</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 px-2.5 rounded-lg bg-surface-container-low">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[16px]">check_circle</span>
                            <span class="font-medium text-on-surface">Siti Rahmawati, S.P., M.Si.</span>
                        </div>
                        <span class="font-mono text-[11px] text-on-surface-variant">08:02 WIB</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Scan Result Card & Participant Dossier (7 cols) --}}
        <div class="lg:col-span-7 flex flex-col gap-4">
            @if($scanState === 'error')
                {{-- BLOCKED / ERROR STATE VIEW --}}
                <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-red-200 overflow-hidden">
                    <div class="bg-error px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[24px]">block</span>
                            </div>
                            <div>
                                <span class="text-xs uppercase font-bold tracking-wider text-white/80 block">Security Alert</span>
                                <h3 class="text-lg font-bold">{{ __('asic-secretariat.reregistration.blocked_title') }}</h3>
                            </div>
                        </div>
                        <span class="font-mono text-xs px-2.5 py-1 rounded bg-black/20 text-white font-bold self-start sm:self-auto">
                            #ASIC-REG-0891
                        </span>
                    </div>

                    <div class="p-6 flex flex-col gap-5">
                        {{-- Delegate Info --}}
                        <div class="flex items-start justify-between border-b border-surface-container-high pb-4">
                            <div>
                                <span class="text-xs text-on-surface-variant block">Delegate Name</span>
                                <h4 class="text-base font-bold text-on-surface">Ir. Budi Prasetyo, M.T.</h4>
                                <span class="text-xs text-on-surface-variant">Institut Teknologi Bandung • Listener Category</span>
                            </div>
                            <span class="text-xs text-error font-bold flex items-center gap-1 bg-red-50 border border-red-200 px-2.5 py-1 rounded-full">
                                <span class="material-symbols-outlined text-[14px]">block</span>
                                BLOCKED AT GATE
                            </span>
                        </div>

                        {{-- Billing & Amenities Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-surface-container-low p-4 rounded-xl flex flex-col gap-2 border border-surface-container-high text-xs">
                                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-secretariat.reregistration.billing_details') }}</span>
                                <div class="flex justify-between items-baseline mt-1">
                                    <span class="text-on-surface-variant">Invoice Reference:</span>
                                    <span class="font-mono font-semibold text-on-surface">INV-2027-0891-TBA</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-on-surface-variant">{{ __('asic-secretariat.reregistration.amount_payable') }}:</span>
                                    <span class="text-base font-bold text-error font-mono">IDR 1,250,000</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-on-surface-variant">{{ __('asic-secretariat.reregistration.payment_mode') }}:</span>
                                    <span class="text-on-surface">Bank Transfer (Bank Mandiri)</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-on-surface-variant">{{ __('asic-secretariat.reregistration.transaction_slip') }}:</span>
                                    <span class="text-[11px] text-error font-semibold flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[13px]">cancel</span>
                                        {{ __('asic-secretariat.reregistration.slip_unverified') }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-surface-container-low p-4 rounded-xl flex flex-col gap-2 border border-surface-container-high text-xs">
                                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-secretariat.reregistration.package_amenities') }}</span>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-on-surface-variant">Delegate Seminar Kit:</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-surface-container-highest text-outline font-medium">{{ __('asic-secretariat.reregistration.kit_withheld') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-on-surface-variant">Printed Name Badge:</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-surface-container-highest text-outline font-medium">{{ __('asic-secretariat.reregistration.badge_hold') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-on-surface-variant">Banquet Meal Voucher:</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-surface-container-highest text-outline font-medium">{{ __('asic-secretariat.reregistration.meal_locked') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-on-surface-variant">Certificate of Attendance:</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-surface-container-highest text-outline font-medium">{{ __('asic-secretariat.reregistration.cert_disabled') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Policy Warning Alert --}}
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3 text-xs">
                            <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">info</span>
                            <div class="flex flex-col">
                                <span class="font-bold text-error">Secretariat Policy Warning</span>
                                <p class="text-on-surface mt-0.5 leading-relaxed">
                                    {{ __('asic-secretariat.reregistration.policy_warning') }}
                                </p>
                            </div>
                        </div>

                        {{-- Actions Flow --}}
                        <div class="flex flex-col gap-2 pt-2 border-t border-surface-container-high">
                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full">
                                <a href="{{ route('fe.treasurer.payments.index') }}" 
                                   class="w-full sm:flex-1 bg-[#F5C518] hover:bg-[#FAD54D] text-[#0F3D24] px-4 py-2.5 rounded-lg text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition">
                                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                                    <span>{{ __('asic-secretariat.reregistration.btn_redirect_finance') }}</span>
                                </a>
                                <button @click="openSupervisor()" 
                                        class="w-full sm:flex-1 bg-surface-container hover:bg-surface-container-high text-on-surface px-4 py-2.5 rounded-lg text-xs font-semibold flex items-center justify-center gap-2 border border-surface-container-high transition">
                                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                                    <span>{{ __('asic-secretariat.reregistration.btn_supervisor_override') }}</span>
                                </button>
                            </div>
                            <a href="{{ route('fe.secretariat.reregistration', ['scan' => 'verified']) }}" 
                               class="w-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface py-2 rounded-lg text-xs font-medium flex items-center justify-center gap-1.5 transition">
                                <span class="material-symbols-outlined text-[16px]">qr_code_scanner</span>
                                <span>{{ __('asic-secretariat.reregistration.btn_dismiss') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                {{-- VERIFIED PARTICIPANT VIEW --}}
                <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden">
                    {{-- Header --}}
                    <div class="bg-primary-container px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[24px]">verified</span>
                            </div>
                            <div>
                                <span class="text-xs uppercase font-bold tracking-wider text-tertiary-fixed block">Clearance Verified</span>
                                <h3 class="text-lg font-bold">{{ __('asic-secretariat.reregistration.confirmed_attendee') }}</h3>
                            </div>
                        </div>
                        <span class="font-mono text-xs px-2.5 py-1 rounded bg-black/20 text-white font-bold self-start sm:self-auto">
                            #ASIC-REG-1042
                        </span>
                    </div>

                    <div class="p-6 flex flex-col gap-5">
                        {{-- Participant Details Card --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-surface-container-high pb-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/asic/avatar.svg') }}" alt="Avatar" class="w-12 h-12 rounded-full border border-surface-container-high">
                                <div>
                                    <h4 class="text-base font-bold text-on-surface">Dr. Farhan Harahap</h4>
                                    <span class="text-xs text-on-surface-variant">Universitas Andalas • Oral Presenter</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-surface-container text-primary font-semibold">Track 2: Climate-smart</span>
                                        <span class="text-[11px] text-outline">Room 2A</span>
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-auto">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                Paid & Cleared
                            </span>
                        </div>

                        {{-- Metadata Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                                <span class="text-[10px] text-on-surface-variant block uppercase">Registration Tier</span>
                                <span class="font-bold text-on-surface mt-0.5 block">Presenter (Dom)</span>
                            </div>
                            <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                                <span class="text-[10px] text-on-surface-variant block uppercase">Payment Status</span>
                                <span class="font-bold text-emerald-700 mt-0.5 block">Settled (QRIS)</span>
                            </div>
                            <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                                <span class="text-[10px] text-on-surface-variant block uppercase">{{ __('asic-secretariat.reregistration.badge_printed') }}</span>
                                <span class="font-bold mt-0.5 block" :class="badgePrinted ? 'text-primary' : 'text-amber-700'" x-text="badgePrinted ? 'Issued' : 'Pending Print'">Pending Print</span>
                            </div>
                            <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                                <span class="text-[10px] text-on-surface-variant block uppercase">{{ __('asic-secretariat.reregistration.kit_issued') }}</span>
                                <span class="font-bold mt-0.5 block" :class="kitIssued ? 'text-primary' : 'text-amber-700'" x-text="kitIssued ? 'Handed Over' : 'Ready'">Ready</span>
                            </div>
                        </div>

                        {{-- Checklist Actions --}}
                        <div class="flex flex-col gap-2 pt-2 border-t border-surface-container-high">
                            <div class="flex flex-col sm:flex-row items-center gap-2">
                                <button @click="badgePrinted = true; $store.toast.show('Printing badge for Dr. Farhan Harahap...', 'info')" 
                                        class="w-full sm:flex-1 py-3 px-4 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold flex items-center justify-center gap-2 border border-surface-container-high transition">
                                    <span class="material-symbols-outlined text-[18px]">print</span>
                                    <span>{{ __('asic-secretariat.reregistration.print_badge') }}</span>
                                </button>
                                <button @click="kitIssued = true; $store.toast.show('Attendance marked & seminar kit issued.', 'success')" 
                                        class="w-full sm:flex-1 py-3 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-semibold flex items-center justify-center gap-2 shadow-xs transition">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    <span>{{ __('asic-secretariat.reregistration.mark_attended') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Terminal Footer Info --}}
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-xs border border-surface-container-high flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs text-on-surface-variant">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
                    <span>Convention Hall, Campus Limau Manis, Universitas Andalas, Padang</span>
                </div>
                <div class="flex items-center gap-2 font-mono text-[11px]">
                    <span>TERMINAL-SEC-04</span>
                    <span>•</span>
                    <span class="text-primary font-semibold">Live DB Sync Active</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

