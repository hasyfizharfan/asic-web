@extends('layouts.portal', [
    'role' => 'secretariat',
    'roleName' => 'Secretariat Admin',
    'nav' => $secNav,
    'title' => __('asic-secretariat.recap.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    activeView: 'all',
    triggerExport() {
        $store.toast.show('Generating ASIC_2027_Recap_Master.xlsx — Financial breakdown & author ledger included.', 'info');
    }
}" class="flex flex-col w-full">

    {{-- Top Operational Banner & Action Center --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded-full font-mono text-[10px] uppercase font-bold bg-secondary-container text-on-secondary-container">Secretariat Desk • Phase IV</span>
                <span class="text-on-surface-variant text-xs">Live System Sync: Padang (UTC+7)</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">{{ __('asic-secretariat.recap.title') }}</h1>
            <p class="text-xs text-on-surface-variant mt-0.5">{{ __('asic-secretariat.recap.subtitle') }}</p>
        </div>

        {{-- Action Group --}}
        <div class="flex items-center gap-2.5 shrink-0 self-start lg:self-auto">
            <div class="flex items-center bg-surface-container rounded-lg p-1 border border-surface-container-high">
                <button @click="activeView = 'all'" 
                        :class="activeView === 'all' ? 'bg-surface-container-lowest text-primary shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface'"
                        class="px-3 py-1.5 rounded-md text-xs transition">
                    {{ __('asic-secretariat.recap.tab_all_tracks') }}
                </button>
                <button @click="activeView = 'day1'" 
                        :class="activeView === 'day1' ? 'bg-surface-container-lowest text-primary shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface'"
                        class="px-3 py-1.5 rounded-md text-xs transition">
                    {{ __('asic-secretariat.recap.tab_day1') }}
                </button>
                <button @click="activeView = 'day2'" 
                        :class="activeView === 'day2' ? 'bg-surface-container-lowest text-primary shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface'"
                        class="px-3 py-1.5 rounded-md text-xs transition">
                    {{ __('asic-secretariat.recap.tab_day2') }}
                </button>
            </div>

            <button @click="triggerExport()" 
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold bg-[#F5C518] text-[#0F3D24] hover:bg-[#FAD54D] active:scale-95 shadow-xs transition">
                <span class="material-symbols-outlined text-[18px]">table_chart</span>
                <span>{{ __('asic-secretariat.recap.export_excel') }}</span>
            </button>
        </div>
    </div>

    {{-- Stat Cards Row (4 Column Layout) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        {{-- 1. Registrants --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">Delegates & Audience</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-bold font-mono text-on-surface">482</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-semibold">+12% vs 400 target</span>
                    </div>
                    <div class="text-xs font-semibold text-primary mt-1">{{ __('asic-secretariat.recap.total_registrants') }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">group</span>
                </div>
            </div>
            <div class="mt-4 pt-3 bg-surface-container-low/60 -mx-5 -mb-5 px-5 pb-4 rounded-b-xl flex items-center justify-between text-xs text-on-surface-variant border-t border-surface-container-high">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <span>182 {{ __('asic-secretariat.recap.presenters') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>300 {{ __('asic-secretariat.recap.listeners') }}</span>
                </div>
            </div>
        </div>

        {{-- 2. Abstracts Submitted --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">Scientific Submissions</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-bold font-mono text-on-surface">342</span>
                        <span class="text-xs text-on-surface-variant">{{ __('asic-secretariat.recap.manuscripts') }}</span>
                    </div>
                    <div class="text-xs font-semibold text-secondary mt-1">{{ __('asic-secretariat.recap.abstracts_submitted') }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[22px]">article</span>
                </div>
            </div>
            {{-- Segmented Funnel Progress Line --}}
            <div class="mt-4">
                <div class="w-full h-2 rounded-full bg-surface-container flex overflow-hidden mb-2">
                    <div class="h-full bg-primary" style="width: 53.2%" title="182 Accepted"></div>
                    <div class="h-full bg-blue-500" style="width: 25.1%" title="86 Under Review"></div>
                    <div class="h-full bg-amber-500" style="width: 14.0%" title="48 Desk Check"></div>
                    <div class="h-full bg-error" style="width: 7.7%" title="26 Rejected"></div>
                </div>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-[11px]">
                    <span class="text-primary font-semibold">182 Accepted</span>
                    <span class="text-blue-700 font-semibold">86 In Review</span>
                    <span class="text-amber-800">48 Desk Check</span>
                    <span class="text-error font-medium">26 Rejected</span>
                </div>
            </div>
        </div>

        {{-- 3. Payment Status --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">Conference Treasury</span>
                    <div class="mt-1">
                        <span class="text-lg font-bold font-mono text-on-surface">IDR TBA / USD TBA</span>
                    </div>
                    <div class="text-xs font-semibold text-primary mt-1">{{ __('asic-secretariat.recap.treasury_title') }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-4 pt-3 bg-surface-container-low/60 -mx-5 -mb-5 px-5 pb-4 rounded-b-xl flex flex-col gap-1 text-[11px] border-t border-surface-container-high">
                <div class="flex justify-between items-center">
                    <span class="text-primary font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        {{ __('asic-secretariat.recap.settled', ['pct' => 88]) }}
                    </span>
                    <span class="text-amber-700 font-medium">{{ __('asic-secretariat.recap.pending_verification', ['count' => 42]) }}</span>
                </div>
                <div class="flex justify-between items-center text-outline">
                    <span>Virtual Acc & Wire</span>
                    <span class="text-error font-semibold">18 Overdue</span>
                </div>
            </div>
        </div>

        {{-- 4. On-site Check-in Rate --}}
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col justify-between group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">Convention Headcount</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-bold font-mono text-primary">78.4%</span>
                        <span class="text-xs text-on-surface-variant font-mono">378 Checked</span>
                    </div>
                    <div class="text-xs font-semibold text-secondary mt-1">{{ __('asic-secretariat.recap.checkin_rate') }}</div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[22px]">qr_code_2</span>
                </div>
            </div>
            <div class="mt-4 pt-3 bg-surface-container-low/60 -mx-5 -mb-5 px-5 pb-4 rounded-b-xl flex items-center justify-between text-xs text-on-surface-variant border-t border-surface-container-high">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <span>Day 1: 294 Verified</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span>Day 2: 84 Rolling</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bento Grid: Comparative Visuals (7 / 5 split) --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
        {{-- Chart 1: Academic Tracks (7 cols) --}}
        <div class="xl:col-span-7 bg-surface-container-lowest rounded-xl p-6 shadow-xs border border-surface-container-high flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 gap-2 border-b border-surface-container-high">
                <div>
                    <h2 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.recap.chart_track_title') }}</h2>
                    <p class="text-xs text-on-surface-variant">Workload and submission distribution across 4 core agriculture tracks</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-primary"></span>
                        <span>Submissions</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-secondary"></span>
                        <span>Accepted</span>
                    </div>
                </div>
            </div>

            {{-- Bars --}}
            <div class="space-y-4 pt-4">
                {{-- Track 1 --}}
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between text-xs">
                        <span class="font-medium text-on-surface">Sustainable Agrifood Systems</span>
                        <span class="font-mono text-on-surface-variant">112 Submissions • 62 Accepted</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-surface-container-high overflow-hidden flex">
                        <div class="h-full bg-primary rounded-l-full" style="width: 55%;"></div>
                        <div class="h-full bg-secondary rounded-r-full" style="width: 30%;"></div>
                    </div>
                </div>

                {{-- Track 2 --}}
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between text-xs">
                        <span class="font-medium text-on-surface">Climate-smart Agriculture</span>
                        <span class="font-mono text-on-surface-variant">98 Submissions • 54 Accepted</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-surface-container-high overflow-hidden flex">
                        <div class="h-full bg-primary rounded-l-full" style="width: 48%;"></div>
                        <div class="h-full bg-secondary rounded-r-full" style="width: 26%;"></div>
                    </div>
                </div>

                {{-- Track 3 --}}
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between text-xs">
                        <span class="font-medium text-on-surface">Post-harvest Technology & Processing</span>
                        <span class="font-mono text-on-surface-variant">74 Submissions • 40 Accepted</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-surface-container-high overflow-hidden flex">
                        <div class="h-full bg-primary rounded-l-full" style="width: 36%;"></div>
                        <div class="h-full bg-secondary rounded-r-full" style="width: 20%;"></div>
                    </div>
                </div>

                {{-- Track 4 --}}
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between text-xs">
                        <span class="font-medium text-on-surface">Food Security, Policy & Agribusiness</span>
                        <span class="font-mono text-on-surface-variant">58 Submissions • 26 Accepted</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-surface-container-high overflow-hidden flex">
                        <div class="h-full bg-primary rounded-l-full" style="width: 28%;"></div>
                        <div class="h-full bg-secondary rounded-r-full" style="width: 13%;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Participant Demographic & Origin (5 cols) --}}
        <div class="xl:col-span-5 bg-surface-container-lowest rounded-xl p-6 shadow-xs border border-surface-container-high flex flex-col justify-between">
            <div class="pb-3 border-b border-surface-container-high">
                <h2 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.recap.chart_demographics') }}</h2>
                <p class="text-xs text-on-surface-variant">Affiliation balance and participant regional makeup</p>
            </div>

            <div class="py-4 flex flex-col gap-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-on-surface font-semibold">Domestic vs International</span>
                    <span class="font-mono font-bold text-primary">76% Domestic / 24% Intl</span>
                </div>
                <div class="w-full h-2 rounded-full bg-surface-container-high flex overflow-hidden">
                    <div class="h-full bg-primary" style="width: 76%;"></div>
                    <div class="h-full bg-secondary" style="width: 24%;"></div>
                </div>

                <div class="mt-2 space-y-2 text-xs">
                    <span class="text-[10px] uppercase font-bold text-outline tracking-wider">Top Institutions Represented</span>
                    <div class="flex items-center justify-between py-1 border-b border-surface-container-high">
                        <span class="text-on-surface">Universitas Andalas (Host)</span>
                        <span class="font-mono font-semibold text-primary">124 delegates</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-surface-container-high">
                        <span class="text-on-surface">IPB University</span>
                        <span class="font-mono font-semibold">42 delegates</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-surface-container-high">
                        <span class="text-on-surface">Badan Riset & Inovasi Nasional (BRIN)</span>
                        <span class="font-mono font-semibold">28 delegates</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-on-surface">Universiti Putra Malaysia (Intl)</span>
                        <span class="font-mono font-semibold text-secondary">16 delegates</span>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded-lg bg-surface-container-low text-[11px] text-on-surface-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px] text-primary">public</span>
                <span>8 Foreign Countries Represented across Southeast Asia & Oceania</span>
            </div>
        </div>
    </div>

    {{-- Summary Session Breakdown Table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden">
        <div class="p-4 border-b border-surface-container-high flex items-center justify-between">
            <h3 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.recap.summary_table_title') }}</h3>
            <span class="text-xs text-on-surface-variant font-mono">4 Tracks Active</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-semibold uppercase tracking-wider h-10 border-b border-surface-container-high">
                        <th class="px-4">Track Name</th>
                        <th class="px-4 text-center">Submissions</th>
                        <th class="px-4 text-center">Accepted</th>
                        <th class="px-4 text-center">Settled Payments</th>
                        <th class="px-4 text-center">Check-in Quorum</th>
                        <th class="px-4 text-right">Assigned Room</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    <tr class="hover:bg-surface-container-low/50 transition">
                        <td class="px-4 py-3 font-semibold text-on-surface">Sustainable Agrifood Systems</td>
                        <td class="px-4 text-center font-mono">112</td>
                        <td class="px-4 text-center font-mono text-primary font-bold">62</td>
                        <td class="px-4 text-center font-mono text-emerald-700">58 (93%)</td>
                        <td class="px-4 text-center font-mono">81.2%</td>
                        <td class="px-4 text-right font-medium">Auditorium Main Hall</td>
                    </tr>
                    <tr class="hover:bg-surface-container-low/50 transition">
                        <td class="px-4 py-3 font-semibold text-on-surface">Climate-smart Agriculture</td>
                        <td class="px-4 text-center font-mono">98</td>
                        <td class="px-4 text-center font-mono text-primary font-bold">54</td>
                        <td class="px-4 text-center font-mono text-emerald-700">49 (90%)</td>
                        <td class="px-4 text-center font-mono">77.5%</td>
                        <td class="px-4 text-right font-medium">Room 2A</td>
                    </tr>
                    <tr class="hover:bg-surface-container-low/50 transition">
                        <td class="px-4 py-3 font-semibold text-on-surface">Post-harvest Technology</td>
                        <td class="px-4 text-center font-mono">74</td>
                        <td class="px-4 text-center font-mono text-primary font-bold">40</td>
                        <td class="px-4 text-center font-mono text-emerald-700">35 (87%)</td>
                        <td class="px-4 text-center font-mono">72.0%</td>
                        <td class="px-4 text-right font-medium">Room 2B</td>
                    </tr>
                    <tr class="hover:bg-surface-container-low/50 transition">
                        <td class="px-4 py-3 font-semibold text-on-surface">Food Security & Agribusiness</td>
                        <td class="px-4 text-center font-mono">58</td>
                        <td class="px-4 text-center font-mono text-primary font-bold">26</td>
                        <td class="px-4 text-center font-mono text-emerald-700">22 (84%)</td>
                        <td class="px-4 text-center font-mono">80.4%</td>
                        <td class="px-4 text-right font-medium">Room 3A</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

