@extends('layouts.auth', ['title' => __('asic-auth.participant_portal') . ' - ASIC 2027', 'photo' => 'images/asic/photos/farmer-field.jpg'])

@section('content')
@if (session('registered_email') || request('registered'))
    <div role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-[#cfe3d5] bg-[#E6F4EA] p-4 text-sm text-[#0B5D2E]">
        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">mark_email_read</span>
        <div><p class="font-bold">{{ __('asic-auth.registered_title') }}</p>
            <p class="mt-0.5 text-[13px] leading-relaxed">{{ session('registered_email_sent') ? __('asic-auth.registered_sent', ['email' => session('registered_email')]) : __('asic-auth.registered_plain') }}</p></div>
    </div>
@endif
@include('auth._login_form', [
    'badge' => __('asic-auth.participant_portal'), 'icon' => 'person', 'heading' => __('asic-auth.participant_welcome'),
    'desc' => __('asic-auth.participant_desc'), 'emailLabel' => __('asic-auth.email_label'), 'emailPlaceholder' => __('asic-auth.email_placeholder'),
])
<p class="mt-6 border-t border-surface-container-highest pt-5 text-center text-[15px] text-on-surface-variant">
    {{ __('asic-auth.no_account') }} <a href="{{ route('auth.participant.register') }}" class="font-bold text-primary hover:underline">{{ __('asic-auth.register_now') }}</a>
</p>
@endsection
