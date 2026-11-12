@extends('layouts.portal', [
    'role' => 'treasurer',
    'roleName' => 'Treasurer',
    'nav' => $treasurerNav,
    'title' => __('asic-treasurer.tariffs.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    saving: false,
    saveChanges() {
        this.saving = true;
        setTimeout(() => {
            this.saving = false;
            $store.toast.show('Tariff schedules updated and submitted to Senate Finance Registry.', 'success');
        }, 500);
    },
    resetChanges() {
        $store.toast.show('Tariff values restored to last confirmed senate baseline.', 'info');
    }
}" class="flex flex-col w-full">

    {{-- Status & Session Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface-container px-4 sm:px-6 py-3 rounded-xl mb-6 shadow-xs border border-surface-container-high">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-container text-white font-bold shrink-0">
                <span class="material-symbols-outlined text-[16px]">tune</span>
            </span>
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                <span class="text-[11px] uppercase tracking-wider text-on-surface-variant font-bold">Active Financial Cycle:</span>
                <span class="text-xs font-semibold text-primary">ASIC 2027 Universitas Andalas • Draft Revision 2.4</span>
            </div>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-bold">
                <span class="material-symbols-outlined text-[14px]">schedule</span>
                <span>{{ __('asic-treasurer.tariffs.senate_validation') }}</span>
            </span>
            <span class="hidden md:inline text-outline-variant">•</span>
            <span class="hidden md:inline text-on-surface-variant text-[11px]">Last autosaved: Today, 14:32 WIB</span>
        </div>
    </div>

    {{-- Operational Header Block --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <div class="flex flex-col gap-1 max-w-2xl">
            <div class="flex items-center gap-2 mb-0.5">
                <span class="text-[11px] font-bold tracking-widest uppercase text-secondary font-mono">Treasury • Schedule of Tariffs</span>
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                <span class="text-[11px] font-mono text-outline">Ref: ASIC-FIN-TAR-2027</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">{{ __('asic-treasurer.tariffs.title') }}</h1>
            <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                {{ __('asic-treasurer.tariffs.subtitle') }}
            </p>
        </div>

        {{-- Header Action CTAs --}}
        <div class="flex items-center gap-2 shrink-0 self-start md:self-auto">
            <button @click="resetChanges()" type="button" 
                    class="px-4 py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-semibold transition shadow-xs flex items-center gap-1.5 border border-surface-container-high">
                <span class="material-symbols-outlined text-[16px]">history</span>
                <span>{{ __('asic-treasurer.tariffs.btn_reset') }}</span>
            </button>
            <button @click="saveChanges()" type="button" :disabled="saving"
                    class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 disabled:opacity-60">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                <span x-text="saving ? 'Saving...' : '{{ __('asic-treasurer.tariffs.btn_save') }}'"></span>
            </button>
        </div>
    </div>

    {{-- Highlights / Metric Banners --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Card 1: Matrix Coverage --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-high flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-treasurer.tariffs.tier_coverage') }}</span>
                <span class="px-2 py-0.5 rounded-md bg-secondary-container text-on-secondary-container text-[11px] font-bold">{{ __('asic-treasurer.tariffs.tier_combinations') }}</span>
            </div>
            <div>
                <div class="text-lg font-bold text-on-surface mb-0.5">3 Tracks • 2 Modes</div>
                <div class="text-xs text-on-surface-variant">Oral, Poster, Listener across Student, Regular, and Invited categories.</div>
            </div>
        </div>

        {{-- Card 2: Publication Outlets --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-xs border border-surface-container-high flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-treasurer.tariffs.proceedings_outlets') }}</span>
                <span class="px-2 py-0.5 rounded-md bg-secondary-container text-on-secondary-container text-[11px] font-bold">{{ __('asic-treasurer.tariffs.proceedings_tiers') }}</span>
            </div>
            <div>
                <div class="text-lg font-bold text-on-surface mb-0.5">Scopus • SINTA • Q1/Q2</div>
                <div class="text-xs text-on-surface-variant">All processing rates linked directly to institutional invoicing logic.</div>
            </div>
        </div>

        {{-- Card 3: Invoicing Policy --}}
        <div class="bg-primary text-white p-5 rounded-xl shadow-xs flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] uppercase tracking-wider text-tertiary-fixed font-semibold">{{ __('asic-treasurer.tariffs.invoicing_policy') }}</span>
                <span class="material-symbols-outlined text-tertiary-fixed text-[18px]">timer</span>
            </div>
            <div>
                <div class="text-lg font-bold text-white mb-0.5">7 Calendar Days</div>
                <div class="text-xs text-tertiary-fixed-dim">{{ __('asic-treasurer.tariffs.grace_threshold') }}</div>
            </div>
        </div>
    </div>

    {{-- SECTION 1: Seminar Fee Table --}}
    <section class="flex flex-col mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-5 rounded-full bg-primary inline-block"></span>
                <h2 class="text-base font-bold text-on-surface">{{ __('asic-treasurer.tariffs.sec1_title') }}</h2>
                <span class="text-outline-variant">•</span>
                <span class="text-xs text-on-surface-variant">{{ __('asic-treasurer.tariffs.sec1_desc') }}</span>
            </div>
            <div class="flex items-center gap-1 text-[11px] text-on-surface-variant">
                <span class="material-symbols-outlined text-[15px]">lock</span>
                <span>Lock: IDR (Bank Mandiri / BSI), USD (SWIFT wire)</span>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-semibold uppercase tracking-wider h-11 border-b border-surface-container-high">
                            <th class="px-4">{{ __('asic-treasurer.tariffs.col_participant_type') }}</th>
                            <th class="px-4">{{ __('asic-treasurer.tariffs.col_category') }}</th>
                            <th class="px-4">{{ __('asic-treasurer.tariffs.col_period') }}</th>
                            <th class="px-4">{{ __('asic-treasurer.tariffs.col_mode') }}</th>
                            <th class="px-4">{{ __('asic-treasurer.tariffs.col_fee_idr') }}</th>
                            <th class="px-4">{{ __('asic-treasurer.tariffs.col_fee_usd') }}</th>
                            <th class="px-4 text-right">{{ __('asic-treasurer.tariffs.col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high text-on-surface">
                        {{-- Row 1 --}}
                        <tr class="hover:bg-surface-container-low/50 transition">
                            <td class="px-4 py-3 font-semibold text-primary flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                Oral Presenter
                            </td>
                            <td class="px-4">Student (Dom / Intl)</td>
                            <td class="px-4 font-medium">Early Bird</td>
                            <td class="px-4">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-secondary">
                                    <span class="material-symbols-outlined text-[14px]">videocam</span> Online
                                </span>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">IDR</span>
                                    <span>1.250.000</span>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">USD</span>
                                    <span>120</span>
                                </div>
                            </td>
                            <td class="px-4 text-right">
                                <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[10px] font-bold">Confirmed</span>
                            </td>
                        </tr>

                        {{-- Row 2 --}}
                        <tr class="hover:bg-surface-container-low/50 transition">
                            <td class="px-4 py-3 font-semibold text-primary flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                Oral Presenter
                            </td>
                            <td class="px-4">Regular Academic / Researcher</td>
                            <td class="px-4 font-medium">Early Bird</td>
                            <td class="px-4">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary">
                                    <span class="material-symbols-outlined text-[14px]">apartment</span> Onsite Padang
                                </span>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">IDR</span>
                                    <span>1.750.000</span>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">USD</span>
                                    <span>180</span>
                                </div>
                            </td>
                            <td class="px-4 text-right">
                                <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[10px] font-bold">Confirmed</span>
                            </td>
                        </tr>

                        {{-- Row 3 --}}
                        <tr class="hover:bg-surface-container-low/50 transition">
                            <td class="px-4 py-3 font-semibold text-on-surface flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-secondary"></span>
                                Poster Presenter
                            </td>
                            <td class="px-4">General Presenter</td>
                            <td class="px-4 font-medium">Regular Rate</td>
                            <td class="px-4">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary">
                                    <span class="material-symbols-outlined text-[14px]">apartment</span> Onsite Padang
                                </span>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">IDR</span>
                                    <span>1.500.000</span>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">USD</span>
                                    <span>150</span>
                                </div>
                            </td>
                            <td class="px-4 text-right">
                                <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[10px] font-bold">Confirmed</span>
                            </td>
                        </tr>

                        {{-- Row 4 --}}
                        <tr class="hover:bg-surface-container-low/50 transition">
                            <td class="px-4 py-3 font-semibold text-on-surface flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-outline"></span>
                                Listener / Observer
                            </td>
                            <td class="px-4">Public / Student Attendee</td>
                            <td class="px-4 font-medium">General Public</td>
                            <td class="px-4">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-outline">
                                    <span class="material-symbols-outlined text-[14px]">videocam</span> Hybrid Entry
                                </span>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">IDR</span>
                                    <span>500.000</span>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-low font-mono font-bold">
                                    <span class="text-outline font-normal">USD</span>
                                    <span>50</span>
                                </div>
                            </td>
                            <td class="px-4 text-right">
                                <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[10px] font-bold">Confirmed</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- SECTION 2 & 3: Publication Surcharges & Add-ons --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Section 2: Proceeding & Publication --}}
        <section class="flex flex-col">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-5 rounded-full bg-secondary inline-block"></span>
                <h2 class="text-base font-bold text-on-surface">{{ __('asic-treasurer.tariffs.sec2_title') }}</h2>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col gap-3 text-xs">
                <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                    <div class="flex flex-col">
                        <span class="font-bold text-on-surface">Scopus Indexed Proceedings</span>
                        <span class="text-[11px] text-on-surface-variant">AIP / IOP / Atlantis Press Indexing</span>
                    </div>
                    <span class="font-mono font-bold text-primary text-sm">IDR 2.000.000 / USD 150</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                    <div class="flex flex-col">
                        <span class="font-bold text-on-surface">SINTA 2 National Accredited Journal</span>
                        <span class="text-[11px] text-on-surface-variant">Selected top manuscripts fast-track</span>
                    </div>
                    <span class="font-mono font-bold text-primary text-sm">IDR 1.000.000 / USD 75</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                    <div class="flex flex-col">
                        <span class="font-bold text-on-surface">Standard Conference Proceedings (ISBN)</span>
                        <span class="text-[11px] text-on-surface-variant">Full digital proceeding repository with DOI</span>
                    </div>
                    <span class="font-mono font-bold text-emerald-700 text-sm">Free (Included)</span>
                </div>
            </div>
        </section>

        {{-- Section 3: Add-on Components --}}
        <section class="flex flex-col">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-5 rounded-full bg-tertiary-container inline-block"></span>
                <h2 class="text-base font-bold text-on-surface">{{ __('asic-treasurer.tariffs.sec3_title') }}</h2>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col gap-3 text-xs">
                <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                    <div class="flex flex-col">
                        <span class="font-bold text-on-surface">Minangkabau Highland Agro-Tour</span>
                        <span class="text-[11px] text-on-surface-variant">Full day excursion to Harau Valley & organic coffee estate</span>
                    </div>
                    <span class="font-mono font-bold text-primary text-sm">IDR 450.000 / USD 35</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                    <div class="flex flex-col">
                        <span class="font-bold text-on-surface">Additional Gala Dinner Banquet Ticket</span>
                        <span class="text-[11px] text-on-surface-variant">Accompanying person dinner voucher</span>
                    </div>
                    <span class="font-mono font-bold text-primary text-sm">IDR 300.000 / USD 25</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-high">
                    <div class="flex flex-col">
                        <span class="font-bold text-on-surface">Airport Transfer Shuttles (BIM - Campus)</span>
                        <span class="text-[11px] text-on-surface-variant">Minangkabau International Airport direct shuttle</span>
                    </div>
                    <span class="font-mono font-bold text-primary text-sm">IDR 150.000 / USD 12</span>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

