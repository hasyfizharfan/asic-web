@extends('layouts.portal', [
    'role' => 'secretariat',
    'roleName' => 'Secretariat Admin',
    'nav' => $secNav,
    'title' => __('asic-secretariat.announcements.title') . ' — ASIC 2027'
])

@section('content')
<div x-data="{
    category: 'schedule',
    previewTab: 'banner',
    subject: '[Important] Parallel Session Room Reschedule: Track 2 Agro-technology',
    body: 'Please be advised that the parallel oral presentation session for Track 2 (Agro-technology and Precision Farming) scheduled for 10:30 WIB will be moved from Room 2A to the Main Auditorium West Wing due to technical audiovisual upgrades. Presenters are requested to report 15 minutes prior.',
    channels: {
        email: true,
        whatsapp: true,
        banner: true
    },
    recipients: {
        all: false,
        oral: true,
        poster: false,
        listener: false,
        roomA: true
    },
    get recipientCount() {
        if (this.recipients.all) return 648;
        let c = 0;
        if (this.recipients.oral) c += 142;
        if (this.recipients.poster) c += 196;
        if (this.recipients.listener) c += 310;
        if (this.recipients.roomA) c += 74;
        return c > 0 ? c : 0;
    },
    dispatchNotice() {
        $store.toast.show('Broadcast dispatched successfully to ' + this.recipientCount + ' recipients via 3 channels.', 'success');
    }
}" class="flex flex-col w-full gap-6">

    {{-- Top Action Breadcrumb & Live Stats Bar --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs text-primary uppercase tracking-widest font-semibold">Secretariat Desk</span>
                <span class="text-outline-variant font-mono text-xs">/</span>
                <span class="font-mono text-xs text-outline uppercase tracking-widest">Communications Hub</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container">
                    Dispatch Engine Online
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">{{ __('asic-secretariat.announcements.title') }}</h1>
            <p class="text-xs text-on-surface-variant mt-0.5">{{ __('asic-secretariat.announcements.subtitle') }}</p>
        </div>

        {{-- Telemetry Chips --}}
        <div class="flex items-center gap-2 bg-surface-container-lowest p-1.5 rounded-xl shadow-xs border border-surface-container-high self-start md:self-auto">
            <div class="px-3 py-1.5 rounded-lg bg-surface-container-low flex items-center gap-2 text-xs">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                <span class="text-on-surface font-semibold">SMTP & WA Gateway: Online</span>
            </div>
            <div class="px-3 py-1.5 rounded-lg bg-surface-container-low flex items-center gap-1.5 text-xs">
                <span class="material-symbols-outlined text-[16px] text-primary">groups</span>
                <span class="text-on-surface">{{ __('asic-secretariat.announcements.receivers_count', ['count' => 648]) }}</span>
            </div>
        </div>
    </div>

    {{-- 2-Column Responsive Workspace Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- LEFT COLUMN: COMPOSER FORM (7 cols) --}}
        <section class="lg:col-span-7 flex flex-col gap-6">
            <div class="bg-surface-container-lowest rounded-xl p-5 sm:p-6 shadow-xs border border-surface-container-high flex flex-col gap-5">
                {{-- Category Selector --}}
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-bold text-on-surface uppercase tracking-wide flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-primary">category</span>
                            {{ __('asic-secretariat.announcements.category_label') }}
                        </label>
                        <span class="text-[11px] text-on-surface-variant">Affects priority banner styling</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" @click="category = 'schedule'"
                                :class="category === 'schedule' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-lg text-xs transition">
                            <span class="material-symbols-outlined text-[16px]">schedule</span>
                            <span>{{ __('asic-secretariat.announcements.cat_schedule') }}</span>
                        </button>
                        <button type="button" @click="category = 'venue'"
                                :class="category === 'venue' ? 'bg-error text-white font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-lg text-xs transition">
                            <span class="material-symbols-outlined text-[16px]">warning</span>
                            <span>{{ __('asic-secretariat.announcements.cat_venue') }}</span>
                        </button>
                        <button type="button" @click="category = 'status'"
                                :class="category === 'status' ? 'bg-secondary text-white font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-lg text-xs transition">
                            <span class="material-symbols-outlined text-[16px]">info</span>
                            <span>{{ __('asic-secretariat.announcements.cat_status') }}</span>
                        </button>
                        <button type="button" @click="category = 'session'"
                                :class="category === 'session' ? 'bg-primary-container text-white font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-lg text-xs transition">
                            <span class="material-symbols-outlined text-[16px]">podium</span>
                            <span>{{ __('asic-secretariat.announcements.cat_session') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Audience Multi-Selector --}}
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-bold text-on-surface uppercase tracking-wide flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-primary">target</span>
                            {{ __('asic-secretariat.announcements.audience_label') }}
                        </label>
                        <span class="font-mono text-xs px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-bold">
                            {{ __('asic-secretariat.announcements.selected_recipients', ['count' => '']) }}<span x-text="recipientCount"></span>
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <label class="cursor-pointer">
                            <input type="checkbox" x-model="recipients.all" class="sr-only">
                            <span :class="recipients.all ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs transition select-none">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                All Attendees (648)
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" x-model="recipients.oral" class="sr-only">
                            <span :class="recipients.oral ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs transition select-none">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                Oral Presenters (142)
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" x-model="recipients.poster" class="sr-only">
                            <span :class="recipients.poster ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs transition select-none">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                Poster Presenters (196)
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" x-model="recipients.listener" class="sr-only">
                            <span :class="recipients.listener ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs transition select-none">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                Listeners (310)
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" x-model="recipients.roomA" class="sr-only">
                            <span :class="recipients.roomA ? 'bg-primary text-white font-semibold' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs transition select-none">
                                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                Room 2A Attendees (74)
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Subject Line Input --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface uppercase tracking-wide flex items-center justify-between">
                        <span>{{ __('asic-secretariat.announcements.subject_label') }}</span>
                        <span class="font-mono text-[11px] text-outline" x-text="subject.length + ' / 120 chars'"></span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-outline text-[18px]">flag</span>
                        <input type="text" 
                               x-model="subject"
                               maxlength="120"
                               class="w-full h-11 pl-9 pr-4 rounded-lg bg-surface-container-low text-xs text-on-surface shadow-xs outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition">
                    </div>
                </div>

                {{-- Delivery Channels --}}
                <div class="flex flex-col gap-2 p-4 rounded-xl bg-surface-container-low border border-surface-container-high">
                    <span class="text-xs font-bold text-on-surface uppercase tracking-wide">{{ __('asic-secretariat.announcements.channels_label') }}</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                        <label class="flex items-start gap-2.5 p-3 rounded-lg bg-surface-container-lowest shadow-xs cursor-pointer border border-surface-container-high hover:border-primary/40 transition">
                            <input type="checkbox" x-model="channels.email" class="mt-0.5 accent-primary w-4 h-4 rounded">
                            <div class="flex flex-col text-xs">
                                <span class="font-bold text-on-surface">{{ __('asic-secretariat.announcements.channel_email') }}</span>
                                <span class="text-[10px] text-on-surface-variant">{{ __('asic-secretariat.announcements.channel_email_desc') }}</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 p-3 rounded-lg bg-surface-container-lowest shadow-xs cursor-pointer border border-surface-container-high hover:border-primary/40 transition">
                            <input type="checkbox" x-model="channels.whatsapp" class="mt-0.5 accent-primary w-4 h-4 rounded">
                            <div class="flex flex-col text-xs">
                                <span class="font-bold text-on-surface">{{ __('asic-secretariat.announcements.channel_wa') }}</span>
                                <span class="text-[10px] text-on-surface-variant">{{ __('asic-secretariat.announcements.channel_wa_desc') }}</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 p-3 rounded-lg bg-surface-container-lowest shadow-xs cursor-pointer border border-surface-container-high hover:border-primary/40 transition">
                            <input type="checkbox" x-model="channels.banner" class="mt-0.5 accent-primary w-4 h-4 rounded">
                            <div class="flex flex-col text-xs">
                                <span class="font-bold text-on-surface">{{ __('asic-secretariat.announcements.channel_banner') }}</span>
                                <span class="text-[10px] text-on-surface-variant">{{ __('asic-secretariat.announcements.channel_banner_desc') }}</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Message Body Editor --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-on-surface uppercase tracking-wide">{{ __('asic-secretariat.announcements.content_label') }}</label>
                    <textarea rows="5" 
                              x-model="body"
                              class="w-full rounded-lg bg-surface-container-low p-3.5 text-xs text-on-surface leading-relaxed outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary/20 transition"></textarea>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row items-center gap-2 pt-2 border-t border-surface-container-high">
                    <button type="button" @click="dispatchNotice()" 
                            class="w-full sm:flex-1 py-3 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition">
                        <span class="material-symbols-outlined text-[18px]">send</span>
                        <span>{{ __('asic-secretariat.announcements.btn_dispatch') }}</span>
                    </button>
                    <button type="button" @click="$store.toast.show('Notice scheduled for automatic broadcast at 08:00 WIB.', 'info')" 
                            class="w-full sm:w-auto py-3 px-4 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold flex items-center justify-center gap-2 border border-surface-container-high transition">
                        <span class="material-symbols-outlined text-[18px]">schedule_send</span>
                        <span>{{ __('asic-secretariat.announcements.btn_schedule') }}</span>
                    </button>
                </div>
            </div>
        </section>

        {{-- RIGHT COLUMN: LIVE MULTI-DEVICE PREVIEW (5 cols) --}}
        <section class="lg:col-span-5 flex flex-col gap-4 sticky top-20">
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-xs border border-surface-container-high flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-primary">devices</span>
                        <h3 class="text-xs font-bold text-on-surface uppercase tracking-wide">{{ __('asic-secretariat.announcements.preview_title') }}</h3>
                    </div>
                    <span class="text-[10px] font-mono text-primary font-semibold bg-primary-fixed px-2 py-0.5 rounded">Live Render</span>
                </div>

                {{-- Device Selector Tabs --}}
                <div class="flex items-center rounded-lg bg-surface-container-low p-1 border border-surface-container-high text-xs">
                    <button @click="previewTab = 'banner'" 
                            :class="previewTab === 'banner' ? 'bg-surface-container-lowest text-primary shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface'"
                            class="flex-1 py-1 rounded transition text-center">
                        {{ __('asic-secretariat.announcements.tab_banner') }}
                    </button>
                    <button @click="previewTab = 'wa'" 
                            :class="previewTab === 'wa' ? 'bg-surface-container-lowest text-primary shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface'"
                            class="flex-1 py-1 rounded transition text-center">
                        {{ __('asic-secretariat.announcements.tab_wa') }}
                    </button>
                    <button @click="previewTab = 'email'" 
                            :class="previewTab === 'email' ? 'bg-surface-container-lowest text-primary shadow-xs font-semibold' : 'text-on-surface-variant hover:text-on-surface'"
                            class="flex-1 py-1 rounded transition text-center">
                        {{ __('asic-secretariat.announcements.tab_email') }}
                    </button>
                </div>

                {{-- Preview 1: In-App Dashboard Banner --}}
                <div x-show="previewTab === 'banner'" class="flex flex-col gap-3">
                    <span class="text-[10px] text-outline font-semibold uppercase">Attendee Dashboard View</span>
                    <div :class="{
                            'bg-primary-container text-white': category === 'schedule',
                            'bg-red-700 text-white': category === 'venue',
                            'bg-secondary text-white': category === 'status',
                            'bg-emerald-800 text-white': category === 'session'
                         }" 
                         class="p-4 rounded-xl shadow-xs flex items-start gap-3 transition-colors">
                        <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">campaign</span>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold line-clamp-1" x-text="subject"></span>
                            <p class="text-[11px] opacity-90 mt-1 leading-relaxed line-clamp-3" x-text="body"></p>
                        </div>
                    </div>
                </div>

                {{-- Preview 2: WhatsApp Push View --}}
                <div x-show="previewTab === 'wa'" class="flex flex-col gap-3" style="display: none;">
                    <span class="text-[10px] text-outline font-semibold uppercase">Mobile WhatsApp Push Card</span>
                    <div class="bg-[#e5ddd5] p-3 rounded-xl border border-surface-container-high flex flex-col gap-2">
                        <div class="bg-white p-3 rounded-lg shadow-xs flex flex-col gap-1 text-xs text-[#111c2d]">
                            <span class="font-bold text-[#075e54] flex items-center gap-1 text-[11px]">
                                <span class="material-symbols-outlined text-[13px]">verified</span>
                                ASIC 2027 Secretariat Official
                            </span>
                            <span class="font-bold mt-1 text-xs" x-text="subject"></span>
                            <p class="text-[11px] text-gray-700 leading-relaxed whitespace-pre-line mt-1" x-text="body"></p>
                            <span class="text-[9px] text-gray-400 text-right mt-1 font-mono">10:42 WIB ✓✓</span>
                        </div>
                    </div>
                </div>

                {{-- Preview 3: Email Template View --}}
                <div x-show="previewTab === 'email'" class="flex flex-col gap-3" style="display: none;">
                    <span class="text-[10px] text-outline font-semibold uppercase">Official Committee Email</span>
                    <div class="bg-white rounded-xl border border-surface-container-high p-4 flex flex-col gap-3 text-xs shadow-xs">
                        <div class="flex items-center gap-2 border-b border-surface-container-high pb-2">
                            <img src="{{ asset('images/asic/logo.svg') }}" alt="Logo" class="w-6 h-6 object-contain">
                            <div class="flex flex-col">
                                <span class="font-bold text-[11px]">ASIC 2027 Secretariat Notice</span>
                                <span class="text-[9px] text-outline">secretariat@asic.unand.ac.id</span>
                            </div>
                        </div>
                        <h4 class="font-bold text-xs text-on-surface" x-text="subject"></h4>
                        <p class="text-[11px] text-on-surface-variant leading-relaxed whitespace-pre-line" x-text="body"></p>
                        <div class="pt-2 border-t border-surface-container-high text-[10px] text-outline">
                            Faculty of Agriculture, Universitas Andalas • Padang, West Sumatra
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Past Broadcast Logs Table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container-high overflow-hidden">
        <div class="p-4 border-b border-surface-container-high flex items-center justify-between">
            <h3 class="text-sm font-bold text-on-surface">{{ __('asic-secretariat.announcements.history_title') }}</h3>
            <span class="text-xs text-on-surface-variant font-mono">3 Dispatches Recorded</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-semibold uppercase tracking-wider h-10 border-b border-surface-container-high">
                        <th class="px-4">Timestamp</th>
                        <th class="px-4">Notice Title</th>
                        <th class="px-4">Category</th>
                        <th class="px-4">Target Segment</th>
                        <th class="px-4">Channels</th>
                        <th class="px-4 text-right">Delivery Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    <tr class="hover:bg-surface-container-low/50 transition">
                        <td class="px-4 py-3 font-mono text-[11px] text-on-surface-variant whitespace-nowrap">Today, 08:30 WIB</td>
                        <td class="px-4 font-semibold text-on-surface">Registration Desk & Technical Rehearsal Open</td>
                        <td class="px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary text-white">Schedule</span>
                        </td>
                        <td class="px-4 text-on-surface-variant">All Attendees (648)</td>
                        <td class="px-4 font-mono text-[11px]">Email, WA, App</td>
                        <td class="px-4 text-right font-bold text-emerald-700 font-mono">99.4% (644/648)</td>
                    </tr>
                    <tr class="hover:bg-surface-container-low/50 transition">
                        <td class="px-4 py-3 font-mono text-[11px] text-on-surface-variant whitespace-nowrap">Yesterday, 16:15 WIB</td>
                        <td class="px-4 font-semibold text-on-surface">Keynote Slide Deck Submission Reminder</td>
                        <td class="px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-secondary text-white">Status</span>
                        </td>
                        <td class="px-4 text-on-surface-variant">Presenters Only (338)</td>
                        <td class="px-4 font-mono text-[11px]">Email, WA</td>
                        <td class="px-4 text-right font-bold text-emerald-700 font-mono">98.8% (334/338)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

