@extends('layouts.portal', [
    'role' => 'secretariat',
    'roleName' => 'Secretariat Admin',
    'nav' => $secNav,
    'title' => __('asic-secretariat.attendance.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    activeDay: 1,
    filterCategory: 'all',
    filterRoom: 'all',
    filterCert: 'all',
    searchQuery: '',
    items: [
        {
            id: 'ABS-2027-0104',
            name: 'Dr. Farhan Harahap',
            institution: 'Universitas Andalas',
            type: 'oral',
            title: 'Precision Nitrogen Delivery System in Tropical Rice Cultivation',
            room: 'Hall A (Soil Science)',
            time: '10:30 WIB',
            day1_present: true,
            day2_present: true,
            presented: true,
            eligible: true
        },
        {
            id: 'ABS-2027-0103',
            name: 'Prof. Siti Nurhaliza',
            institution: 'IPB University',
            type: 'poster',
            title: 'Enzymatic Hydrolysis for Post-harvest Cocoa Shell Waste Valorization',
            room: 'Main Gallery (Posters)',
            time: '14:00 WIB',
            day1_present: true,
            day2_present: false,
            presented: true,
            eligible: false,
            reason: 'missing_day'
        },
        {
            id: 'ABS-2027-0102',
            name: 'Dr. Hendra Wijaya',
            institution: 'Universitas Riau',
            type: 'oral',
            title: 'Community Bio-economy and Micro-finance in Oil Palm Agroforestry',
            room: 'Hall B (Agro-tech)',
            time: '11:15 WIB',
            day1_present: true,
            day2_present: true,
            presented: false,
            eligible: false,
            reason: 'pending_pres'
        },
        {
            id: 'REG-2027-0418',
            name: 'Dewi Lestari, S.P.',
            institution: 'Universitas Brawijaya',
            type: 'listener',
            title: 'Conference Listener / Observer',
            room: 'All Plenary Sessions',
            time: 'Full Day',
            day1_present: true,
            day2_present: true,
            presented: true,
            eligible: true
        }
    ],
    togglePresentation(index) {
        this.items[index].presented = !this.items[index].presented;
        if (this.items[index].presented && this.items[index].day1_present && this.items[index].day2_present) {
            this.items[index].eligible = true;
            $store.toast.show('Presentation verified for ' + this.items[index].name + '. Certificate unlocked.', 'success');
        } else {
            this.items[index].eligible = false;
            $store.toast.show('Presentation status updated.', 'info');
        }
    }
}" class="flex flex-col w-full gap-6">

    {{-- Top Directive / Policy Banner --}}
    <div class="relative overflow-hidden rounded-xl bg-surface-container-high p-4 sm:p-5 shadow-xs border border-surface-container-high">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary text-white shadow-xs">
                <span class="material-symbols-outlined text-[22px]">verified</span>
            </div>
            <div class="flex flex-col gap-1 pr-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-bold text-on-surface">{{ __('asic-secretariat.attendance.directive_title') }}</span>
                    <span class="rounded-full bg-secondary-container px-2 py-0.5 text-[10px] font-bold text-on-secondary-container">Standard Protocol</span>
                </div>
                <p class="text-xs text-on-surface-variant leading-relaxed">
                    {{ __('asic-secretariat.attendance.directive_text') }}
                </p>
            </div>
            <div class="ml-auto hidden xl:flex items-center gap-2 self-center rounded-lg bg-surface-container-lowest px-3 py-1.5 shadow-xs border border-surface-container-high">
                <span class="h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                <span class="font-mono text-xs text-on-surface">ROOM SYNC: ACTIVE</span>
            </div>
        </div>
    </div>

    {{-- Summary Metric Strip --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Metric 1: Total Checked In --}}
        <div class="flex items-center justify-between rounded-xl bg-surface-container-lowest p-5 shadow-xs border border-surface-container-high">
            <div class="flex flex-col">
                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">Total Checked-In</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold font-mono text-on-surface">248</span>
                    <span class="text-sm font-semibold text-on-surface-variant font-mono">/ 342</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-primary text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">trending_up</span>
                    <span>{{ __('asic-secretariat.attendance.checked_in_quorum', ['pct' => '72.5']) }}</span>
                </div>
            </div>
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                    <path class="text-surface-container-high" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3.5"></path>
                    <path class="text-primary" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="72.5, 100" stroke-linecap="round" stroke-width="3.5"></path>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center font-mono text-xs font-bold text-on-surface">73%</span>
            </div>
        </div>

        {{-- Metric 2: Presentations Completed --}}
        <div class="flex items-center justify-between rounded-xl bg-surface-container-lowest p-5 shadow-xs border border-surface-container-high">
            <div class="flex flex-col">
                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-secretariat.attendance.presentations_completed') }}</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold font-mono text-on-surface">92</span>
                    <span class="text-sm font-semibold text-on-surface-variant font-mono">/ 140</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-secondary text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">co_present</span>
                    <span>{{ __('asic-secretariat.attendance.pending_log', ['count' => 48]) }}</span>
                </div>
            </div>
            <div class="relative h-14 w-14 shrink-0">
                <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                    <path class="text-surface-container-high" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3.5"></path>
                    <path class="text-secondary" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="65.7, 100" stroke-linecap="round" stroke-width="3.5"></path>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center font-mono text-xs font-bold text-on-surface">66%</span>
            </div>
        </div>

        {{-- Metric 3: Eligible Certificates --}}
        <div class="flex items-center justify-between rounded-xl bg-surface-container-lowest p-5 shadow-xs border border-surface-container-high">
            <div class="flex flex-col">
                <span class="text-[11px] uppercase tracking-wider text-outline font-semibold">{{ __('asic-secretariat.attendance.certs_ready') }}</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold font-mono text-primary">215</span>
                    <span class="text-sm font-semibold text-on-surface-variant font-mono">/ 342</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-tertiary text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">workspace_premium</span>
                    <span>{{ __('asic-secretariat.attendance.auto_eligible', ['pct' => 63]) }}</span>
                </div>
            </div>
            <div class="flex flex-col items-end justify-center gap-1">
                <button @click="$store.toast.show('Queueing batch certificate generation (215 items)...', 'info')" 
                        class="flex items-center gap-1.5 rounded-lg bg-secondary text-white px-3 py-1.5 text-xs font-semibold shadow-xs hover:bg-secondary/90 transition">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>{{ __('asic-secretariat.attendance.batch_print', ['count' => 215]) }}</span>
                </button>
                <span class="font-mono text-[10px] text-outline">Ready for print desk</span>
            </div>
        </div>
    </div>

    {{-- Operational Filter Toolbar --}}
    <div class="flex flex-col gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-xs border border-surface-container-high">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
            {{-- Search --}}
            <div class="lg:col-span-4 relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-[18px] text-outline">search</span>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Filter by author, presenter ID, or keyword..." 
                       class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-low text-xs text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
            </div>

            {{-- Category Filter --}}
            <div class="lg:col-span-2">
                <select x-model="filterCategory" class="w-full h-10 px-3 rounded-lg bg-surface-container-low text-xs text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 border border-transparent">
                    <option value="all">{{ __('asic-secretariat.attendance.filter_category') }}</option>
                    <option value="oral">Oral Presenters</option>
                    <option value="poster">Poster Presenters</option>
                    <option value="listener">Listeners / General</option>
                </select>
            </div>

            {{-- Room Filter --}}
            <div class="lg:col-span-2">
                <select x-model="filterRoom" class="w-full h-10 px-3 rounded-lg bg-surface-container-low text-xs text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 border border-transparent">
                    <option value="all">{{ __('asic-secretariat.attendance.filter_room') }}</option>
                    <option value="a">Hall A (Soil Science)</option>
                    <option value="b">Hall B (Agro-tech)</option>
                    <option value="c">Hall C (Biosecurity)</option>
                    <option value="poster_hall">Main Gallery (Posters)</option>
                </select>
            </div>

            {{-- Day Filter --}}
            <div class="lg:col-span-2">
                <div class="flex items-center rounded-lg bg-surface-container-low p-1 border border-surface-container-high">
                    <button @click="activeDay = 1" 
                            :class="activeDay === 1 ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                            class="flex-1 py-1 rounded text-xs font-semibold transition">
                        {{ __('asic-secretariat.attendance.day_1') }}
                    </button>
                    <button @click="activeDay = 2" 
                            :class="activeDay === 2 ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                            class="flex-1 py-1 rounded text-xs font-semibold transition">
                        {{ __('asic-secretariat.attendance.day_2') }}
                    </button>
                </div>
            </div>

            {{-- Eligibility Filter --}}
            <div class="lg:col-span-2">
                <select x-model="filterCert" class="w-full h-10 px-3 rounded-lg bg-surface-container-low text-xs text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 border border-transparent">
                    <option value="all">{{ __('asic-secretariat.attendance.filter_cert') }}</option>
                    <option value="eligible">Eligible (Auto-issue)</option>
                    <option value="missing_day">Missing Attendance</option>
                    <option value="pending_pres">Presentation Pending</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Academic Data Table Container --}}
    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-xs border border-surface-container-high">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant h-11 text-xs font-semibold uppercase tracking-wider border-b border-surface-container-high">
                        <th class="px-4 min-w-[220px]">{{ __('asic-secretariat.attendance.col_participant') }}</th>
                        <th class="px-4 min-w-[280px]">{{ __('asic-secretariat.attendance.col_session') }}</th>
                        <th class="px-4 w-[140px] text-center">{{ __('asic-secretariat.attendance.col_attendance_log') }}</th>
                        <th class="px-4 w-[160px] text-center">{{ __('asic-secretariat.attendance.col_presentation_log') }}</th>
                        <th class="px-4 w-[160px] text-right">{{ __('asic-secretariat.attendance.col_cert_status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high text-xs text-on-surface">
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="h-16 hover:bg-surface-container-low/50 transition">
                            {{-- Participant --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ asset('images/asic/avatar.svg') }}" alt="Avatar" class="w-8 h-8 rounded-full border border-surface-container-high">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-on-surface" x-text="item.name"></span>
                                        <span class="text-[11px] text-on-surface-variant" x-text="item.institution"></span>
                                        <span class="font-mono text-[10px] text-primary" x-text="'#' + item.id"></span>
                                    </div>
                                </div>
                            </td>

                            {{-- Session & Room --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-on-surface line-clamp-1" x-text="item.title"></span>
                                    <div class="flex items-center gap-2 text-[11px] text-on-surface-variant">
                                        <span class="flex items-center gap-1 text-primary">
                                            <span class="material-symbols-outlined text-[13px]">location_on</span>
                                            <span x-text="item.room"></span>
                                        </span>
                                        <span>•</span>
                                        <span class="font-mono" x-text="item.time"></span>
                                    </div>
                                </div>
                            </td>

                            {{-- Attendance Chips --}}
                            <td class="px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="item.day1_present ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-outline'">
                                        D1
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="item.day2_present ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-outline'">
                                        D2
                                    </span>
                                </div>
                            </td>

                            {{-- Presentation Verification Toggle --}}
                            <td class="px-4 text-center">
                                <template x-if="item.type === 'listener'">
                                    <span class="text-[11px] text-outline font-medium italic">N/A (Listener)</span>
                                </template>
                                <template x-if="item.type !== 'listener'">
                                    <button @click="togglePresentation(index)" 
                                            :class="item.presented ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-surface-container-low text-on-surface-variant border-surface-container-high hover:border-primary/40'"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition shadow-xs">
                                        <span class="material-symbols-outlined text-[14px]" x-text="item.presented ? 'check_circle' : 'schedule'"></span>
                                        <span x-text="item.presented ? '{{ __('asic-secretariat.attendance.pres_completed') }}' : '{{ __('asic-secretariat.attendance.btn_verify_pres') }}'"></span>
                                    </button>
                                </template>
                            </td>

                            {{-- Certificate Status & Action --}}
                            <td class="px-4 text-right">
                                <template x-if="item.eligible">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="material-symbols-outlined text-[13px]">workspace_premium</span>
                                            {{ __('asic-secretariat.attendance.eligible') }}
                                        </span>
                                        <a href="{{ route('fe.participant.certificate', ['eligible' => 1]) }}" target="_blank"
                                           class="p-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container text-primary transition" title="{{ __('asic-secretariat.attendance.btn_view_cert') }}">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        </a>
                                    </div>
                                </template>
                                <template x-if="!item.eligible">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200"
                                          x-text="item.reason === 'missing_day' ? '{{ __('asic-secretariat.attendance.missing_attendance') }}' : '{{ __('asic-secretariat.attendance.missing_pres') }}'">
                                    </span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-surface-container-low/50 border-t border-surface-container-high flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-on-surface-variant">
            <span>Showing 4 of 342 attendance & presentation records</span>
            <div class="flex items-center gap-1">
                <button class="px-2.5 py-1 rounded bg-surface-container-lowest border border-surface-container-high text-on-surface font-medium disabled:opacity-40" disabled>Previous</button>
                <button class="px-2.5 py-1 rounded bg-primary text-white font-semibold">1</button>
                <button class="px-2.5 py-1 rounded bg-surface-container-lowest border border-surface-container-high text-on-surface font-medium hover:bg-surface-container">2</button>
                <button class="px-2.5 py-1 rounded bg-surface-container-lowest border border-surface-container-high text-on-surface font-medium hover:bg-surface-container">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection

