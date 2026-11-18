@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => __('asic-notifications.page.title'), 'title' => __('asic-notifications.page.title')])

@section('content')
@include('partials.notifications-feed', ['withAnnouncements' => false, 'subtitle' => __('asic-notifications.page.subtitle_staff')])
@endsection
