@php($t = fn ($k, $r = []) => __('asic-notifications.page.'.$k, $r))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-notifications.page.title'), 'nav' => $participantNav, 'title' => __('asic-notifications.page.title')])

@section('content')
@include('partials.notifications-feed', ['withAnnouncements' => true])
@endsection
