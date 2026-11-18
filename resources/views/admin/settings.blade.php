@php($t = trans('asic-admin.settings_page'))
@extends('layouts.portal', ['title' => $t['title'], 'role' => 'admin', 'roleName' => $t['title'], 'nav' => $adminNav])

@section('content')
<main class="space-y-5">
    <header><h1 class="text-2xl font-bold">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>
    @include('settings._periods_payment')
</main>
@endsection
