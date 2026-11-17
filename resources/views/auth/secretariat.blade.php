@extends('layouts.auth', ['title' => __('asic-auth.secretariat_portal') . ' - ASIC 2027', 'photo' => 'images/asic/photos/audience.jpg', 'asideTitle' => __('asic-auth.split.staff_title')])

@section('content')
@include('auth._login_form', [
    'badge' => __('asic-auth.secretariat_portal'), 'icon' => 'badge', 'heading' => __('asic-auth.secretariat_welcome'),
    'desc' => __('asic-auth.secretariat_desc'), 'emailLabel' => __('asic-auth.staff_email_label'), 'emailPlaceholder' => __('asic-auth.staff_email_placeholder'),
])
@endsection
