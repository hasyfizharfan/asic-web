@extends('layouts.auth', ['title' => __('asic-auth.participant_portal') . ' - ASIC 2027', 'photo' => 'images/asic/photos/farmer-field.jpg'])

@section('content')
@include('auth._login_form', [
    'badge' => __('asic-auth.participant_portal'), 'icon' => 'person', 'heading' => __('asic-auth.participant_welcome'),
    'desc' => __('asic-auth.participant_desc'), 'emailLabel' => __('asic-auth.email_label'), 'emailPlaceholder' => __('asic-auth.email_placeholder'),
])
<p class="mt-6 border-t border-surface-container-highest pt-5 text-center text-[15px] text-on-surface-variant">
    {{ __('asic-auth.no_account') }} <a href="{{ route('auth.participant.register') }}" class="font-bold text-primary hover:underline">{{ __('asic-auth.register_now') }}</a>
</p>
@endsection
