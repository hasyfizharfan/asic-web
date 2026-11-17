<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Login - ASIC 2027' }}</title>
    @include('partials.fonts')
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/asic/logo.svg') }}">
    @include('partials.asic-i18n')
    @vite(['resources/css/asic.css', 'resources/js/asic.js'])
    @stack('styles')
</head>
@php
    $authEvent = app(\App\Services\PublicContent::class)->activeEvent();
    $authPhoto = $photo ?? 'images/asic/photos/farmer-field.jpg';
    $A = trans('asic-auth.split');
@endphp
<body class="min-h-screen bg-[#F7FAF8] font-sans text-on-surface antialiased selection:bg-primary selection:text-white">
<div class="grid min-h-screen lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
    {{-- Panel foto (desktop) --}}
    <aside class="relative hidden overflow-hidden lg:block">
        <img src="{{ asset($authPhoto) }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-[#072b1b] via-[#072b1b]/70 to-[#072b1b]/20" aria-hidden="true"></div>
        <div class="relative flex h-full flex-col justify-between p-10 text-white xl:p-14">
            <a href="{{ route('portal.landing') }}" class="flex w-fit items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/95 p-1.5"><img src="{{ asset('images/asic/logo.svg') }}" alt="" class="h-full w-full"></span>
                <span class="leading-tight"><span class="block font-serif text-2xl font-semibold">ASIC 2027</span><span class="text-xs tracking-[0.2em] text-white/80">UNIVERSITAS ANDALAS</span></span>
            </a>
            <div class="max-w-md">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#F5C518]">{{ $A['eyebrow'] }}</p>
                <h2 class="mt-3 font-serif text-4xl font-semibold leading-tight xl:text-5xl">{{ $asideTitle ?? $A['title'] }}</h2>
                <ul class="mt-8 space-y-3 text-[15px] text-white/90">
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-[#F5C518]">calendar_month</span>{{ $authEvent ? \App\Support\DateRange::format($authEvent->tanggal_mulai, $authEvent->tanggal_selesai) : __('asic-landing.hero_info.dates_default') }}</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-[#F5C518]">location_on</span>{{ $authEvent?->lokasi ?: __('asic-landing.hero_info.venue_default') }}</li>
                    <li class="flex items-center gap-3"><span class="material-symbols-outlined text-[#F5C518]">groups</span>{{ $A['hybrid'] }}</li>
                </ul>
            </div>
            <p class="text-xs text-white/60">© {{ date('Y') }} ASIC 2027 · {{ __('asic-landing.fact_organizer_val') }}</p>
        </div>
    </aside>

    {{-- Panel form --}}
    <div class="flex min-h-screen flex-col">
        <header class="flex items-center justify-between px-5 py-2.5 sm:px-10">
            <a href="{{ route('portal.landing') }}" class="inline-flex items-center gap-2 rounded-lg py-2 text-sm font-semibold text-primary hover:text-[#168348]">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>{{ __('asic-auth.back_to_home') }}
            </a>
            <x-layout.lang-switcher theme="light" />
        </header>
        <main class="flex flex-1 items-center justify-center px-5 pb-4 sm:px-10">
            <div class="w-full {{ $formWidth ?? 'max-w-md' }}">
                @yield('content')
            </div>
        </main>
    </div>
</div>
<x-ui.toast />
@stack('scripts')
</body>
</html>
