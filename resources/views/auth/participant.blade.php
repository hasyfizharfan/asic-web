@extends('layouts.auth', ['title' => __('asic-auth.participant_portal') . ' - ASIC 2027', 'photo' => 'images/asic/photos/farmer-field.jpg'])

@section('content')
@php
    $verifyStatus = session('verify_status');
    $banner = match (true) {
        $verifyStatus === 'verified' => ['ok', 'verified', __('asic-auth.verify.verified_title'), __('asic-auth.verify.verified_body')],
        $verifyStatus === 'invalid' => ['warn', 'link_off', __('asic-auth.verify.invalid_title'), __('asic-auth.verify.invalid_body')],
        (bool) session('registered_needs_verification') => ['ok', 'mark_email_unread', __('asic-auth.verify.title'), __('asic-auth.verify.sent', ['email' => session('registered_email')])],
        (bool) (session('registered_email') || request('registered')) => ['ok', 'mark_email_read', __('asic-auth.registered_title'), session('registered_email_sent') ? __('asic-auth.registered_sent', ['email' => session('registered_email')]) : __('asic-auth.registered_plain')],
        default => null,
    };
@endphp
@if ($banner)
    <div role="status" class="mb-5 flex items-start gap-3 rounded-xl border p-4 text-sm {{ $banner[0] === 'ok' ? 'border-[#cfe3d5] bg-[#E6F4EA] text-[#0B5D2E]' : 'border-[#F5C518]/50 bg-[#FEF6D8] text-[#5c4600]' }}">
        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">{{ $banner[1] }}</span>
        <div><p class="font-bold">{{ $banner[2] }}</p><p class="mt-0.5 text-[13px] leading-relaxed">{{ $banner[3] }}</p></div>
    </div>
@endif
@include('auth._login_form', [
    'badge' => __('asic-auth.participant_portal'), 'icon' => 'person', 'heading' => __('asic-auth.participant_welcome'),
    'desc' => __('asic-auth.participant_desc'), 'emailLabel' => __('asic-auth.email_label'), 'emailPlaceholder' => __('asic-auth.email_placeholder'),
])
<div data-resend-verification class="{{ in_array(session('verify_status'), ['invalid'], true) || session('registered_needs_verification') ? '' : 'hidden' }} mt-4 rounded-xl border border-surface-container-highest bg-surface-container-low p-3 text-center">
    <button type="button" data-resend-btn data-label="{{ __('asic-auth.verify.resend') }}" data-busy="{{ __('asic-auth.verify.resending') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:underline"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">forward_to_inbox</span><span>{{ __('asic-auth.verify.resend') }}</span></button>
    <p data-resend-msg class="mt-1 hidden text-xs text-on-surface-variant"></p>
</div>
<p class="mt-6 border-t border-surface-container-highest pt-5 text-center text-[15px] text-on-surface-variant">
    {{ __('asic-auth.no_account') }} <a href="{{ route('auth.participant.register') }}" class="font-bold text-primary hover:underline">{{ __('asic-auth.register_now') }}</a>
</p>
@endsection
