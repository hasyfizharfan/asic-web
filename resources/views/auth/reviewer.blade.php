@extends('layouts.auth', ['title' => __('asic-reviewer.portal_title') . ' - ASIC 2027', 'photo' => 'images/asic/photos/smart-farming-lab.jpg', 'asideTitle' => __('asic-auth.split.staff_title')])

@section('content')
@include('auth._login_form', [
    'badge' => __('asic-reviewer.portal_title'), 'icon' => 'rate_review', 'heading' => __('asic-reviewer.login_card_title'),
    'desc' => __('asic-auth.split.reviewer_desc'), 'emailLabel' => __('asic-reviewer.email_label'), 'emailPlaceholder' => __('asic-reviewer.email_placeholder'),
])
@endsection
