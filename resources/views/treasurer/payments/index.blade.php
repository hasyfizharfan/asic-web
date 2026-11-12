@extends('layouts.portal', [
    'role' => 'treasurer',
    'roleName' => 'Treasurer',
    'nav' => $treasurerNav,
    'title' => __('asic-treasurer.queue.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    activeMethod: 'all',
    activeStatus: 'all',
    searchQuery: '',
    items: [
        {
            id: 'INV-2027-0104',
            timestamp: 'Today, 14:38 WIB',
            name: 'Dr. Farhan Harahap',
            institution: 'Universitas Andalas',
            scope: 'Oral Presenter (Onsite)',
            method: 'qris',
            method_label: 'QRIS (BNI)',
            amount: 'Rp 3.750.000',
            status: 'waiting',
            status_label: 'Waiting Verification'
        },
        {
            id: 'INV-2027-0105',
            timestamp: 'Today, 13:20 WIB',
            name: 'Prof. Kenji Takahashi',
            institution: 'Kyoto University, Japan',
            scope: 'Oral Presenter (International)',
            method: 'paypal',
            method_label: 'PayPal (Auto IPN)',
            amount: 'USD 180',
            status: 'verified',
            status_label: 'Verified'
        },
        {
            id: 'INV-2027-0103',
            timestamp: 'Yesterday, 16:45 WIB',
            name: 'Prof. Siti Nurhaliza',
            institution: 'IPB University',
            scope: 'Poster Presenter + Proceeding',
            method: 'bank',
            method_label: 'Bank Transfer (Mandiri)',
            amount: 'Rp 3.500.000',
            status: 'waiting',
            status_label: 'Waiting Verification'
        },
        {
            id: 'INV-2027-0098',
            timestamp: 'Oct 18, 11:10 WIB',
            name: 'Rian Pratama, M.Sc.',
            institution: 'Balai Litbang Pertanian',
            scope: 'Listener (Hybrid)',
            method: 'bank',
            method_label: 'Bank Transfer (BSI)',
            amount: 'Rp 500.000',
            status: 'rejected',
            status_label: 'Rejected'
        }
    ],
    get filteredItems() {
        return this.items.filter(item => {
            const matchesMethod = (this.activeMethod === 'all' || item.method === this.activeMethod);
            const matchesStatus = (this.activeStatus === 'all' || item.status === this.activeStatus);
            const matchesSearch = (this.searchQuery === '' || 
                item.id.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                item.institution.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
            return matchesMethod && matchesStatus && matchesSearch;
        });
    },
    batchVerifyPayPal() {
        $store.toast.show('14 automated PayPal IPN transaction batches reconciled into ledger.', 'success');
    }
}" class="flex flex-col w-full gap-6">

    {{-- Top Bar & Queue Meta Controls --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="flex flex-col gap-1 max-w-2xl">
            <div class="flex items-center gap-2 text-outline font-mono text-[11px] uppercase tracking-wider">
                <span>Treasury Management</span>
                <span class="text-outline-variant font-bold">•</span>
                <span>Remittance Auditing</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-treasurer.queue.title') }}</h1>
            <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                {{ __('asic-treasurer.queue.subtitle') }}
            </p>
        </div>

        {{-- Quick Tool Group --}}
        <div class="flex items-center gap-2 shrink-0 self-start md:self-auto">
            <button @click="$store.toast.show('Payment queue synchronized with bank and PayPal webhooks.', 'info')" 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-container-lowest text-on-surface hover:bg-surface-container shadow-xs border border-surface-container-high text-xs font-semibold transition active:scale-95">
                <span class="material-symbols-outlined text-[16px] text-secondary">sync</span>
                <span>{{ __('asic-treasurer.queue.btn_refresh') }}</span>
            </button>
            <button @click="$store.toast.show('Exporting payments ledger (CSV)...', 'info')" 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-container-lowest text-on-surface hover:bg-surface-container shadow-xs border border-surface-container-high text-xs font-semibold transition active:scale-95">
                <span class="material-symbols-outlined text-[16px] text-secondary">file_download</span>
                <span>{{ __('asic-treasurer.queue.btn_export') }}</span>
            </button>
            <button @click="$store.toast.show('Manual remittance reconcile ledger opened.', 'info')" 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-secondary text-white text-xs font-bold shadow-xs hover:bg-secondary/90 transition active:scale-95">
                <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>
                <span>{{ __('asic-treasurer.queue.btn_reconcile') }}</span>
            </button>
        </div>
    </div>

    {{-- Metric Statistics Bento Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Stat 1: Total Queue --}}
        <div class="p-5 rounded-xl bg-surface-container-lowest shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">{{ __('asic-treasurer.queue.stat_total') }}</span>
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">inbox</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold font-mono text-on-surface">24</span>
                <span class="text-[11px] text-outline font-medium">Txns</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-on-surface-variant">
                <span class="text-[11px] text-outline">Active ledger</span>
                <span class="text-[11px] text-secondary font-bold">100% indexed</span>
            </div>
        </div>

        {{-- Stat 2: Waiting Verification --}}
        <div class="p-5 rounded-xl bg-surface-container-lowest shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider text-amber-700 font-bold">{{ __('asic-treasurer.queue.stat_waiting') }}</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">hourglass_top</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold font-mono text-amber-700">9</span>
                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Requires Audit</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-[11px] text-outline">QR / Wire slip</span>
                <span class="text-[11px] text-amber-700 font-bold">High Priority</span>
            </div>
        </div>

        {{-- Stat 3: Verified --}}
        <div class="p-5 rounded-xl bg-surface-container-lowest shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider text-primary font-bold">{{ __('asic-treasurer.queue.stat_verified') }}</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">task_alt</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold font-mono text-primary">14</span>
                <span class="text-[11px] text-outline font-medium">Settled</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-[11px] text-outline">Cleared</span>
                <span class="text-[11px] text-primary font-bold">58.3% Cleared</span>
            </div>
        </div>

        {{-- Stat 4: Overdue --}}
        <div class="p-5 rounded-xl bg-surface-container-lowest shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider text-error font-bold">{{ __('asic-treasurer.queue.stat_overdue') }}</span>
                <div class="w-8 h-8 rounded-lg bg-red-50 text-error flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">warning</span>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-bold font-mono text-error">3</span>
                <span class="px-2 py-0.5 rounded-full bg-red-100 text-error text-[10px] font-bold">>7 Days Grace</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-[11px] text-outline">Auto-cancel flag</span>
                <span class="text-[11px] text-error font-bold">Active</span>
            </div>
        </div>
    </div>

    {{-- Integrated Filtering & Operations Console --}}
    <div class="bg-surface-container-lowest p-4 rounded-xl shadow-xs border border-surface-container-high flex flex-col gap-3">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            {{-- Search Bar --}}
            <div class="relative flex-1 max-w-lg">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-outline">search</span>
                <input type="text" 
                       x-model="searchQuery"
                       placeholder="{{ __('asic-treasurer.queue.search_placeholder') }}" 
                       class="w-full h-10 pl-9 pr-4 rounded-lg bg-surface-container-low text-xs text-on-surface placeholder:text-outline focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
            </div>

            {{-- Method Switcher Buttons --}}
            <div class="flex items-center gap-1 p-1 rounded-lg bg-surface-container-low border border-surface-container-high text-xs">
                <button @click="activeMethod = 'all'" 
                        :class="activeMethod === 'all' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                        class="px-3 py-1.5 rounded-md transition">
                    {{ __('asic-treasurer.queue.all_methods') }}
                </button>
                <button @click="activeMethod = 'qris'" 
                        :class="activeMethod === 'qris' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                        class="px-3 py-1.5 rounded-md transition">
                    {{ __('asic-treasurer.queue.qr_code') }}
                </button>
                <button @click="activeMethod = 'paypal'" 
                        :class="activeMethod === 'paypal' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                        class="px-3 py-1.5 rounded-md transition">
                    {{ __('asic-treasurer.queue.paypal') }}
                </button>
                <button @click="activeMethod = 'bank'" 
                        :class="activeMethod === 'bank' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                        class="px-3 py-1.5 rounded-md transition">
                    {{ __('asic-treasurer.queue.bank_transfer') }}
                </button>
            </div>
        </div>

        {{-- Status Tabs Strip & Batch Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-surface-container-high text-xs">
            <div class="flex items-center gap-2 overflow-x-auto">
                <span class="text-[11px] text-outline uppercase font-semibold">Audit:</span>
                <button @click="activeStatus = 'all'" 
                        :class="activeStatus === 'all' ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                        class="px-3 py-1 rounded-full transition shadow-xs">
                    All Status (24)
                </button>
                <button @click="activeStatus = 'waiting'" 
                        :class="activeStatus === 'waiting' ? 'bg-amber-600 text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                        class="px-3 py-1 rounded-full transition">
                    Waiting Verification (9)
                </button>
                <button @click="activeStatus = 'verified'" 
                        :class="activeStatus === 'verified' ? 'bg-emerald-700 text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                        class="px-3 py-1 rounded-full transition">
                    Verified (14)
                </button>
                <button @click="activeStatus = 'rejected'" 
                        :class="activeStatus === 'rejected' ? 'bg-error text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                        class="px-3 py-1 rounded-full transition">
                    Rejected (1)
                </button>
            </div>

            <button @click="batchVerifyPayPal()" 
                    class="px-3 py-1 rounded-lg bg-surface-container hover:bg-surface-container-high text-primary font-semibold text-xs border border-surface-container-high transition self-start sm:self-auto">
                {{ __('asic-treasurer.queue.batch_paypal') }}
            </button>
        </div>
    </div>

    {{-- Primary Queue Table Panel --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-semibold uppercase tracking-wider h-11 border-b border-surface-container-high">
                        <th class="px-4 w-[160px]">{{ __('asic-treasurer.queue.col_invoice') }}</th>
                        <th class="px-4 min-w-[240px]">{{ __('asic-treasurer.queue.col_participant') }}</th>
                        <th class="px-4 w-[180px]">{{ __('asic-treasurer.queue.col_method') }}</th>
                        <th class="px-4 w-[140px]">{{ __('asic-treasurer.queue.col_amount') }}</th>
                        <th class="px-4 w-[140px] text-center">{{ __('asic-treasurer.queue.col_status') }}</th>
                        <th class="px-4 w-[130px] text-right">{{ __('asic-treasurer.queue.col_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high text-on-surface">
                    <template x-for="item in filteredItems" :key="item.id">
                        <tr class="h-16 hover:bg-surface-container-low/50 transition">
                            {{-- Invoice & Timestamp --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-primary" x-text="'#' + item.id"></span>
                                    <span class="text-[10px] text-outline font-mono" x-text="item.timestamp"></span>
                                </div>
                            </td>

                            {{-- Participant & Scope --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-bold text-on-surface" x-text="item.name"></span>
                                    <span class="text-[11px] text-on-surface-variant" x-text="item.institution"></span>
                                    <span class="text-[10px] text-secondary font-medium" x-text="item.scope"></span>
                                </div>
                            </td>

                            {{-- Method --}}
                            <td class="px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container-low font-medium text-[11px]">
                                    <span class="material-symbols-outlined text-[15px] text-secondary"
                                          x-text="item.method === 'paypal' ? 'credit_card' : (item.method === 'qris' ? 'qr_code_2' : 'account_balance')"></span>
                                    <span x-text="item.method_label"></span>
                                </span>
                            </td>

                            {{-- Amount --}}
                            <td class="px-4 font-mono font-bold text-on-surface text-sm" x-text="item.amount"></td>

                            {{-- Status Badge --}}
                            <td class="px-4 text-center">
                                <template x-if="item.status === 'waiting'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Waiting
                                    </span>
                                </template>
                                <template x-if="item.status === 'verified'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="material-symbols-outlined text-[13px]">check</span>
                                        Verified
                                    </span>
                                </template>
                                <template x-if="item.status === 'rejected'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-800 border border-red-200">
                                        <span class="material-symbols-outlined text-[13px]">close</span>
                                        Rejected
                                    </span>
                                </template>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 text-right">
                                <template x-if="item.status === 'verified'">
                                    <a :href="'{{ url('treasurer/receipts/preview') }}?id=' + item.id" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold transition border border-surface-container-high">
                                        <span class="material-symbols-outlined text-[15px]">description</span>
                                        <span>{{ __('asic-treasurer.queue.btn_view_receipt') }}</span>
                                    </a>
                                </template>
                                <template x-if="item.status !== 'verified'">
                                    <a :href="'{{ url('treasurer/payments') }}/' + item.id + '?gateway=' + item.method" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-semibold shadow-xs transition">
                                        <span>{{ __('asic-treasurer.queue.btn_verify') }}</span>
                                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                                    </a>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

