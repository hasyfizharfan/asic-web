@extends('layouts.portal', [
    'role' => 'treasurer',
    'roleName' => 'Treasurer',
    'nav' => $treasurerNav,
    'title' => __('asic-treasurer.receipt.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    resendEmail() {
        $store.toast.show('Official receipt PDF dispatched to author.contact@institution.ac.id', 'success');
    }
}" class="flex flex-col w-full">

    {{-- Breadcrumbs & Quick Return --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-2 text-xs text-on-surface-variant font-medium">
            <a href="{{ route('fe.treasurer.payments.index') }}" class="inline-flex items-center gap-1 text-primary hover:text-primary-container transition group">
                <span class="material-symbols-outlined text-[16px] group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
                <span>{{ __('asic-treasurer.receipt.btn_return') }}</span>
            </a>
            <span class="text-outline-variant font-mono">/</span>
            <span class="text-on-surface font-semibold">{{ __('asic-treasurer.receipt.breadcrumb') }}</span>
            <span class="text-outline-variant font-mono">/</span>
            <span class="font-mono text-xs px-2 py-0.5 rounded bg-surface-container-highest text-on-surface font-bold">#REC-2027-0104</span>
        </div>
        <div class="flex items-center gap-2 text-xs text-outline font-mono">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Ledger Status: <strong class="text-on-surface">Reconciled & Locked</strong></span>
        </div>
    </div>

    {{-- Header Banner & Action Bar --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8 bg-surface-container-lowest p-6 rounded-xl shadow-xs border border-surface-container-high">
        <div class="flex flex-col gap-1 max-w-xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-mono text-[10px] uppercase tracking-wide font-bold">
                    Financial Proof Voucher
                </span>
                <span class="text-outline-variant">•</span>
                <span class="font-mono text-xs text-outline">Ref ID: TXN-Q27-90812</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">{{ __('asic-treasurer.receipt.title') }}</h1>
            <p class="text-xs text-on-surface-variant">{{ __('asic-treasurer.receipt.subtitle') }}</p>
        </div>

        {{-- Actions Toolbar --}}
        <div class="flex flex-wrap items-center gap-2 shrink-0 self-start lg:self-auto">
            <button @click="window.print()" type="button" 
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-bold text-xs shadow-xs transition active:scale-95">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>{{ __('asic-treasurer.receipt.btn_print') }}</span>
            </button>
            <button @click="resendEmail()" type="button" 
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-semibold text-xs border border-surface-container-high transition shadow-xs">
                <span class="material-symbols-outlined text-[16px]">forward_to_inbox</span>
                <span>{{ __('asic-treasurer.receipt.btn_email') }}</span>
            </button>
            <a href="{{ route('fe.treasurer.payments.index') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface-variant text-xs font-medium transition">
                <span class="material-symbols-outlined text-[16px]">list_alt</span>
                <span>Queue</span>
            </a>
        </div>
    </div>

    {{-- Central Receipt Canvas Workspace --}}
    <div class="relative w-full flex justify-center py-2 pb-12">
        {{-- Receipt Sheet Container --}}
        <div class="relative w-full max-w-[840px] bg-white rounded-2xl shadow-xl p-8 sm:p-12 overflow-hidden border border-gray-200 text-gray-900">
            {{-- Background Watermark Emblem --}}
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-[0.03]">
                <img src="{{ asset('images/asic/logo.svg') }}" alt="Watermark" class="w-96 h-96 object-contain">
            </div>

            {{-- Inner Header Frame --}}
            <div class="p-6 bg-surface-container-low/60 rounded-xl mb-6 border border-surface-container-high relative">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    {{-- Logo & Seal Brand --}}
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/asic/logo.svg') }}" alt="Logo" class="h-14 w-14 object-contain shrink-0">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-primary tracking-widest uppercase font-bold">UNIVERSITAS ANDALAS • FAKULTAS PERTANIAN</span>
                            <span class="text-sm sm:text-base font-bold text-gray-900 leading-tight">Agrifood System International Conference (ASIC 2027)</span>
                            <span class="text-[11px] text-gray-500 mt-0.5">Kampus Limau Manis, Padang, Sumatera Barat 25163, Indonesia</span>
                        </div>
                    </div>

                    {{-- Document Code & Badge --}}
                    <div class="flex flex-col sm:items-end text-left sm:text-right">
                        <span class="px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wider self-start sm:self-auto">
                            Official Clearance
                        </span>
                        <span class="text-sm font-bold font-mono text-primary mt-1">#REC-2027-0104</span>
                        <span class="text-[10px] text-gray-400 font-mono">UNAND/FAPERTA/ASIC/FIN/2027</span>
                    </div>
                </div>
            </div>

            {{-- Receipt Title Banner --}}
            <div class="text-center pb-4 mb-6 border-b border-gray-200">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 uppercase tracking-tight">
                    {{ __('asic-treasurer.receipt.doc_header') }}
                </h2>
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6 mt-2 text-xs text-gray-600">
                    <div class="flex items-center gap-1 font-mono">
                        <span class="text-gray-400">{{ __('asic-treasurer.receipt.receipt_no') }}:</span>
                        <span class="font-bold text-gray-900">REC-2027-0104</span>
                    </div>
                    <span class="text-gray-300">•</span>
                    <div class="flex items-center gap-1">
                        <span class="text-gray-400">{{ __('asic-treasurer.receipt.settlement_date') }}:</span>
                        <span class="font-semibold text-gray-900">14 May 2027, 10:42 WIB</span>
                    </div>
                    <span class="text-gray-300">•</span>
                    <div class="flex items-center gap-1 font-mono">
                        <span class="text-gray-400">{{ __('asic-treasurer.receipt.invoice_ref') }}:</span>
                        <span class="font-bold text-gray-900">INV-2027-0104</span>
                    </div>
                </div>
            </div>

            {{-- Delegate Metadata Card --}}
            <div class="bg-gray-50 p-5 rounded-xl mb-6 border border-gray-100 text-xs">
                <div class="flex justify-between items-center mb-3 border-b border-gray-200 pb-2">
                    <span class="text-[10px] font-bold text-primary uppercase tracking-wider">{{ __('asic-treasurer.receipt.bill_to') }}</span>
                    <span class="text-[10px] text-gray-500 font-mono">Paper ID: #ABS-2027-0104</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-baseline gap-2">
                            <span class="text-gray-400 w-28">{{ __('asic-treasurer.receipt.participant_name') }}:</span>
                            <span class="font-bold text-gray-900">Dr. Farhan Harahap</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-gray-400 w-28">{{ __('asic-treasurer.receipt.affiliation') }}:</span>
                            <span class="text-gray-700">Universitas Andalas</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-baseline gap-2">
                            <span class="text-gray-400 w-28">{{ __('asic-treasurer.receipt.city_country') }}:</span>
                            <span class="text-gray-700">Padang, Indonesia</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-gray-400 w-28">{{ __('asic-treasurer.receipt.registration_tier') }}:</span>
                            <span class="font-bold text-primary">Oral Presenter (Onsite Padang)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Itemized Ledger Table --}}
            <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 text-xs">
                <div class="grid grid-cols-12 bg-gray-100 px-4 py-2.5 text-gray-700 font-bold uppercase tracking-wider text-[10px]">
                    <div class="col-span-1">{{ __('asic-treasurer.receipt.item_no') }}</div>
                    <div class="col-span-7">{{ __('asic-treasurer.receipt.item_desc') }}</div>
                    <div class="col-span-2 text-center">{{ __('asic-treasurer.receipt.item_curr') }}</div>
                    <div class="col-span-2 text-right">{{ __('asic-treasurer.receipt.item_subtotal') }}</div>
                </div>
                <div class="divide-y divide-gray-100 bg-white">
                    {{-- Line 1 --}}
                    <div class="grid grid-cols-12 px-4 py-3.5 items-center">
                        <div class="col-span-1 font-mono text-gray-400">01</div>
                        <div class="col-span-7 flex flex-col">
                            <span class="font-bold text-gray-900 text-xs">Conference Seminar Registration Fee</span>
                            <span class="text-[11px] text-gray-500">Early Bird Presenter - Regular Academic Member. Covers conference kit, 3-day access, and banquet.</span>
                        </div>
                        <div class="col-span-2 text-center font-mono text-gray-500">IDR</div>
                        <div class="col-span-2 text-right font-mono font-bold text-gray-900">2.750.000</div>
                    </div>
                    {{-- Line 2 --}}
                    <div class="grid grid-cols-12 px-4 py-3.5 items-center">
                        <div class="col-span-1 font-mono text-gray-400">02</div>
                        <div class="col-span-7 flex flex-col">
                            <span class="font-bold text-gray-900 text-xs">Publication Surcharge</span>
                            <span class="text-[11px] text-gray-500">SINTA 2 National Accredited Journal Fast-track</span>
                        </div>
                        <div class="col-span-2 text-center font-mono text-gray-500">IDR</div>
                        <div class="col-span-2 text-right font-mono font-bold text-gray-900">1.000.000</div>
                    </div>
                </div>
                {{-- Total --}}
                <div class="grid grid-cols-12 px-4 py-3 bg-gray-50 border-t border-gray-200 items-center">
                    <div class="col-span-8 font-bold text-gray-900 uppercase text-xs">{{ __('asic-treasurer.receipt.total_settled') }}</div>
                    <div class="col-span-4 text-right font-mono font-bold text-primary text-base">IDR 3.750.000</div>
                </div>
            </div>

            {{-- Signatory & Stamp Block --}}
            <div class="pt-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex flex-col text-xs text-gray-500">
                    <span class="font-mono text-[10px]">Verification: SHA256:7b9f-0104-e28a-asic2027</span>
                    <span class="text-[10px] mt-0.5">Payment Gateway: QRIS Settlement Confirmed (BNI)</span>
                </div>
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full border-2 border-dashed border-primary/30 flex items-center justify-center text-primary mb-1">
                        <span class="material-symbols-outlined text-[28px]">verified</span>
                    </div>
                    <span class="text-xs font-bold text-gray-900">{{ __('asic-treasurer.receipt.auth_signatory') }}</span>
                    <span class="text-[10px] text-gray-400">Universitas Andalas, Padang</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

