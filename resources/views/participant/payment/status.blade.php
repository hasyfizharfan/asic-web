@php
    $state = request()->query('state', 'verified'); // waiting_payment, waiting_verification, verified, rejected, failed

    $stateConfigs = [
        'waiting_payment' => [
            'badge' => 'warning',
            'title' => 'Waiting for Payment Settlement',
            'icon' => 'hourglass_top',
            'iconBg' => 'bg-secondary-container/40 text-secondary',
            'desc' => 'Your invoice has been generated. Please complete the transaction using the selected payment gateway.',
            'alert' => 'Please complete payment before the invoice expiry date to secure your slot.',
        ],
        'waiting_verification' => [
            'badge' => 'amber',
            'title' => 'Payment Verification in Progress',
            'icon' => 'pending_actions',
            'iconBg' => 'bg-amber-100 text-amber-800',
            'desc' => 'Your transfer proof has been received by the ASIC treasury desk and is being verified against our bank statement.',
            'alert' => 'Treasury verification typically takes 1–3 business hours on weekdays.',
        ],
        'verified' => [
            'badge' => 'success',
            'title' => 'Payment Verified & Confirmed!',
            'icon' => 'verified',
            'iconBg' => 'bg-tertiary-fixed text-primary',
            'desc' => 'Your conference registration fee has been successfully reconciled. You are now officially registered as a participant.',
            'alert' => 'Official kuitansi receipt is ready for download. You can also view your on-site attendance QR code.',
        ],
        'rejected' => [
            'badge' => 'danger',
            'title' => 'Payment Proof Rejected',
            'icon' => 'highlight_off',
            'iconBg' => 'bg-error-container text-error',
            'desc' => 'The treasury team was unable to verify your transfer proof. The transferred amount or reference did not match our records.',
            'alert' => 'Reason: Transfer amount does not match invoice total. Please re-upload a clear proof or contact the treasurer.',
        ],
        'failed' => [
            'badge' => 'danger',
            'title' => 'Payment Gateway Transaction Failed',
            'icon' => 'error',
            'iconBg' => 'bg-error-container text-error',
            'desc' => 'The electronic payment gateway reported a failure. No funds were debited from your account.',
            'alert' => 'Please try another card or select QRIS / direct bank transfer instead.',
        ],
    ];

    $cfg = $stateConfigs[$state] ?? $stateConfigs['verified'];
@endphp

@extends('layouts.portal', [
    'role' => 'participant',
    'roleName' => 'Oral Presenter',
    'nav' => [
        ['label' => __('asic-nav.dashboard'), 'route' => 'participant.dashboard', 'icon' => 'space_dashboard'],
        ['label' => __('asic-nav.my_abstract'), 'route' => 'participant.abstract', 'icon' => 'description'],
        ['label' => __('asic-nav.payment'), 'route' => 'participant.payment.checkout', 'icon' => 'payments'],
        ['label' => __('asic-nav.documents'), 'route' => 'participant.documents', 'icon' => 'folder'],
        ['label' => __('asic-nav.attendance_qr'), 'route' => 'participant.attendance-qr', 'icon' => 'qr_code_2'],
        ['label' => __('asic-nav.full_paper'), 'route' => 'participant.full-paper', 'icon' => 'upload_file'],
        ['label' => __('asic-nav.certificate'), 'route' => 'participant.certificate', 'icon' => 'workspace_premium'],
        ['label' => __('asic-nav.notifications'), 'route' => 'participant.notifications', 'icon' => 'notifications', 'badge' => '2'],
        ['label' => __('asic-nav.profile'), 'route' => 'participant.profile', 'icon' => 'account_circle'],
    ]
])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header with 5-State Simulator Switcher -->
    <x-layout.page-header title="Payment Status Verification" subtitle="Real-time transaction reconciliation status">
        <!-- Status Switcher Query Pills -->
        <div class="flex items-center gap-1 p-1 bg-surface-container-low rounded-xl border border-surface-container-highest text-xs overflow-x-auto">
            <span class="text-[10px] font-bold text-on-surface-variant px-1.5 uppercase shrink-0">State:</span>
            <a href="{{ route('fe.participant.payment.status', ['state' => 'waiting_payment']) }}" 
               class="px-2 py-1 rounded-lg font-semibold shrink-0 transition {{ $state === 'waiting_payment' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Waiting Payment
            </a>
            <a href="{{ route('fe.participant.payment.status', ['state' => 'waiting_verification']) }}" 
               class="px-2 py-1 rounded-lg font-semibold shrink-0 transition {{ $state === 'waiting_verification' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Waiting Verification
            </a>
            <a href="{{ route('fe.participant.payment.status', ['state' => 'verified']) }}" 
               class="px-2 py-1 rounded-lg font-semibold shrink-0 transition {{ $state === 'verified' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Verified
            </a>
            <a href="{{ route('fe.participant.payment.status', ['state' => 'rejected']) }}" 
               class="px-2 py-1 rounded-lg font-semibold shrink-0 transition {{ $state === 'rejected' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Rejected
            </a>
            <a href="{{ route('fe.participant.payment.status', ['state' => 'failed']) }}" 
               class="px-2 py-1 rounded-lg font-semibold shrink-0 transition {{ $state === 'failed' ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Failed
            </a>
        </div>
    </x-layout.page-header>

    <!-- Main Status Card -->
    <x-ui.card>
        <div class="text-center py-6 space-y-4">
            <!-- Icon -->
            <div class="w-16 h-16 mx-auto rounded-2xl {{ $cfg['iconBg'] }} flex items-center justify-center shadow-sm">
                <span class="material-symbols-outlined text-[36px]">{{ $cfg['icon'] }}</span>
            </div>

            <!-- State Tag & Title -->
            <div class="space-y-1">
                <x-ui.badge :variant="$cfg['badge']" dot class="mb-1">
                    {{ strtoupper(str_replace('_', ' ', $state)) }}
                </x-ui.badge>
                <h2 class="text-xl sm:text-2xl font-bold text-on-surface font-display">{{ $cfg['title'] }}</h2>
                <p class="text-xs text-on-surface-variant max-w-lg mx-auto leading-relaxed">{{ $cfg['desc'] }}</p>
            </div>

            <!-- Alert Notice -->
            <div class="max-w-lg mx-auto text-left">
                <x-ui.alert :variant="$cfg['badge']">
                    {{ $cfg['alert'] }}
                </x-ui.alert>
            </div>

            <!-- Transaction Detail Breakdown Table -->
            <div class="max-w-lg mx-auto p-4 rounded-xl bg-surface-container-low border border-surface-container-highest text-xs text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Invoice Reference:</span>
                    <span class="font-mono font-bold text-on-surface">#INV-2027-0089-PRS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Participant Name:</span>
                    <span class="font-bold text-on-surface">Prof. Dr. Ahmad Fauzi</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Amount Paid:</span>
                    <span class="font-mono font-bold text-primary text-sm">IDR 1.500.000</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Payment Method:</span>
                    <span class="font-semibold text-on-surface">QRIS Instant Settlement</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Transaction Time:</span>
                    <span class="font-mono text-on-surface">2027-05-18 10:14:28 WIB</span>
                </div>
            </div>

            <!-- Contextual CTA Buttons -->
            <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                @if($state === 'verified')
                    <x-ui.button variant="primary" href="{{ route('fe.participant.documents') }}" iconLeft="receipt_long">
                        Download Official Receipt (Kuitansi)
                    </x-ui.button>
                    <x-ui.button variant="outline" href="{{ route('fe.participant.attendance-qr') }}" iconLeft="qr_code_2">
                        View Attendance QR
                    </x-ui.button>
                @elseif($state === 'waiting_payment')
                    <x-ui.button variant="primary" href="{{ route('fe.participant.payment.checkout') }}" iconLeft="payments">
                        Complete Payment Now
                    </x-ui.button>
                @elseif($state === 'waiting_verification')
                    <x-ui.button variant="outline" href="{{ route('fe.participant.dashboard') }}" iconLeft="space_dashboard">
                        Return to Dashboard
                    </x-ui.button>
                @else
                    <x-ui.button variant="danger" href="{{ route('fe.participant.payment.checkout') }}" iconLeft="refresh">
                        Retry Payment Transaction
                    </x-ui.button>
                    <x-ui.button variant="outline" href="{{ route('fe.participant.refund') }}" iconLeft="contact_support">
                        Request Refund / Assistance
                    </x-ui.button>
                @endif
            </div>
        </div>
    </x-ui.card>
</div>
@endsection

