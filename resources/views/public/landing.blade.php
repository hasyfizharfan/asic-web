@extends('layouts.public', ['title' => __('asic-landing.title')])

@section('content')
@php
    $countdownText = $asic['countdown_status'] ?? __('asic-landing.strip_tag');
    $fees = [
        'student_id' => $asic['fee_student_id'] ?? 'IDR TBA',
        'student_intl' => $asic['fee_student_intl'] ?? 'USD TBA',
        'regular_id' => $asic['fee_regular_id'] ?? 'IDR TBA',
        'regular_intl' => $asic['fee_regular_intl'] ?? 'USD TBA',
    ];
@endphp

<!-- Hero Section with Integrated Navbar matching Image 2 & Prototype w__landing.html -->
<header id="top" class="relative text-white overflow-hidden flex flex-col justify-between"
        style="background-color: #0A2B19;">
    
    <!-- SVG Hero Scenic Vector Art (Mountain + Layered Terraces with #F5C518 Contours) -->
    @include('public.partials.hero-art')

    <!-- Top Integrated Navigation Bar (Matches Image 2 & Prototype w__landing.html exactly) -->
    <nav x-data="{ mobileOpen: false }" class="relative z-20 w-full px-6 lg:px-16 py-4 sm:py-5 flex items-center justify-between">
        <!-- Logo & Subtitle -->
        <a href="{{ route('fe.home') }}" class="flex flex-col group select-none">
            <span class="text-2xl font-bold tracking-tight text-white font-display leading-none">ASIC 2027</span>
            <span class="text-[10px] text-white/75 tracking-[0.2em] font-semibold mt-1 uppercase">UNIVERSITAS ANDALAS</span>
        </a>

        <!-- Desktop Links with Proper Spacing (Beranda, Tentang, Pengiriman Abstrak, Biaya, Kontak) -->
        <div class="hidden md:flex items-center text-sm font-medium" style="gap: 28px;">
            <a href="{{ route('fe.home') }}" class="text-white/85 hover:text-white transition px-1 py-1">{{ __('asic-landing.nav_home') }}</a>
            <a href="#about" class="text-white/85 hover:text-white transition px-1 py-1">{{ __('asic-landing.nav_about') }}</a>
            <a href="#cfa" class="text-white/85 hover:text-white transition px-1 py-1">{{ __('asic-landing.nav_cfa') }}</a>
            <a href="#fees" class="text-white/85 hover:text-white transition px-1 py-1">{{ __('asic-landing.nav_fees') }}</a>
            <a href="#contact" class="text-white/85 hover:text-white transition px-1 py-1">{{ __('asic-landing.nav_contact') }}</a>
        </div>

        <!-- Right Side: Segmented Lang Pill [ID | EN] + Masuk / Login + [Daftar / Register] Button -->
        <div class="hidden md:flex items-center" style="gap: 20px;">
            <div class="flex items-center p-0.5 border border-white/25 rounded-xl bg-black/25 text-xs">
                <a href="{{ route('fe.lang.switch', 'id') }}" 
                   class="px-2.5 py-1 rounded-lg font-bold transition {{ app()->getLocale() === 'id' ? 'bg-[#F5C518] text-[#17261D] shadow-xs' : 'text-white/80 hover:text-white' }}">
                    ID
                </a>
                <a href="{{ route('fe.lang.switch', 'en') }}" 
                   class="px-2.5 py-1 rounded-lg font-bold transition {{ app()->getLocale() === 'en' ? 'bg-[#F5C518] text-[#17261D] shadow-xs' : 'text-white/80 hover:text-white' }}">
                    EN
                </a>
            </div>

            <a href="{{ route('fe.participant.login') }}" class="text-sm font-semibold text-white/90 hover:text-white transition">
                {{ __('asic-landing.btn_login') }}
            </a>

            <a href="{{ route('fe.participant.login') }}" class="px-5 py-2 rounded-xl bg-[#F5C518] hover:bg-[#fed649] text-[#17261D] font-bold text-sm shadow transition active:scale-95">
                {{ __('asic-landing.btn_register') }}
            </a>
        </div>

        <!-- Mobile Menu Trigger -->
        <div class="flex items-center gap-3 md:hidden">
            <div class="flex items-center p-0.5 border border-white/25 rounded-xl bg-black/25 text-xs">
                <a href="{{ route('fe.lang.switch', 'id') }}" 
                   class="px-2 py-0.5 rounded-lg font-bold transition {{ app()->getLocale() === 'id' ? 'bg-[#F5C518] text-[#17261D]' : 'text-white/80' }}">
                    ID
                </a>
                <a href="{{ route('fe.lang.switch', 'en') }}" 
                   class="px-2 py-0.5 rounded-lg font-bold transition {{ app()->getLocale() === 'en' ? 'bg-[#F5C518] text-[#17261D]' : 'text-white/80' }}">
                    EN
                </a>
            </div>
            <button @click="mobileOpen = !mobileOpen" type="button" class="p-2 rounded-lg text-white hover:bg-white/10 transition" aria-label="Toggle Navigation">
                <span class="material-symbols-outlined" x-text="mobileOpen ? 'close' : 'menu'">menu</span>
            </button>
        </div>

        <!-- Mobile Dropdown -->
        <div x-show="mobileOpen" 
             @click.outside="mobileOpen = false"
             x-transition 
             class="absolute top-full left-4 right-4 mt-2 p-4 rounded-2xl bg-[#0F3D24] border border-white/20 shadow-2xl space-y-3 z-50 md:hidden"
             style="display: none;">
            <a @click="mobileOpen = false" href="{{ route('fe.home') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-white hover:bg-white/10">{{ __('asic-landing.nav_home') }}</a>
            <a @click="mobileOpen = false" href="#about" class="block px-3 py-2 rounded-lg text-sm font-medium text-white hover:bg-white/10">{{ __('asic-landing.nav_about') }}</a>
            <a @click="mobileOpen = false" href="#cfa" class="block px-3 py-2 rounded-lg text-sm font-medium text-white hover:bg-white/10">{{ __('asic-landing.nav_cfa') }}</a>
            <a @click="mobileOpen = false" href="#fees" class="block px-3 py-2 rounded-lg text-sm font-medium text-white hover:bg-white/10">{{ __('asic-landing.nav_fees') }}</a>
            <a @click="mobileOpen = false" href="#contact" class="block px-3 py-2 rounded-lg text-sm font-medium text-white hover:bg-white/10">{{ __('asic-landing.nav_contact') }}</a>
            <div class="pt-2 border-t border-white/15 flex flex-col gap-2">
                <a href="{{ route('fe.participant.login') }}" class="flex items-center justify-center px-4 py-2 rounded-lg border border-white/30 text-white font-semibold text-xs">
                    {{ __('asic-landing.btn_login') }}
                </a>
                <a href="{{ route('fe.participant.login') }}" class="flex items-center justify-center px-4 py-2 rounded-lg bg-[#F5C518] text-[#17261D] font-bold text-xs shadow">
                    {{ __('asic-landing.btn_register') }}
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Main Typography (Scaled to comfortably show CTA buttons above the fold) -->
    <div class="relative z-10 max-w-4xl px-6 lg:px-16 pt-2 sm:pt-4 pb-16 sm:pb-24 lg:pb-28">
        <div class="text-xs sm:text-sm font-bold tracking-[0.18em] text-[#F5C518] uppercase mb-2.5">
            {{ __('asic-landing.location_badge') }}
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-[50px] font-bold font-display tracking-tight text-white leading-[1.08] mb-3 sm:mb-4">
            @if(app()->getLocale() === 'id')
                Konferensi<br>
                Internasional<br>
                Sistem Agrifood
            @else
                Agrifood System<br>
                International<br>
                Conference
            @endif
        </h1>

        <p class="text-sm sm:text-base text-white/90 max-w-2xl leading-relaxed mb-5 font-normal">
            {{ __('asic-landing.hero_lead') }}
        </p>

        <!-- CTA Buttons matching Image 2 & Prototype w__landing.html -->
        <div class="flex items-center gap-4 flex-wrap">
            <a href="{{ route('fe.participant.login') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#F5C518] hover:bg-[#fed649] text-[#0F3D24] font-bold text-sm sm:text-base shadow-xl active:scale-95 transition">
                <span>{{ __('asic-landing.btn_register') }}</span>
            </a>
            <a href="#cfa" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-white/60 hover:border-white text-white font-semibold text-sm sm:text-base hover:bg-white/10 active:scale-95 transition">
                <span>{{ __('asic-landing.btn_submit_abstract') }}</span>
            </a>
        </div>
    </div>
</header>

<!-- Countdown & Date Announcement Strip -->
<div class="text-white border-t-4 border-[#F5C518] py-6 px-6 lg:px-16 shadow-md" style="background-color: #0F3D24;">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#0A2B19]/60 border border-[#F5C518]/30 flex items-center justify-center shrink-0 text-[#F5C518]">
                <svg width="28" height="28" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="5" width="20" height="19" rx="2"/>
                    <path d="M8 2v5M18 2v5M3 11h20"/>
                    <circle cx="21" cy="21" r="6" fill="#0F3D24"/>
                    <path d="M21 18v3.5l2 1.2"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold font-display text-white tracking-tight">{{ __('asic-landing.strip_title') }}</h2>
                <p class="text-xs sm:text-sm text-white/80 mt-0.5">{{ __('asic-landing.strip_desc') }}</p>
            </div>
        </div>

        <div class="inline-flex items-center gap-2 self-start md:self-auto px-4 py-2 rounded-lg bg-[#0A2B19]/80 border border-[#F5C518]/40 text-[#F5C518] font-mono text-xs font-bold tracking-wider">
            <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
            <span>{{ $countdownText }}</span>
        </div>
    </div>
</div>

<!-- Section: About & Facts -->
<section id="about" class="py-20 lg:py-28 px-6 lg:px-16 bg-[#F1F5EC] text-[#17261D]">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-display tracking-tight text-[#17261D] max-w-2xl leading-tight">
            {{ __('asic-landing.about_title') }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12 pt-8 border-t-2 border-[#0F3D24]">
            <div class="space-y-1">
                <small class="block text-xs uppercase font-semibold tracking-wider text-[#0F3D24]/70">{{ __('asic-landing.fact_organizer_label') }}</small>
                <b class="block text-lg sm:text-xl font-bold font-display text-[#0F3D24]">{{ __('asic-landing.fact_organizer_val') }}</b>
            </div>
            <div class="space-y-1">
                <small class="block text-xs uppercase font-semibold tracking-wider text-[#0F3D24]/70">{{ __('asic-landing.fact_location_label') }}</small>
                <b class="block text-lg sm:text-xl font-bold font-display text-[#0F3D24]">{{ __('asic-landing.fact_location_val') }}</b>
            </div>
            <div class="space-y-1">
                <small class="block text-xs uppercase font-semibold tracking-wider text-[#0F3D24]/70">{{ __('asic-landing.fact_format_label') }}</small>
                <b class="block text-lg sm:text-xl font-bold font-display text-[#0F3D24]">{{ __('asic-landing.fact_format_val') }}</b>
            </div>
        </div>
    </div>
</section>

<!-- Section: Call for Abstracts & Research Themes -->
<section id="cfa" class="py-20 lg:py-28 px-6 lg:px-16 bg-white text-[#17261D] border-y border-[#d9e3f6]">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-display tracking-tight text-[#17261D] leading-tight mb-10">
            {{ __('asic-landing.themes_title') }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-14 gap-y-4">
            <div class="py-4 border-b border-[#c9d6c2]">
                <p class="text-lg font-bold text-[#0F3D24] font-display flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518]"></span>
                    <span>{{ __('asic-landing.track_1') }}</span>
                </p>
            </div>
            <div class="py-4 border-b border-[#c9d6c2]">
                <p class="text-lg font-bold text-[#0F3D24] font-display flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518]"></span>
                    <span>{{ __('asic-landing.track_2') }}</span>
                </p>
            </div>
            <div class="py-4 border-b border-[#c9d6c2]">
                <p class="text-lg font-bold text-[#0F3D24] font-display flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518]"></span>
                    <span>{{ __('asic-landing.track_3') }}</span>
                </p>
            </div>
            <div class="py-4 border-b border-[#c9d6c2]">
                <p class="text-lg font-bold text-[#0F3D24] font-display flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518]"></span>
                    <span>{{ __('asic-landing.track_4') }}</span>
                </p>
            </div>
            <div class="py-4 border-b border-[#c9d6c2] md:col-span-2">
                <p class="text-lg font-bold text-[#0F3D24] font-display flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518]"></span>
                    <span>{{ __('asic-landing.track_5') }}</span>
                </p>
            </div>
        </div>

        <div class="mt-10">
            <a href="{{ route('fe.participant.login') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-[#F5C518] hover:bg-[#fed649] text-[#0F3D24] font-bold text-base shadow hover:brightness-105 transition">
                <span>{{ __('asic-landing.btn_submit_abstract') }}</span>
                <span class="material-symbols-outlined text-[18px]">upload_file</span>
            </a>
        </div>
    </div>
</section>

<!-- Section: Four Steps to Join -->
<section class="py-20 lg:py-28 px-6 lg:px-16 bg-[#F1F5EC] text-[#17261D]">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-display tracking-tight text-[#17261D] leading-tight mb-14">
            {{ __('asic-landing.steps_title') }}
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 relative">
            <!-- Step 1 -->
            <div class="relative pt-12">
                <div class="absolute top-0 left-0 w-11 h-11 rounded-full bg-[#0F3D24] text-[#F5C518] flex items-center justify-center font-bold text-lg font-display shadow-md">
                    1
                </div>
                <h3 class="text-lg font-bold text-[#0F3D24] font-display mb-2">{{ __('asic-landing.step_1_title') }}</h3>
                <p class="text-sm text-[#3b4a40] leading-relaxed">{{ __('asic-landing.step_1_desc') }}</p>
            </div>

            <!-- Step 2 -->
            <div class="relative pt-12">
                <div class="absolute top-0 left-0 w-11 h-11 rounded-full bg-[#0F3D24] text-[#F5C518] flex items-center justify-center font-bold text-lg font-display shadow-md">
                    2
                </div>
                <h3 class="text-lg font-bold text-[#0F3D24] font-display mb-2">{{ __('asic-landing.step_2_title') }}</h3>
                <p class="text-sm text-[#3b4a40] leading-relaxed">{{ __('asic-landing.step_2_desc') }}</p>
            </div>

            <!-- Step 3 -->
            <div class="relative pt-12">
                <div class="absolute top-0 left-0 w-11 h-11 rounded-full bg-[#0F3D24] text-[#F5C518] flex items-center justify-center font-bold text-lg font-display shadow-md">
                    3
                </div>
                <h3 class="text-lg font-bold text-[#0F3D24] font-display mb-2">{{ __('asic-landing.step_3_title') }}</h3>
                <p class="text-sm text-[#3b4a40] leading-relaxed">{{ __('asic-landing.step_3_desc') }}</p>
            </div>

            <!-- Step 4 -->
            <div class="relative pt-12">
                <div class="absolute top-0 left-0 w-11 h-11 rounded-full bg-[#0F3D24] text-[#F5C518] flex items-center justify-center font-bold text-lg font-display shadow-md">
                    4
                </div>
                <h3 class="text-lg font-bold text-[#0F3D24] font-display mb-2">{{ __('asic-landing.step_4_title') }}</h3>
                <p class="text-sm text-[#3b4a40] leading-relaxed">{{ __('asic-landing.step_4_desc') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Registration Fees -->
<section id="fees" class="py-20 lg:py-28 px-6 lg:px-16 text-white" style="background-color: #0F3D24;">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-display tracking-tight text-white leading-tight mb-8">
            {{ __('asic-landing.fees_title') }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-14 gap-y-2 mt-8">
            <div class="flex items-center justify-between py-4 border-b border-white/20 text-base sm:text-lg">
                <span class="text-white/90">{{ __('asic-landing.fee_student_domestic') }}</span>
                <b class="text-[#F5C518] font-mono font-bold">{{ $fees['student_id'] }}</b>
            </div>
            <div class="flex items-center justify-between py-4 border-b border-white/20 text-base sm:text-lg">
                <span class="text-white/90">{{ __('asic-landing.fee_student_intl') }}</span>
                <b class="text-[#F5C518] font-mono font-bold">{{ $fees['student_intl'] }}</b>
            </div>
            <div class="flex items-center justify-between py-4 border-b border-white/20 text-base sm:text-lg">
                <span class="text-white/90">{{ __('asic-landing.fee_regular_domestic') }}</span>
                <b class="text-[#F5C518] font-mono font-bold">{{ $fees['regular_id'] }}</b>
            </div>
            <div class="flex items-center justify-between py-4 border-b border-white/20 text-base sm:text-lg">
                <span class="text-white/90">{{ __('asic-landing.fee_regular_intl') }}</span>
                <b class="text-[#F5C518] font-mono font-bold">{{ $fees['regular_intl'] }}</b>
            </div>
        </div>
    </div>
</section>
@endsection
