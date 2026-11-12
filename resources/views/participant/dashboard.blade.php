@php
    $participantType = session('participant_type', 'oral');
    $isListener = ($participantType === 'listener');
    
    // Dummy participant data
    // {{-- TODO(BE): ganti dengan Auth::user()->participant --}}
    $participant = [
        'name' => 'Prof. Dr. Ir. Ahmad Fauzi, M.Sc.',
        'reg_no' => 'ASIC-2027-' . ($isListener ? 'LIS-8841' : 'PRS-0014'),
        'institution' => 'Universitas Andalas',
        'type_label' => match($participantType) {
            'poster' => __('asic-participant.role_poster') ?? 'Poster Presenter',
            'listener' => __('asic-participant.role_listener') ?? 'Listener (Non-Presenter)',
            default => __('asic-participant.role_oral') ?? 'Oral Presenter',
        },
        'abstract_status' => 'accepted', // submitted, under_review, accepted, rejected
        'payment_status' => 'pending', // pending, verified
        'attendance_status' => 'not_checked_in',
        'certificate_ready' => false,
    ];
@endphp

@extends('layouts.portal', [
    'role' => 'participant',
    'roleName' => $participant['type_label'],
    'userName' => $participant['name']
])

@section('content')
<div class="space-y-6">
    <!-- Top Greeting & Meta Bar (Image 4 Alignment: Elongated View Abstract + Separate Track Switcher) -->
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-4">
        <!-- Upper Row: Portal Badge & View My Abstract / Attendee Guide + Notification Bell -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-surface-container-highest/60">
            <div class="flex flex-wrap items-center gap-2">
                @if($isListener)
                    <span class="font-mono text-xs uppercase tracking-wider text-secondary font-bold">Attendee Track • Ref: {{ $participant['reg_no'] }}</span>
                    <span class="inline-flex items-center rounded-full bg-surface-container-high px-2.5 py-0.5 text-xs font-semibold text-primary">
                        Non-Presenter
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-fixed text-primary">
                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                        <span>{{ $participant['type_label'] }}</span>
                    </span>
                    <span class="text-xs font-mono font-bold text-on-surface-variant">{{ $participant['reg_no'] }}</span>
                @endif
            </div>

            <!-- Elongated Action Button + Notification Bell (Image 4 Design) -->
            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                @if(!$isListener)
                    <a href="{{ route('fe.participant.abstract') }}" 
                       class="inline-flex items-center gap-2.5 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold text-sm shadow-sm transition active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                        <span>{{ app()->getLocale() === 'id' ? 'Lihat My Abstrak' : 'View My Abstract' }}</span>
                    </a>
                @else
                    <a href="{{ route('fe.participant.documents') }}" 
                       class="inline-flex items-center gap-2.5 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white font-bold text-sm shadow-sm transition active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">menu_book</span>
                        <span>{{ app()->getLocale() === 'id' ? 'Panduan Peserta PDF' : 'Attendee Guide PDF' }}</span>
                    </a>
                @endif

                <a href="{{ route('fe.participant.notifications') }}" 
                   class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl bg-surface-container-low text-on-surface border border-surface-container-highest shadow-xs hover:bg-surface-container transition"
                   title="Notifications">
                    <span class="material-symbols-outlined text-[20px]">notifications</span>
                    <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error"></span>
                </a>
            </div>
        </div>

        <!-- Lower Row: Welcome Title & Track Simulator (Completely Separated!) -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pt-1">
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-on-surface font-display tracking-tight">
                    {{ __('asic-participant.welcome_back', ['name' => $participant['name']]) }}
                </h1>
                <p class="text-xs sm:text-sm text-on-surface-variant flex flex-wrap items-center gap-2">
                    <span>{{ $participant['institution'] }}</span>
                    <span>•</span>
                    <span class="font-semibold text-primary">{{ $isListener ? 'Listener (Attendee Track)' : 'Oral Presenter Track' }}</span>
                    <span>•</span>
                    <span>Conference Date: <span class="font-mono font-bold">{{ $asic['dates'] }}</span></span>
                </p>
            </div>

            <!-- Role Simulator Query Switcher (Separated at bottom right) -->
            <div class="flex items-center gap-1.5 p-1 bg-surface-container-low rounded-xl border border-surface-container-highest text-xs self-start md:self-auto">
                <span class="text-[11px] font-bold text-on-surface-variant px-2 uppercase tracking-wider">Track:</span>
                <a href="{{ route('fe.participant.switch-track', 'oral') }}" 
                   class="px-2.5 py-1 rounded-lg font-semibold transition {{ $participantType === 'oral' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                    Oral
                </a>
                <a href="{{ route('fe.participant.switch-track', 'poster') }}" 
                   class="px-2.5 py-1 rounded-lg font-semibold transition {{ $participantType === 'poster' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                    Poster
                </a>
                <a href="{{ route('fe.participant.switch-track', 'listener') }}" 
                   class="px-2.5 py-1 rounded-lg font-semibold transition {{ $participantType === 'listener' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                    Listener
                </a>
            </div>
        </div>
    </div>

    @if($isListener)
        <!-- ==========================================
             LISTENER VIEW (Matches p__c1b_dashboard_listener.html)
             ========================================== -->
        
        <!-- Listener Lifecycle Horizontal Stepper (8 Steps) -->
        <div class="rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-surface-container-highest">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]">linear_scale</span>
                    <span class="font-bold text-base text-on-surface font-display">Listener Journey Progression</span>
                </div>
                <span class="font-mono text-xs text-on-surface-variant font-medium">Step 3 of 8 • Invoice Issued</span>
            </div>

            <!-- 8 Steps -->
            <div class="relative w-full overflow-x-auto pt-6 pb-2">
                <div class="min-w-[840px] relative">
                    <!-- Progress Line -->
                    <div class="absolute left-6 right-6 top-5 h-1 bg-surface-container-highest -z-0">
                        <div class="h-full w-[30%] bg-primary transition-all duration-500"></div>
                    </div>

                    <div class="relative z-10 flex items-center justify-between">
                        <!-- Step 1: Account -->
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-on-primary shadow-sm ring-4 ring-surface-container-lowest">
                                <span class="material-symbols-outlined text-[20px]">check</span>
                            </div>
                            <span class="text-xs font-bold text-on-surface">Account</span>
                            <span class="font-mono text-[10px] text-secondary font-semibold">Completed</span>
                        </div>

                        <!-- Step 2: Registration -->
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-on-primary shadow-sm ring-4 ring-surface-container-lowest">
                                <span class="material-symbols-outlined text-[20px]">check</span>
                            </div>
                            <span class="text-xs font-bold text-on-surface">Registration</span>
                            <span class="font-mono text-[10px] text-secondary font-semibold">Recorded</span>
                        </div>

                        <!-- Step 3: Invoice (Active) -->
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-tertiary-fixed text-on-tertiary-fixed shadow-md ring-4 ring-tertiary-fixed/30 animate-pulse">
                                <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                            </div>
                            <span class="text-xs font-bold text-primary">Invoice</span>
                            <span class="rounded-full bg-tertiary-fixed/40 px-2 py-0.5 font-mono text-[10px] font-bold text-tertiary">Pending Pay</span>
                        </div>

                        <!-- Step 4: Payment -->
                        <div class="flex flex-col items-center gap-1.5 opacity-70">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant ring-4 ring-surface-container-lowest">
                                <span class="material-symbols-outlined text-[20px]">payments</span>
                            </div>
                            <span class="text-xs text-on-surface-variant font-medium">Payment</span>
                            <span class="font-mono text-[10px] text-outline">Upcoming</span>
                        </div>

                        <!-- Step 5: Verification -->
                        <div class="flex flex-col items-center gap-1.5 opacity-70">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant ring-4 ring-surface-container-lowest">
                                <span class="material-symbols-outlined text-[20px]">policy</span>
                            </div>
                            <span class="text-xs text-on-surface-variant font-medium">Verification</span>
                            <span class="font-mono text-[10px] text-outline">Upcoming</span>
                        </div>

                        <!-- Step 6: Re-registration -->
                        <div class="flex flex-col items-center gap-1.5 opacity-70">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant ring-4 ring-surface-container-lowest">
                                <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
                            </div>
                            <span class="text-xs text-on-surface-variant font-medium">Re-registration</span>
                            <span class="font-mono text-[10px] text-outline">On-site</span>
                        </div>

                        <!-- Step 7: Seminar -->
                        <div class="flex flex-col items-center gap-1.5 opacity-70">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant ring-4 ring-surface-container-lowest">
                                <span class="material-symbols-outlined text-[20px]">co_present</span>
                            </div>
                            <span class="text-xs text-on-surface-variant font-medium">Seminar</span>
                            <span class="font-mono text-[10px] text-outline">Day 1 & 2</span>
                        </div>

                        <!-- Step 8: Certificate -->
                        <div class="flex flex-col items-center gap-1.5 opacity-70">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant ring-4 ring-surface-container-lowest">
                                <span class="material-symbols-outlined text-[20px]">military_tech</span>
                            </div>
                            <span class="text-xs text-on-surface-variant font-medium">Certificate</span>
                            <span class="font-mono text-[10px] text-outline">Post-Event</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listener Main Grid (2-Column 8:4) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Next Action Card (Pay Invoice) -->
                <div class="relative overflow-hidden rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-sm">
                    <div class="absolute left-0 top-0 h-1.5 w-full bg-primary"></div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-tertiary-fixed text-tertiary shadow-sm">
                                <span class="material-symbols-outlined text-[28px]">notification_important</span>
                            </div>
                            <div class="space-y-1">
                                <h2 class="text-lg font-bold text-on-surface font-display leading-tight">
                                    Complete your registration and pay the invoice
                                </h2>
                                <p class="text-xs text-on-surface-variant max-w-xl leading-relaxed">
                                    Your listener registration has been recorded. Please complete payment within 7 days of invoice date to confirm your seat.
                                </p>
                                <div class="mt-2 flex flex-wrap items-center gap-4 text-xs">
                                    <div class="flex items-center gap-1.5 font-semibold text-error">
                                        <span class="material-symbols-outlined text-[16px]">schedule</span>
                                        <span>Invoice Window: <strong>7 Days Remaining</strong></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 font-semibold text-secondary">
                                        <span class="material-symbols-outlined text-[16px]">verified_user</span>
                                        <span>QRIS & Bank Transfer</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button in Prototype #F5C518 gold style -->
                        <div class="flex shrink-0">
                            <a href="{{ route('fe.participant.payment.checkout') }}" 
                               class="inline-flex items-center gap-2 rounded-xl bg-[#F5C518] hover:bg-[#E5B814] px-5 py-2.5 text-sm font-bold text-[#111827] shadow-sm transition active:scale-95">
                                <span>Proceed to Payment</span>
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Listener Registration Details Card -->
                <div class="rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[22px]">badge</span>
                            <h2 class="text-base font-bold text-on-surface font-display">Listener Registration Details</h2>
                        </div>
                        <span class="rounded-full bg-surface-container-low px-3 py-1 font-mono text-xs font-semibold text-secondary border border-surface-container-highest">
                            Invoice #INV-2027-0841-LIS
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1 rounded-xl bg-surface-container-low p-4 border border-surface-container-highest/60">
                            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Attendance Mode</span>
                            <div class="flex items-center gap-1.5 text-primary font-bold text-sm">
                                <span class="material-symbols-outlined text-[18px]">domain</span>
                                <span>Onsite (Padang, ID)</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant">Convention Hall UNAND</span>
                        </div>

                        <div class="flex flex-col gap-1 rounded-xl bg-surface-container-low p-4 border border-surface-container-highest/60">
                            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Category</span>
                            <div class="flex items-center gap-1.5 text-on-surface font-bold text-sm">
                                <span class="material-symbols-outlined text-[18px] text-secondary">groups</span>
                                <span>Regular Participant</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant">Non-Presenter Attendee Pass</span>
                        </div>

                        <div class="flex flex-col gap-1 rounded-xl bg-surface-container-low p-4 border border-surface-container-highest/60">
                            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Registration Fee</span>
                            <div class="flex items-center gap-1.5 text-primary font-bold text-sm">
                                <span class="material-symbols-outlined text-[18px]">payments</span>
                                <span class="font-mono">IDR 500.000 / USD 50</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant">Includes seminar kit & lunch</span>
                        </div>
                    </div>

                    <!-- Inclusion Banner -->
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-primary-fixed/20 border border-primary-fixed/40 p-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-[24px]">checklist</span>
                            <div>
                                <span class="text-xs font-bold text-on-surface block">Listener Pass Perks Included</span>
                                <span class="text-[11px] text-on-surface-variant">Plenary Sessions • Keynote Speeches • Parallel Abstract Sessions • Conference Kit • Certificate of Attendance</span>
                            </div>
                        </div>
                        <span class="rounded-full bg-secondary-container px-2.5 py-0.5 text-[11px] font-bold text-on-secondary-container">
                            All Keynotes Access
                        </span>
                    </div>
                </div>

                <!-- Community Card (WhatsApp & Telegram Group) -->
                <div class="rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-xs">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-secondary-container text-secondary">
                                <span class="material-symbols-outlined text-[26px]">forum</span>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-base font-bold text-on-surface font-display">Join WhatsApp / Telegram Group</h3>
                                <p class="text-xs text-on-surface-variant max-w-lg leading-relaxed">
                                    Connect directly with conference conveners, fellow attendee delegates, and keynote panelists. Receive live room updates and networking events.
                                </p>
                                <div class="flex items-center gap-3 text-[11px] text-on-surface-variant pt-1">
                                    <span class="flex items-center gap-1 font-semibold text-primary">
                                        <span class="h-2 w-2 rounded-full bg-primary inline-block"></span> 320+ delegates joined
                                    </span>
                                    <span>•</span>
                                    <span>Official broadcast channel</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="https://chat.whatsapp.com/sample" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary text-on-primary text-xs font-semibold shadow-xs hover:bg-primary-container transition">
                                <span class="material-symbols-outlined text-[16px]">chat</span>
                                <span>Join WhatsApp</span>
                            </a>
                            <a href="https://t.me/sample" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-container-high text-on-surface text-xs font-semibold shadow-xs hover:bg-surface-container-highest transition">
                                <span class="material-symbols-outlined text-[16px]">send</span>
                                <span>Telegram</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Event Information Card -->
                <div class="rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-xs space-y-4">
                    <div class="flex items-center gap-2 border-b border-surface-container-highest pb-3">
                        <span class="material-symbols-outlined text-primary text-[22px]">event</span>
                        <h3 class="text-base font-bold text-on-surface font-display">Event Information</h3>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary text-[20px] mt-0.5">pin_drop</span>
                            <div>
                                <span class="font-bold text-on-surface block">Conference Venue</span>
                                <span class="text-on-surface-variant block">{{ $asic['venue'] }}</span>
                                <span class="text-secondary text-[11px]">{{ $asic['city'] }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary text-[20px] mt-0.5">schedule</span>
                            <div>
                                <span class="font-bold text-on-surface block">Conference Date</span>
                                <span class="font-mono font-bold text-primary block">{{ $asic['dates'] }}</span>
                                <span class="text-on-surface-variant text-[11px]">Hybrid Plenary & Parallel Sessions</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary text-[20px] mt-0.5">translate</span>
                            <div>
                                <span class="font-bold text-on-surface block">Working Language</span>
                                <span class="text-on-surface-variant block">English (Official Medium)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- On-site QR Preview Card -->
                <div class="rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-xs text-center space-y-3">
                    <h3 class="text-sm font-bold text-on-surface font-display text-left flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">qr_code_scanner</span>
                        <span>Attendee Check-in Pass</span>
                    </h3>

                    <div class="p-3 bg-white rounded-xl border border-surface-container-highest inline-block shadow-inner">
                        <img src="{{ asset('images/asic/qr-sample.svg') }}" alt="QR Code" class="w-36 h-36 mx-auto">
                    </div>
                    <p class="font-mono text-xs font-bold text-primary">{{ $participant['reg_no'] }}</p>
                    <p class="text-[11px] text-on-surface-variant leading-relaxed">
                        Present this barcode at the on-site registration desk to print your conference badge and receive the seminar package.
                    </p>
                    <a href="{{ route('fe.participant.attendance-qr') }}" class="inline-flex items-center justify-center gap-1.5 w-full py-2 rounded-xl bg-surface-container-low hover:bg-surface-container text-xs font-semibold text-on-surface transition border border-surface-container-highest">
                        <span class="material-symbols-outlined text-[16px]">fullscreen</span>
                        <span>View Full Pass</span>
                    </a>
                </div>

                <!-- Secretariat Helpdesk -->
                <div class="rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-xs space-y-3">
                    <h3 class="text-sm font-bold text-on-surface font-display flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">support_agent</span>
                        <span>Secretariat Helpdesk</span>
                    </h3>
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        Need assistance with your listener badge, receipt, or hotel reservations in Padang?
                    </p>
                    <div class="space-y-2 text-xs pt-1">
                        <a href="mailto:{{ $asic['email'] }}" class="flex items-center gap-2 text-primary font-semibold hover:underline">
                            <span class="material-symbols-outlined text-[16px]">mail</span>
                            <span>{{ $asic['email'] }}</span>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $asic['whatsapp']) }}" target="_blank" class="flex items-center gap-2 text-primary font-semibold hover:underline">
                            <span class="material-symbols-outlined text-[16px]">chat</span>
                            <span>{{ $asic['whatsapp'] }} (WhatsApp)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- ==========================================
             PRESENTER VIEW (Matches p__c1_dashboard_presenter.html)
             ========================================== -->

        <!-- 9-Step Presenter Stepper Bar -->
        <div class="w-full bg-surface-container-lowest rounded-2xl p-6 border border-surface-container-highest shadow-xs overflow-x-auto">
            <div class="flex items-center justify-between min-w-[960px] relative">
                <!-- Background Connecting Line -->
                <div class="absolute top-4 left-6 right-6 h-1 bg-surface-container-highest -z-0"></div>
                <div class="absolute top-4 left-6 w-[28%] h-1 bg-primary -z-0"></div>

                <!-- Step 1: Account -->
                <div class="relative z-10 flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[18px]">check</span>
                    </div>
                    <span class="text-xs font-semibold text-primary">1. Account</span>
                    <span class="font-mono text-[10px] text-outline">Completed</span>
                </div>

                <!-- Step 2: Abstract -->
                <div class="relative z-10 flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[18px]">check</span>
                    </div>
                    <span class="text-xs font-semibold text-primary">2. Abstract</span>
                    <span class="font-mono text-[10px] text-primary font-bold">Submitted</span>
                </div>

                <!-- Step 3: Selection (Active) -->
                <div class="relative z-10 flex flex-col items-center gap-1">
                    <div class="w-9 h-9 rounded-full bg-primary-container text-tertiary-fixed flex items-center justify-center ring-4 ring-tertiary-fixed/30 shadow-md">
                        <span class="material-symbols-outlined text-[20px]">task_alt</span>
                    </div>
                    <span class="text-xs font-bold text-primary-container px-2 py-0.5 rounded-full bg-secondary-container/60">3. Selection</span>
                    <span class="text-[11px] font-bold text-tertiary">Accepted</span>
                </div>

                <!-- Step 4: LOA & Invoice -->
                <div class="relative z-10 flex flex-col items-center gap-1 opacity-70">
                    <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center">
                        <span class="font-mono text-xs font-bold">4</span>
                    </div>
                    <span class="text-xs text-on-surface-variant">LOA & Invoice</span>
                    <span class="font-mono text-[10px] text-outline">Pending</span>
                </div>

                <!-- Step 5: Payment -->
                <div class="relative z-10 flex flex-col items-center gap-1 opacity-70">
                    <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center">
                        <span class="font-mono text-xs font-bold">5</span>
                    </div>
                    <span class="text-xs text-on-surface-variant">Payment</span>
                    <span class="font-mono text-[10px] text-outline">Upcoming</span>
                </div>

                <!-- Step 6: Verification -->
                <div class="relative z-10 flex flex-col items-center gap-1 opacity-70">
                    <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center">
                        <span class="font-mono text-xs font-bold">6</span>
                    </div>
                    <span class="text-xs text-on-surface-variant">Verification</span>
                    <span class="font-mono text-[10px] text-outline">Upcoming</span>
                </div>

                <!-- Step 7: Re-registration -->
                <div class="relative z-10 flex flex-col items-center gap-1 opacity-70">
                    <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center">
                        <span class="font-mono text-xs font-bold">7</span>
                    </div>
                    <span class="text-xs text-on-surface-variant">Re-registration</span>
                    <span class="font-mono text-[10px] text-outline">Upcoming</span>
                </div>

                <!-- Step 8: Seminar -->
                <div class="relative z-10 flex flex-col items-center gap-1 opacity-70">
                    <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center">
                        <span class="font-mono text-xs font-bold">8</span>
                    </div>
                    <span class="text-xs text-on-surface-variant">Seminar</span>
                    <span class="font-mono text-[10px] text-outline">Upcoming</span>
                </div>

                <!-- Step 9: Certificate -->
                <div class="relative z-10 flex flex-col items-center gap-1 opacity-70">
                    <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center">
                        <span class="font-mono text-xs font-bold">9</span>
                    </div>
                    <span class="text-xs text-on-surface-variant">Certificate</span>
                    <span class="font-mono text-[10px] text-outline">Upcoming</span>
                </div>
            </div>
        </div>

        <!-- Alert / Next Action Banner for Presenter -->
        <x-ui.alert variant="success" title="Abstract Accepted for Oral Presentation!" dismissible>
            Your abstract manuscript has been officially accepted by the scientific committee. Please settle the registration invoice before June 30, 2027 to confirm your presentation slot.
            <div class="mt-2.5 flex flex-wrap items-center gap-2">
                <x-ui.button size="sm" variant="success" href="{{ route('fe.participant.documents') }}" iconLeft="download">
                    Download LoA
                </x-ui.button>
                <x-ui.button size="sm" variant="outline" href="{{ route('fe.participant.payment.checkout') }}" iconLeft="credit_card">
                    Complete Payment
                </x-ui.button>
            </div>
        </x-ui.alert>

        <!-- Key Status Cards Grid (4 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Abstract Card -->
            <x-ui.card padding="p-5" class="hover:border-primary/50 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-participant.abstract_card_title') }}</p>
                        <div class="mt-2">
                            <x-ui.badge variant="success" dot>{{ __('asic-status.accepted') }}</x-ui.badge>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-2 truncate max-w-[180px]">Biochar in Tropical Soils...</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primary-fixed/40 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">description</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container-highest">
                    <a href="{{ route('fe.participant.abstract') }}" class="text-xs font-bold text-primary flex items-center gap-1 hover:underline">
                        <span>View Submission</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </x-ui.card>

            <!-- Payment Card -->
            <x-ui.card padding="p-5" class="hover:border-primary/50 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-participant.payment_card_title') }}</p>
                        <div class="mt-2">
                            <x-ui.badge variant="warning" dot>Pending Payment</x-ui.badge>
                        </div>
                        <p class="text-xs font-mono font-bold text-on-surface mt-2">IDR 1.500.000</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-secondary-container/30 text-secondary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">payments</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container-highest">
                    <a href="{{ route('fe.participant.payment.checkout') }}" class="text-xs font-bold text-secondary flex items-center gap-1 hover:underline">
                        <span>Settle Payment</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </x-ui.card>

            <!-- Attendance Card -->
            <x-ui.card padding="p-5" class="hover:border-primary/50 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-participant.attendance_card_title') }}</p>
                        <div class="mt-2">
                            <x-ui.badge variant="warning" dot>{{ __('asic-status.not_checked_in') }}</x-ui.badge>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-2">Hall Room B (Day 1)</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-surface-container-high text-on-surface flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">qr_code_2</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container-highest">
                    <a href="{{ route('fe.participant.attendance-qr') }}" class="text-xs font-bold text-primary flex items-center gap-1 hover:underline">
                        <span>Show Check-In QR</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </x-ui.card>

            <!-- Certificate Card -->
            <x-ui.card padding="p-5" class="hover:border-primary/50 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __('asic-participant.certificate_card_title') }}</p>
                        <div class="mt-2">
                            <x-ui.badge variant="neutral" dot>{{ __('asic-status.locked') }}</x-ui.badge>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-2">Post-event release</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">workspace_premium</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container-highest">
                    <a href="{{ route('fe.participant.certificate', ['eligible' => 0]) }}" class="text-xs font-bold text-on-surface-variant flex items-center gap-1 hover:underline">
                        <span>Certificate Rules</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </x-ui.card>
        </div>

        <!-- Presenter Bottom Grid (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card title="Paper Review Progress & Milestones" icon="timeline">
                    <x-ui.timeline :steps="[
                        ['title' => 'Abstract Submitted', 'date' => '12 Apr 2027', 'status' => 'completed', 'description' => 'Original manuscript submitted by primary author.'],
                        ['title' => 'Peer Review Evaluation', 'date' => '19 Apr 2027', 'status' => 'completed', 'description' => 'Review score: 4.8 / 5.0 (Accepted with minor suggestions).'],
                        ['title' => 'Letter of Acceptance Issued', 'date' => '24 Apr 2027', 'status' => 'completed', 'description' => 'Signed by ASIC Scientific Committee Chair.'],
                        ['title' => 'Payment Verification', 'date' => 'Pending', 'status' => 'current', 'description' => 'Please confirm transaction before the deadline.'],
                        ['title' => 'Camera-Ready Paper Upload', 'date' => 'Due 15 Jul 2027', 'status' => 'upcoming', 'description' => 'Upload full manuscript for proceeding indexing.']
                    ]" />
                </x-ui.card>

                <!-- Quick Downloads Box -->
                <x-ui.card title="Available Presenter Documents" icon="folder_open">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-xl border border-surface-container-highest flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-[24px]">verified</span>
                                <div>
                                    <p class="text-xs font-bold text-on-surface">Letter of Acceptance</p>
                                    <p class="text-[10px] text-on-surface-variant font-mono">LoA-ASIC-2027</p>
                                </div>
                            </div>
                            <x-ui.button size="sm" variant="outline" href="{{ route('fe.participant.documents') }}" iconLeft="download">
                                Get
                            </x-ui.button>
                        </div>

                        <div class="p-3.5 rounded-xl border border-surface-container-highest flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-[24px]">picture_as_pdf</span>
                                <div>
                                    <p class="text-xs font-bold text-on-surface">Proforma Invoice</p>
                                    <p class="text-[10px] text-on-surface-variant font-mono">#INV-2027-0089</p>
                                </div>
                            </div>
                            <x-ui.button size="sm" variant="outline" href="{{ route('fe.participant.documents') }}" iconLeft="download">
                                Get
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Community Card for Presenters (WhatsApp & Telegram Group) -->
                <div class="rounded-2xl bg-surface-container-lowest p-6 border border-surface-container-highest shadow-xs">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-secondary-container text-secondary">
                                <span class="material-symbols-outlined text-[26px]">forum</span>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-base font-bold text-on-surface font-display">Join WhatsApp / Telegram Group</h3>
                                <p class="text-xs text-on-surface-variant max-w-lg leading-relaxed">
                                    Connect directly with conference conveners, fellow presenter delegates, and keynote panelists. Receive live room updates and schedule notices.
                                </p>
                                <div class="flex items-center gap-3 text-[11px] text-on-surface-variant pt-1">
                                    <span class="flex items-center gap-1 font-semibold text-primary">
                                        <span class="h-2 w-2 rounded-full bg-primary inline-block"></span> 320+ delegates joined
                                    </span>
                                    <span>•</span>
                                    <span>Official broadcast channel</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="https://chat.whatsapp.com/sample" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary text-on-primary text-xs font-semibold shadow-xs hover:bg-primary-container transition">
                                <span class="material-symbols-outlined text-[16px]">chat</span>
                                <span>Join WhatsApp</span>
                            </a>
                            <a href="https://t.me/sample" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-container-high text-on-surface text-xs font-semibold shadow-xs hover:bg-surface-container-highest transition">
                                <span class="material-symbols-outlined text-[16px]">send</span>
                                <span>Telegram</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <x-ui.card title="On-site Check-in QR" icon="qr_code_scanner">
                    <div class="text-center space-y-3">
                        <div class="p-3 bg-white rounded-xl border border-surface-container-highest inline-block shadow-inner">
                            <img src="{{ asset('images/asic/qr-sample.svg') }}" alt="QR Code" class="w-36 h-36 mx-auto">
                        </div>
                        <p class="font-mono text-xs font-bold text-primary">{{ $participant['reg_no'] }}</p>
                        <p class="text-[11px] text-on-surface-variant leading-relaxed">
                            Show this QR code at the registration desk on Day 1 for badge printing and presenter packet.
                        </p>
                        <x-ui.button size="sm" variant="primary" class="w-full" href="{{ route('fe.participant.attendance-qr') }}" iconLeft="fullscreen">
                            Enlarge QR Code
                        </x-ui.button>
                    </div>
                </x-ui.card>

                <!-- Helpdesk Card -->
                <x-ui.card title="Secretariat Helpdesk" icon="support_agent">
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        Have questions about your presentation schedule, hotel accommodation, or invoice?
                    </p>
                    <div class="mt-4 space-y-2 text-xs">
                        <a href="mailto:{{ $asic['email'] }}" class="flex items-center gap-2 text-primary font-semibold hover:underline">
                            <span class="material-symbols-outlined text-[16px]">mail</span>
                            <span>{{ $asic['email'] }}</span>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $asic['whatsapp']) }}" target="_blank" class="flex items-center gap-2 text-primary font-semibold hover:underline">
                            <span class="material-symbols-outlined text-[16px]">chat</span>
                            <span>{{ $asic['whatsapp'] }} (WhatsApp)</span>
                        </a>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @endif
</div>
@endsection
