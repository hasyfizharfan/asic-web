@extends('layouts.portal', [
    'role' => 'treasurer',
    'roleName' => 'Treasurer',
    'nav' => $treasurerNav,
    'title' => __('asic-treasurer.detail.qris_title') . ' — ASIC 2027'
])

@section('content')
@php
    $gateway = request('gateway', 'qris'); // 'qris', 'bank', or 'paypal'
    $isPaypal = ($gateway === 'paypal');
@endphp

<div x-data="{
    zoom: 1,
    rotation: 0,
    rejectModalOpen: false,
    rejectReason: 'mismatch',
    rejectNotes: '',
    verified: false,
    zoomIn() {
        if (this.zoom < 2) this.zoom += 0.2;
    },
    zoomOut() {
        if (this.zoom > 0.6) this.zoom -= 0.2;
    },
    rotate() {
        this.rotation = (this.rotation + 90) % 360;
    },
    resetTransform() {
        this.zoom = 1;
        this.rotation = 0;
    },
    confirmVerification() {
        this.verified = true;
        $store.toast.show('Payment confirmed and reconciled into University ledger. Official receipt generated.', 'success');
        setTimeout(() => {
            window.location.href = '{{ route('fe.treasurer.receipts.preview', ['id' => 'INV-2027-0104']) }}';
        }, 800);
    },
    openRejectModal() {
        this.rejectModalOpen = true;
    },
    closeRejectModal() {
        this.rejectModalOpen = false;
        this.rejectNotes = '';
    },
    submitReject() {
        this.closeRejectModal();
        $store.toast.show('Proof rejected. Notification dispatched to participant for re-upload.', 'warning');
        setTimeout(() => {
            window.location.href = '{{ route('fe.treasurer.payments.index') }}';
        }, 800);
    }
}" class="flex flex-col w-full relative">

    {{-- Rejection Modal --}}
    <div x-show="rejectModalOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeRejectModal()" 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4" 
         style="display: none;">
        
        <div @click.stop 
             class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-2xl border border-surface-container-high flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                <div class="flex items-center gap-2 text-error">
                    <span class="material-symbols-outlined text-[24px]">cancel</span>
                    <span class="text-base font-bold text-on-surface">{{ __('asic-treasurer.detail.reject_modal_title') }}</span>
                </div>
                <button @click="closeRejectModal()" class="text-outline hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="flex flex-col gap-1.5 text-xs">
                <label class="font-bold text-on-surface uppercase">{{ __('asic-treasurer.detail.reject_reason_label') }}</label>
                <select x-model="rejectReason" class="bg-surface-container-low px-3 py-2 rounded-lg text-xs text-on-surface focus:outline-none border border-surface-container-high">
                    <option value="mismatch">{{ __('asic-treasurer.detail.reject_reason_mismatch') }}</option>
                    <option value="unreadable">{{ __('asic-treasurer.detail.reject_reason_unreadable') }}</option>
                    <option value="wrong_acc">{{ __('asic-treasurer.detail.reject_reason_wrong_acc') }}</option>
                    <option value="duplicate">{{ __('asic-treasurer.detail.reject_reason_duplicate') }}</option>
                </select>
            </div>

            <div class="flex flex-col gap-1.5 text-xs">
                <label class="font-bold text-on-surface uppercase">{{ __('asic-treasurer.detail.reject_notes_label') }}</label>
                <textarea rows="3" 
                          x-model="rejectNotes"
                          placeholder="e.g. Please re-upload full uncropped BNI Mobile transaction receipt..." 
                          class="w-full bg-surface-container-low p-3 rounded-lg text-xs text-on-surface outline-none border border-surface-container-high focus:border-error"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-surface-container-high">
                <button @click="closeRejectModal()" class="flex-1 py-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold transition">
                    {{ __('asic-treasurer.detail.btn_cancel') }}
                </button>
                <button @click="submitReject()" class="flex-1 py-2.5 rounded-lg bg-error hover:bg-error/90 text-white text-xs font-semibold shadow-xs transition">
                    {{ __('asic-treasurer.detail.btn_confirm_reject') }}
                </button>
            </div>
        </div>
    </div>

    {{-- State Switcher Bar for Demo / QA --}}
    <div class="mb-5 p-3 rounded-xl bg-surface-container-low border border-surface-container-high flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px] text-primary">tune</span>
                <span>Verification Gateway Mode:</span>
            </span>
            <div class="inline-flex rounded-lg bg-surface-container-lowest p-1 shadow-xs border border-surface-container-high">
                <a href="{{ route('fe.treasurer.payments.show', ['id' => 'INV-2027-0104', 'gateway' => 'qris']) }}" 
                   class="px-3 py-1 rounded text-xs font-semibold transition {{ !$isPaypal ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface' }}">
                    QRIS / Bank Transfer Proof
                </a>
                <a href="{{ route('fe.treasurer.payments.show', ['id' => 'INV-2027-0105', 'gateway' => 'paypal']) }}" 
                   class="px-3 py-1 rounded text-xs font-semibold transition {{ $isPaypal ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface' }}">
                    PayPal Express IPN
                </a>
            </div>
        </div>
        <div class="text-[11px] text-outline font-mono">
            Param: ?gateway={{ $gateway }}
        </div>
    </div>

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col gap-2 mb-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('fe.treasurer.payments.index') }}" class="inline-flex items-center gap-1 text-xs text-primary font-semibold hover:text-primary-container transition group">
                <span class="material-symbols-outlined text-[16px] group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
                <span>{{ __('asic-treasurer.detail.back_to_queue') }}</span>
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                <span>Audit Pipeline Active</span>
            </span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mt-1">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">
                    {{ $isPaypal ? __('asic-treasurer.detail.paypal_title') : __('asic-treasurer.detail.qris_title') }}
                </h1>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    {{ $isPaypal ? 'Read-only gateway clearance log and PayPal IPN sync audit for international conference registrations.' : 'QR Indonesian Standard (QRIS) / Bank Transfer Proof Review · Faculty of Agriculture, Universitas Andalas' }}
                </p>
            </div>

            <div class="flex items-center gap-3 bg-surface-container-lowest px-4 py-2 rounded-xl shadow-xs border border-surface-container-high self-start md:self-auto">
                <div class="flex flex-col text-right">
                    <span class="text-[10px] text-on-surface-variant uppercase font-semibold">{{ __('asic-treasurer.detail.settlement_target') }}</span>
                    <span class="text-sm font-bold font-mono text-primary">{{ $isPaypal ? 'USD 180' : 'Rp 3.750.000' }}</span>
                </div>
                <div class="w-px h-7 bg-surface-container-high"></div>
                <div class="flex flex-col text-left">
                    <span class="text-[10px] text-on-surface-variant uppercase font-semibold">{{ __('asic-treasurer.detail.author_id') }}</span>
                    <span class="text-xs font-mono font-bold text-on-surface">ASIC-AUT-0841</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Two-Column Split Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        @if(!$isPaypal)
            {{-- QRIS / BANK TRANSFER PROOF (6 Cols) --}}
            <div class="lg:col-span-6 flex flex-col gap-4">
                <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col">
                    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">receipt_long</span>
                            <h2 class="text-xs font-bold text-on-surface uppercase tracking-wide">{{ __('asic-treasurer.detail.submitted_proof') }}</h2>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-semibold">
                            {{ __('asic-treasurer.detail.digital_capture') }}
                        </span>
                    </div>

                    {{-- Controls Toolbar --}}
                    <div class="flex items-center justify-between bg-surface-container-low px-3 py-2 rounded-lg my-3 text-xs border border-surface-container-high">
                        <div class="flex items-center gap-1">
                            <button @click="zoomIn()" type="button" class="w-7 h-7 rounded bg-surface-container-lowest hover:bg-surface-container text-on-surface flex items-center justify-center shadow-xs border border-surface-container-high" title="Zoom In">
                                <span class="material-symbols-outlined text-[16px]">zoom_in</span>
                            </button>
                            <button @click="zoomOut()" type="button" class="w-7 h-7 rounded bg-surface-container-lowest hover:bg-surface-container text-on-surface flex items-center justify-center shadow-xs border border-surface-container-high" title="Zoom Out">
                                <span class="material-symbols-outlined text-[16px]">zoom_out</span>
                            </button>
                            <button @click="rotate()" type="button" class="w-7 h-7 rounded bg-surface-container-lowest hover:bg-surface-container text-on-surface flex items-center justify-center shadow-xs border border-surface-container-high" title="Rotate">
                                <span class="material-symbols-outlined text-[16px]">rotate_right</span>
                            </button>
                            <button @click="resetTransform()" type="button" class="px-2 h-7 rounded bg-surface-container-lowest hover:bg-surface-container text-on-surface-variant text-[11px] shadow-xs border border-surface-container-high">
                                Reset
                            </button>
                        </div>
                        <button @click="$store.toast.show('Downloading transfer receipt image...', 'info')" 
                                class="inline-flex items-center gap-1 px-2.5 h-7 rounded bg-primary text-white text-[11px] font-semibold hover:bg-primary/90 transition shadow-xs">
                            <span class="material-symbols-outlined text-[14px]">file_download</span>
                            <span>{{ __('asic-treasurer.detail.btn_download_original') }}</span>
                        </button>
                    </div>

                    {{-- Visual Canvas --}}
                    <div class="relative w-full h-[480px] bg-surface-container rounded-xl overflow-hidden flex items-center justify-center select-none shadow-inner border border-surface-container-high">
                        <div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(#707a6f_1px,transparent_1px)] [background-size:16px_16px]"></div>
                        
                        {{-- Simulated Bank QRIS Slip Voucher --}}
                        <div :style="'transform: scale(' + zoom + ') rotate(' + rotation + 'deg);'" 
                             class="transition-transform duration-200 ease-out origin-center p-4">
                            <div class="w-[300px] bg-white rounded-xl shadow-xl p-4 text-[#111c2d] flex flex-col border border-gray-200 text-xs">
                                <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-orange-600 text-white flex items-center justify-center font-bold text-xs">BNI</div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-xs leading-tight">Bank Negara Indonesia</span>
                                            <span class="text-[9px] text-gray-500 leading-none">Mobile Banking Receipt</span>
                                        </div>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 font-mono text-[9px] font-bold text-primary">QRIS</span>
                                </div>

                                <div class="py-3 flex flex-col items-center justify-center text-center">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center mb-1">
                                        <span class="material-symbols-outlined text-[20px]">verified</span>
                                    </div>
                                    <span class="font-bold text-primary uppercase tracking-wider text-[11px]">BERHASIL / SUCCESSFUL</span>
                                    <span class="text-[10px] text-gray-500 mt-0.5">24 Sep 2026 • 14:38:19 WIB</span>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-2.5 flex flex-col items-center my-1 border border-gray-100">
                                    <span class="text-[9px] text-gray-500 uppercase">Total Pembayaran</span>
                                    <span class="text-base font-bold font-mono text-gray-900 mt-0.5">Rp 3.750.000</span>
                                    <span class="text-[9px] text-gray-400 mt-0.5">Biaya Admin: Rp 0</span>
                                </div>

                                <div class="space-y-1 pt-1 text-[10px] text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Merchant:</span>
                                        <span class="font-semibold text-gray-900">Universitas Andalas - ASIC 2027</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>NMID:</span>
                                        <span class="font-mono">ID1020038192831</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>RRN Ref:</span>
                                        <span class="font-mono font-bold text-primary">000492819201</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Pengirim:</span>
                                        <span class="font-semibold text-gray-900">Dr. Farhan Harahap</span>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-t border-gray-200 flex flex-col items-center gap-0.5">
                                    <span class="font-mono text-[8px] text-gray-400 tracking-widest">ASIC2027-PAY-0104-BNI-QRIS-OK</span>
                                </div>
                            </div>
                        </div>

                        <div class="absolute bottom-2 left-2 bg-surface-container-lowest/80 backdrop-blur-xs px-2 py-0.5 rounded text-[10px] font-mono text-on-surface-variant shadow-xs">
                            Mode: Interactive Pan & Zoom
                        </div>
                    </div>
                </div>
            </div>

            {{-- DECISION & INVOICE BREAKDOWN (6 Cols) --}}
            <div class="lg:col-span-6 flex flex-col gap-4">
                {{-- Decision Action Card --}}
                <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col gap-4">
                    <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">gavel</span>
                            <h3 class="text-xs font-bold text-on-surface uppercase tracking-wide">Verification Decision</h3>
                        </div>
                        <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded bg-primary-fixed text-on-primary-fixed-variant">
                            Live Audit
                        </span>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button @click="confirmVerification()" type="button"
                                class="w-full py-3 px-4 rounded-xl bg-primary hover:bg-primary/90 text-white text-xs font-bold shadow-xs flex items-center justify-center gap-2 transition active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                            <span>{{ __('asic-treasurer.detail.btn_confirm') }}</span>
                        </button>
                        <button @click="openRejectModal()" type="button"
                                class="w-full py-2.5 px-4 rounded-xl bg-red-50 hover:bg-red-100 text-error border border-red-200 text-xs font-semibold flex items-center justify-center gap-2 transition">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            <span>{{ __('asic-treasurer.detail.btn_reject') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Invoice Line Items Breakdown --}}
                <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col gap-3">
                    <div class="flex items-center justify-between border-b border-surface-container-high pb-2">
                        <span class="text-xs font-bold text-on-surface uppercase tracking-wide">{{ __('asic-treasurer.detail.invoice_breakdown') }}</span>
                        <span class="font-mono text-xs text-primary font-bold">#INV-2027-0104</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between py-1.5 border-b border-surface-container-high">
                            <div>
                                <span class="font-semibold text-on-surface block">Conference Seminar Registration Fee</span>
                                <span class="text-[11px] text-on-surface-variant">Early Bird Presenter - Regular Academic Member</span>
                            </div>
                            <span class="font-mono font-bold text-on-surface">Rp 2.750.000</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-surface-container-high">
                            <div>
                                <span class="font-semibold text-on-surface block">Publication Surcharge</span>
                                <span class="text-[11px] text-on-surface-variant">SINTA 2 National Accredited Journal Fast-track</span>
                            </div>
                            <span class="font-mono font-bold text-on-surface">Rp 1.000.000</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="font-bold text-on-surface">{{ __('asic-treasurer.detail.total_amount') }}:</span>
                            <span class="text-base font-bold font-mono text-primary">Rp 3.750.000</span>
                        </div>
                    </div>
                </div>

                {{-- Treasurer Pre-Clearance Checklist --}}
                <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col gap-3">
                    <span class="text-xs font-bold text-on-surface uppercase tracking-wide">{{ __('asic-treasurer.detail.audit_checklist') }}</span>
                    <div class="space-y-2 text-xs">
                        <label class="flex items-center gap-2.5 p-2 rounded-lg bg-surface-container-low cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 accent-primary rounded">
                            <span class="text-on-surface">{{ __('asic-treasurer.detail.check_amount') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2 rounded-lg bg-surface-container-low cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 accent-primary rounded">
                            <span class="text-on-surface">{{ __('asic-treasurer.detail.check_beneficiary') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2 rounded-lg bg-surface-container-low cursor-pointer">
                            <input type="checkbox" checked class="w-4 h-4 accent-primary rounded">
                            <span class="text-on-surface">{{ __('asic-treasurer.detail.check_rrn') }}</span>
                        </label>
                    </div>
                </div>
            </div>
        @else
            {{-- PAYPAL IPN VERIFICATION VIEW (12 Cols grid) --}}
            <div class="lg:col-span-7 flex flex-col gap-4">
                <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col justify-between">
                    <div class="flex items-center justify-between pb-3 border-b border-surface-container-high">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[22px]">payments</span>
                            <h2 class="text-xs font-bold text-on-surface uppercase tracking-wide">{{ __('asic-treasurer.detail.gateway_summary') }}</h2>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                            Verified (Automatic IPN)
                        </span>
                    </div>

                    {{-- Financial Breakdown Bento Box --}}
                    <div class="grid grid-cols-3 gap-3 my-4">
                        <div class="p-4 rounded-xl bg-surface-container-low flex flex-col justify-between border border-surface-container-high">
                            <span class="text-[10px] text-on-surface-variant uppercase tracking-wider font-semibold">{{ __('asic-treasurer.detail.gross_invoiced') }}</span>
                            <div class="mt-2 flex flex-col">
                                <span class="text-xl font-bold font-mono text-on-surface">USD 180.00</span>
                                <span class="text-[10px] text-secondary font-medium">Participant Card</span>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-surface-container-low flex flex-col justify-between border border-surface-container-high">
                            <span class="text-[10px] text-on-surface-variant uppercase tracking-wider font-semibold">{{ __('asic-treasurer.detail.paypal_fee') }}</span>
                            <div class="mt-2 flex flex-col">
                                <span class="text-xl font-bold font-mono text-error">-USD 8.22</span>
                                <span class="text-[10px] text-outline font-medium">4.4% Merchant Tariff</span>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-primary text-white flex flex-col justify-between shadow-xs">
                            <span class="text-[10px] text-tertiary-fixed uppercase tracking-wider font-semibold">{{ __('asic-treasurer.detail.net_settlement') }}</span>
                            <div class="mt-2 flex flex-col">
                                <span class="text-xl font-bold font-mono text-white">USD 171.78</span>
                                <span class="text-[10px] text-tertiary-fixed-dim font-medium">Disbursable to Treasury</span>
                            </div>
                        </div>
                    </div>

                    {{-- Spec Data Rows --}}
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-on-surface-variant uppercase tracking-wide">{{ __('asic-treasurer.detail.trans_id') }}</span>
                                <span class="font-mono font-bold text-on-surface">PAYID-ASIC27-9812-4410</span>
                            </div>
                            <button @click="$store.toast.show('Transaction ID copied to clipboard.', 'info')" 
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded bg-surface-container-lowest hover:bg-surface-container text-on-surface text-xs font-semibold shadow-xs border border-surface-container-high transition">
                                <span class="material-symbols-outlined text-[15px]">content_copy</span>
                                <span>{{ __('asic-treasurer.detail.copy_id') }}</span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                            <span class="text-on-surface-variant">Payer Account:</span>
                            <span class="font-mono font-semibold text-on-surface">kenji.takahashi@kyoto-u.ac.jp</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                            <span class="text-on-surface-variant">Payer Country:</span>
                            <span class="font-semibold text-on-surface">Japan (JP) • SWIFT / Visa Card</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-4">
                <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col gap-3 text-xs">
                    <span class="text-xs font-bold text-on-surface uppercase tracking-wide">Actions & Ledger Sync</span>
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        This transaction was cleared automatically via PayPal IPN webhook on 14 April 2027, 10:42 WIB. The invoice is marked reconciled and official receipt is ready for download.
                    </p>
                    <div class="flex flex-col gap-2 pt-2 border-t border-surface-container-high">
                        <a href="{{ route('fe.treasurer.receipts.preview', ['id' => 'INV-2027-0105']) }}" 
                           class="w-full py-2.5 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-xs transition">
                            <span class="material-symbols-outlined text-[16px]">receipt</span>
                            <span>View Official Receipt</span>
                        </a>
                        <button @click="$store.toast.show('Transaction flagged for special committee review.', 'warning')" 
                                class="w-full py-2.5 px-4 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-semibold text-xs border border-surface-container-high transition">
                            {{ __('asic-treasurer.detail.flag_review') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

