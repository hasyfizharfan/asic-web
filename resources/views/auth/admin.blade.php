@extends('layouts.auth', ['title' => __('asic-auth.admin_portal') . ' - ASIC 2027', 'photo' => 'images/asic/photos/conference-hall.jpg', 'asideTitle' => __('asic-auth.split.staff_title')])

@section('content')
@include('auth._login_form', [
    'badge' => __('asic-auth.admin_portal'), 'icon' => 'admin_panel_settings', 'heading' => __('asic-auth.admin_welcome'),
    'desc' => __('asic-auth.admin_desc'), 'emailLabel' => __('asic-auth.admin_email_label'), 'emailPlaceholder' => __('asic-auth.admin_email_placeholder'),
])
@endsection
