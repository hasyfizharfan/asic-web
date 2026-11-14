@extends('layouts.portal', [
    'role' => 'treasurer',
    'roleName' => 'Treasurer',
    'nav' => $treasurerNav,
    'title' => __('asic-treasurer.refunds.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    activeTab: 'all',
    searchQuery: '',
    selectedCase: {
        id: 'REF-2027-01',
        status: 'pending',
        participant: 'Dr. Farhan Harahap',
        institution: 'Universitas Andalas',
        track: 'Scopus Proceedings Track',
        manuscript: 'Precision Nitrogen Delivery System in Tropical Rice Cultivation',
        reason: 'Manuscript withdrawn due to overlapping indexing requirements from main research grant.',
        original_amount: 'Rp 2.000.000',
        fee_deduction: '-Rp 400.000 (20%)',
        net_amount: 'Rp 1.600.000',
        bank_name: 'Bank Nagari (Sumbar)',
        acc_no: '2100-0210-98412-1',
        acc_holder: 'Dr. Farhan Harahap',
        date: 'Oct 18, 2026 • 14:22 WIB'
    },
    cases: [
        {
            id: 'REF-2027-01',
            status: 'pending',
            participant: 'Dr. Farhan Harahap',
            institution: 'Universitas Andalas',
            track: 'Scopus Proceedings Track',
            manuscript: 'Precision Nitrogen Delivery System in Tropical Rice Cultivation',
            reason: 'Manuscript withdrawn due to overlapping indexing requirements from main research grant.',
            original_amount: 'Rp 2.000.000',
            fee_deduction: '-Rp 400.000 (20%)',
            net_amount: 'Rp 1.600.000',
            bank_name: 'Bank Nagari (Sumbar)',
            acc_no: '2100-0210-98412-1',
            acc_holder: 'Dr. Farhan Harahap',
            date: 'Oct 18, 2026 • 14:22 WIB'
        },
        {
            id: 'REF-2027-02',
            status: 'approved',
            participant: 'Prof. Siti Nurhaliza',
            institution: 'IPB University',
            track: 'SINTA 2 National Journal',
            manuscript: 'Enzymatic Hydrolysis for Post-harvest Cocoa Shell Waste Valorization',
            reason: 'Target SINTA 2 journal issue was fully booked before editorial cut-off.',
            original_amount: 'Rp 1.000.000',
            fee_deduction: '-Rp 200.000 (20%)',
            net_amount: 'Rp 800.000',
            bank_name: 'Bank Mandiri',
            acc_no: '129-00-9812401-2',
            acc_holder: 'Prof. Siti Nurhaliza',
            date: 'Oct 17, 2026 • 09:10 WIB'
        },
        {
            id: 'REF-2027-03',
            status: 'declined',
            participant: 'Rian Pratama, M.Sc.',
            institution: 'Balai Litbang Pertanian',
            track: 'Base Seminar Attendee Fee',
            manuscript: 'N/A (Attendee Registration)',
            reason: 'Participant cannot attend on-site due to conflicting administrative assignment.',
            original_amount: 'Rp 1.250.000',
            fee_deduction: 'N/A',
            net_amount: 'Rp 0',
            bank_name: 'Bank Syariah Indonesia (BSI)',
            acc_no: '710-2918-401',
            acc_holder: 'Rian Pratama',
            date: 'Oct 16, 2026 • 11:05 WIB'
        }
    ],
    get filteredCases() {
        return this.cases.filter(c => {
            const matchesStatus = (this.activeTab === 'all' || c.status === this.activeTab);
            const matchesSearch = (this.searchQuery === '' ||
                c.id.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                c.participant.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                c.institution.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
            return matchesStatus && matchesSearch;
        });
    },
    selectCase(c) {
        this.selectedCase = c;
    },
    approveRefund() {
        this.selectedCase.status = 'approved';
        $store.toast.show('Disbursement of ' + this.selectedCase.net_amount + ' authorized to ' + this.selectedCase.bank_name, 'success');
    },
    declineRefund() {
        this.selectedCase.status = 'declined';
        $store.toast.show('Refund request #' + this.selectedCase.id + ' declined under Article 4.2 policy.', 'warning');
    }
}" class="flex flex-col w-full gap-6">

    {{-- Top Bar & Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono uppercase tracking-wider text-secondary font-semibold">Fiscal Operations / Case Registry</span>
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                <span class="text-xs text-on-surface-variant font-semibold">FY 2027</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-treasurer.refunds.title') }}</h1>
            <p class="text-xs sm:text-sm text-on-surface-variant mt-0.5">{{ __('asic-treasurer.refunds.subtitle') }}</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 self-start md:self-auto">
            <div class="px-4 py-2 rounded-xl bg-surface-container flex items-center gap-2 shadow-xs border border-surface-container-high text-xs">
                <span class="material-symbols-outlined text-[18px] text-primary">account_balance_wallet</span>
                <span class="text-on-surface-variant uppercase font-medium">{{ __('asic-treasurer.refunds.escrow_pool') }}:</span>
                <span class="text-primary font-bold font-mono">IDR 48.500.000</span>
            </div>
            <button @click="$store.toast.show('Opening treasury audit log...', 'info')" 
                    class="px-4 py-2 rounded-xl bg-surface-container-lowest text-on-surface text-xs font-semibold shadow-xs hover:bg-surface-container border border-surface-container-high transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">history_edu</span>
                <span>{{ __('asic-treasurer.refunds.audit_log') }}</span>
            </button>
        </div>
    </div>

    {{-- Financial Governance Policy Directive Banner --}}
    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 sm:p-5 shadow-xs flex items-start gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-[22px]">gavel</span>
        </div>
        <div class="flex flex-col flex-1 min-w-0 pr-4">
            <div class="flex items-center gap-2">
                <span class="text-xs sm:text-sm font-bold text-amber-900">{{ __('asic-treasurer.refunds.policy_directive') }}</span>
                <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 text-[10px] font-bold uppercase">Mandatory</span>
            </div>
            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                {{ __('asic-treasurer.refunds.policy_text') }}
            </p>
        </div>
    </div>

    {{-- 2-Column Bento Layout (5 cols left, 7 cols right) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- LEFT COLUMN: Request Queue (5 cols) --}}
        <div class="lg:col-span-5 flex flex-col gap-4">
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-xs border border-surface-container-high flex flex-col gap-3">
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-outline text-[18px]">search</span>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="{{ __('asic-treasurer.refunds.search_placeholder') }}" 
                           class="w-full pl-9 pr-3 py-2 text-xs bg-surface-container-low rounded-lg text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1 p-0.5 bg-surface-container-low rounded-lg border border-surface-container-high">
                        <button @click="activeTab = 'all'" 
                                :class="activeTab === 'all' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                                class="px-2.5 py-1 rounded text-xs transition">
                            {{ __('asic-treasurer.refunds.all') }} (3)
                        </button>
                        <button @click="activeTab = 'pending'" 
                                :class="activeTab === 'pending' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                                class="px-2.5 py-1 rounded text-xs transition">
                            {{ __('asic-treasurer.refunds.pending') }} (1)
                        </button>
                        <button @click="activeTab = 'approved'" 
                                :class="activeTab === 'approved' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                                class="px-2.5 py-1 rounded text-xs transition">
                            {{ __('asic-treasurer.refunds.approved') }} (1)
                        </button>
                        <button @click="activeTab = 'declined'" 
                                :class="activeTab === 'declined' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                                class="px-2.5 py-1 rounded text-xs transition">
                            {{ __('asic-treasurer.refunds.declined') }} (1)
                        </button>
                    </div>
                </div>
            </div>

            {{-- Cards List --}}
            <div class="flex flex-col gap-3">
                <template x-for="item in filteredCases" :key="item.id">
                    <div @click="selectCase(item)" 
                         :class="selectedCase.id === item.id ? 'ring-2 ring-primary bg-primary-container/5' : 'bg-surface-container-lowest hover:bg-surface-container-low/40'"
                         class="p-4 rounded-xl shadow-xs border border-surface-container-high cursor-pointer transition flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold font-mono text-xs text-on-surface" x-text="'#' + item.id"></span>
                                    <span class="px-2 py-0.2 rounded-full text-[10px] font-semibold"
                                          :class="{
                                              'bg-amber-100 text-amber-800': item.status === 'pending',
                                              'bg-emerald-100 text-emerald-800': item.status === 'approved',
                                              'bg-red-100 text-red-800': item.status === 'declined'
                                          }"
                                          x-text="item.status.toUpperCase()"></span>
                                </div>
                                <span class="font-bold text-xs text-on-surface truncate mt-1" x-text="item.participant"></span>
                                <span class="text-[11px] text-on-surface-variant truncate" x-text="item.institution"></span>
                            </div>
                            <span class="font-mono font-bold text-xs text-primary shrink-0" x-text="item.net_amount"></span>
                        </div>

                        <div class="mt-2 pt-2 border-t border-surface-container-high flex items-center justify-between text-[11px] text-on-surface-variant">
                            <span class="truncate max-w-[200px]" x-text="item.track"></span>
                            <span class="text-primary font-semibold flex items-center gap-0.5">Details →</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- RIGHT COLUMN: Case Dossier Detail (7 cols) --}}
        <div class="lg:col-span-7 flex flex-col gap-4">
            <div class="bg-surface-container-lowest rounded-xl p-6 shadow-xs border border-surface-container-high flex flex-col gap-5">
                {{-- Dossier Header --}}
                <div class="flex items-start justify-between border-b border-surface-container-high pb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-mono uppercase tracking-wider text-outline font-bold">{{ __('asic-treasurer.refunds.case_dossier') }}</span>
                            <span class="font-mono text-xs font-bold text-primary" x-text="'#' + selectedCase.id"></span>
                        </div>
                        <h2 class="text-lg font-bold text-on-surface" x-text="selectedCase.participant"></h2>
                        <span class="text-xs text-on-surface-variant" x-text="selectedCase.institution"></span>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-bold"
                          :class="{
                              'bg-amber-50 text-amber-800 border border-amber-200': selectedCase.status === 'pending',
                              'bg-emerald-50 text-emerald-800 border border-emerald-200': selectedCase.status === 'approved',
                              'bg-red-50 text-red-800 border border-red-200': selectedCase.status === 'declined'
                          }"
                          x-text="selectedCase.status === 'pending' ? 'Pending Review' : (selectedCase.status === 'approved' ? 'Approved & Settled' : 'Declined')">
                    </span>
                </div>

                {{-- Manuscript & Reason --}}
                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                        <span class="text-[10px] text-on-surface-variant uppercase font-semibold block mb-0.5">Associated Paper Title</span>
                        <span class="font-semibold text-on-surface" x-text="selectedCase.manuscript"></span>
                    </div>

                    <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                        <span class="text-[10px] text-on-surface-variant uppercase font-semibold block mb-0.5">Reason for Reimbursement Request</span>
                        <p class="text-on-surface-variant leading-relaxed" x-text="selectedCase.reason"></p>
                    </div>
                </div>

                {{-- Financial Breakdown --}}
                <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-high flex flex-col gap-2 text-xs">
                    <span class="text-[11px] uppercase tracking-wider text-outline font-bold">Audit Calculation Ledger</span>
                    
                    <div class="flex justify-between items-center py-1 border-b border-surface-container-high">
                        <span class="text-on-surface-variant">Original Surcharge Claimed:</span>
                        <span class="font-mono font-bold text-on-surface" x-text="selectedCase.original_amount"></span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-surface-container-high">
                        <span class="text-on-surface-variant">{{ __('asic-treasurer.refunds.deduction_rule') }}:</span>
                        <span class="font-mono font-bold text-error" x-text="selectedCase.fee_deduction"></span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="font-bold text-on-surface text-sm">{{ __('asic-treasurer.refunds.net_refund') }}:</span>
                        <span class="font-mono font-bold text-primary text-lg" x-text="selectedCase.net_amount"></span>
                    </div>
                </div>

                {{-- Beneficiary Bank Details --}}
                <div class="p-4 rounded-xl bg-surface-container-lowest border border-surface-container-high flex flex-col gap-2 text-xs">
                    <span class="text-[11px] uppercase tracking-wider text-outline font-bold">{{ __('asic-treasurer.refunds.beneficiary_bank') }}</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-1">
                        <div>
                            <span class="text-[10px] text-on-surface-variant block">Bank Name</span>
                            <span class="font-bold text-on-surface" x-text="selectedCase.bank_name"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-on-surface-variant block">Account Number</span>
                            <span class="font-mono font-bold text-on-surface" x-text="selectedCase.acc_no"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-on-surface-variant block">Account Holder</span>
                            <span class="font-bold text-on-surface" x-text="selectedCase.acc_holder"></span>
                        </div>
                    </div>
                </div>

                {{-- Decision Actions --}}
                <template x-if="selectedCase.status === 'pending'">
                    <div class="flex flex-col sm:flex-row items-center gap-2 pt-2 border-t border-surface-container-high">
                        <button @click="approveRefund()" 
                                class="w-full sm:flex-1 py-3 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">verified</span>
                            <span>{{ __('asic-treasurer.refunds.btn_approve') }}</span>
                        </button>
                        <button @click="declineRefund()" 
                                class="w-full sm:w-auto py-3 px-4 rounded-lg bg-red-50 hover:bg-red-100 text-error border border-red-200 font-semibold text-xs transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">block</span>
                            <span>{{ __('asic-treasurer.refunds.btn_decline') }}</span>
                        </button>
                    </div>
                </template>

                <template x-if="selectedCase.status !== 'pending'">
                    <div class="p-3 rounded-lg bg-surface-container text-xs text-on-surface-variant flex items-center gap-2 border border-surface-container-high">
                        <span class="material-symbols-outlined text-[16px] text-primary">lock</span>
                        <span>This case docket is finalized and permanently recorded in the university treasury ledger.</span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

