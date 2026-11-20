@extends('layouts.auth', ['title' => __('asic-auth.treasurer_portal') . ' - ASIC 2027', 'photo' => 'images/asic/photos/conference-hall.jpg', 'asideTitle' => __('asic-auth.split.staff_title')])

@section('content')
@include('auth._login_form', [
    'badge' => __('asic-auth.treasurer_portal'), 'icon' => 'account_balance', 'heading' => __('asic-auth.treasurer_welcome'),
    'desc' => __('asic-auth.treasurer_desc'), 'emailLabel' => __('asic-auth.treasurer_email_label'), 'emailPlaceholder' => __('asic-auth.treasurer_email_placeholder'),
])
@endsection
