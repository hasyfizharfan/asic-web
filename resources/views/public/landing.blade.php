@extends('layouts.public', ['title' => __('asic-landing.title')])

@section('content')
@php
    $L = trans('asic-landing.sec');
    $locale = app()->getLocale();
    $heroDates = $event ? \App\Support\DateRange::format($event->tanggal_mulai, $event->tanggal_selesai) : __('asic-landing.hero_info.dates_default');
    $edition = $event?->nama_event ?: 'ASIC 2027';
    $scopes = config('abstract-scopes', []);
    $scopeText = $scopeBlocks->mapWithKeys(fn ($b) => [mb_strtolower((string) $b->localized('judul')) => $b])->all();
    $navLinks = [
        ['#about', __('asic-landing.nav_about')], ['#cfa', __('asic-landing.nav_cfa')],
        ['#program', $L['nav_program'], $programDays->isNotEmpty()], ['#speakers', $L['nav_speakers'], $speakers->isNotEmpty()],
        ['#fees', __('asic-landing.nav_fees')], ['#venue', $L['nav_venue']], ['#committee', $L['nav_committee'], $committeeBlocks->isNotEmpty(), 'wide'],
        ['#sponsorship', $L['nav_sponsorship'], true, 'wide'], ['#contact', __('asic-landing.nav_contact')],
    ];
    $navLinks = array_values(array_filter($navLinks, fn ($l) => $l[2] ?? true));
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim(preg_replace('/\b(Prof|Dr|Assoc|Ir|M\.?Sc|Ph\.?D)\.?/i', '', $name))))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $modeLabel = fn (string $m) => $L['mode'][$m] ?? $m;
    $typeLabel = fn (string $t) => $L['type'][$t] ?? $t;
@endphp

@if($isDemo)
    <div class="bg-[#F5C518] px-6 py-2 text-center text-xs font-semibold text-[#182820]" role="note">{{ $L['demo_banner'] }}</div>
@endif

{{-- HERO (Figma "Conference hero" 4:90) --}}
<header id="top" class="relative overflow-hidden bg-[#0f3d24] text-white">
    <img src="{{ asset('images/asic/hero-rice-terraces.jpg') }}" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover" fetchpriority="high">
    <div class="absolute inset-0 bg-gradient-to-r from-[rgba(7,43,27,0.94)] to-[rgba(9,43,27,0.27)]" aria-hidden="true"></div>

    <nav x-data="{ open: false }" class="relative z-20 flex h-20 items-center justify-between gap-6 px-6 lg:h-24 lg:px-10" aria-label="{{ $L['nav_label'] }}">
        <a href="#top" class="flex shrink-0 flex-col leading-none">
            <span class="font-poppins text-[26px] font-bold">ASIC 2027</span>
            <span class="mt-1 text-[10px] font-semibold tracking-[1.7px] text-white/85">UNIVERSITAS ANDALAS</span>
        </a>
        <div class="hidden items-center gap-1 text-[15px] font-medium xl:flex">
            @foreach($navLinks as $link)
                <a href="{{ $link[0] }}" class="{{ ($link[3] ?? null) === 'wide' ? 'hidden min-[1400px]:inline-flex' : 'inline-flex' }} whitespace-nowrap rounded-lg px-2.5 py-2 hover:bg-white/10 hover:text-[#F5C518]">{{ $link[1] }}</a>
            @endforeach
        </div>
        <div class="hidden items-center gap-4 xl:flex">
            <div class="flex rounded-xl border border-[#dce4da] p-1 text-[13px] font-bold">
                <a href="{{ route('portal.language', 'id') }}" class="rounded-lg px-3 py-2 {{ $locale === 'id' ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white text-[#627066]' }}">ID</a>
                <a href="{{ route('portal.language', 'en') }}" class="rounded-lg px-3 py-2 {{ $locale === 'en' ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white text-[#627066]' }}">EN</a>
            </div>
            <a href="{{ route('login') }}" class="rounded-lg px-2 py-2 text-[15px] font-semibold hover:text-[#F5C518]">{{ __('asic-landing.btn_login') }}</a>
            <a href="{{ route('auth.participant.register') }}" class="rounded-xl bg-[#F5C518] px-5 py-3 text-[15px] font-bold text-[#0f3d24] hover:bg-[#fed649]">{{ __('asic-landing.btn_register') }}</a>
        </div>
        <button type="button" @click="open = !open" class="rounded-lg p-2 hover:bg-white/10 xl:hidden" :aria-expanded="open.toString()" aria-controls="mobile-nav" aria-label="{{ $L['menu'] }}">
            <span class="material-symbols-outlined" x-text="open ? 'close' : 'menu'">menu</span>
        </button>
        <div id="mobile-nav" x-show="open" x-transition @click.outside="open = false" class="absolute left-4 right-4 top-full z-50 space-y-1 rounded-2xl border border-white/20 bg-[#0B5D2E] p-4 shadow-2xl xl:hidden" style="display: none;">
            @foreach($navLinks as $link)
                <a href="{{ $link[0] }}" @click="open = false" class="block rounded-lg px-3 py-2.5 text-base hover:bg-white/10">{{ $link[1] }}</a>
            @endforeach
            <div class="flex items-center gap-2 border-t border-white/15 pt-3">
                <a href="{{ route('portal.language', 'id') }}" class="rounded-lg px-3 py-1.5 text-xs font-bold {{ $locale === 'id' ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white/10' }}">ID</a>
                <a href="{{ route('portal.language', 'en') }}" class="rounded-lg px-3 py-1.5 text-xs font-bold {{ $locale === 'en' ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white/10' }}">EN</a>
                <a href="{{ route('login') }}" class="ml-auto rounded-lg border border-white/30 px-3 py-1.5 text-xs font-semibold">{{ __('asic-landing.btn_login') }}</a>
                <a href="{{ route('auth.participant.register') }}" class="rounded-lg bg-[#F5C518] px-3 py-1.5 text-xs font-bold text-[#0f3d24]">{{ __('asic-landing.btn_register') }}</a>
            </div>
        </div>
    </nav>

    <div class="relative z-10 flex flex-col gap-6 px-6 pb-12 pt-10 lg:px-16 lg:pt-16">
        <p class="flex items-center gap-3 text-[13px] font-semibold uppercase tracking-[2px]"><span class="h-[3px] w-8 bg-[#F5C518]" aria-hidden="true"></span>ASIC 2027 · {{ __('asic-landing.location_badge') }}</p>
        <h1 class="max-w-[1000px] font-poppins text-4xl font-semibold leading-[1.12] tracking-[-1px] sm:text-6xl lg:text-[72px] lg:tracking-[-2.5px]">
            @if($locale === 'id') Konferensi Internasional<br class="hidden sm:block"> Sistem Agrifood @else Agrifood System<br class="hidden sm:block"> International Conference @endif
        </h1>
        <p class="max-w-[720px] text-base leading-relaxed sm:text-lg">{{ __('asic-landing.hero_lead') }}<br>{{ $L['organized_by'] }}</p>
        <dl class="grid max-w-3xl grid-cols-1 gap-4 py-2 sm:grid-cols-3 sm:gap-8">
            <div><dt class="text-xs">{{ __('asic-landing.hero_info.theme') }}</dt><dd class="mt-2 font-poppins text-base font-semibold sm:text-lg">{{ $event?->tema ?: __('asic-landing.hero_info.tba') }}</dd></div>
            <div><dt class="text-xs">{{ __('asic-landing.hero_info.dates') }}</dt><dd class="mt-2 font-poppins text-base font-semibold sm:text-lg">{{ $heroDates }}<span class="block text-sm font-normal text-white/80">{{ __('asic-landing.hero_info.field_trip') }}</span></dd></div>
            <div><dt class="text-xs">{{ __('asic-landing.hero_info.venue') }}</dt><dd class="mt-2 font-poppins text-base font-semibold sm:text-lg">{{ $event?->lokasi ?: __('asic-landing.hero_info.venue_default') }}</dd></div>
        </dl>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('auth.participant.register') }}" class="inline-flex h-12 items-center gap-3 rounded-xl border border-white bg-[#F5C518] px-6 text-sm font-semibold text-[#0f3d24] hover:bg-[#fed649]">{{ __('asic-landing.btn_register') }} <img src="{{ asset('images/asic/icon-arrow-up-right.svg') }}" width="16" height="16" alt=""></a>
            <a href="#cfa" class="inline-flex h-12 items-center gap-3 rounded-xl border border-[#1f6b3a] bg-white px-6 text-sm font-semibold text-[#0f3d24] hover:bg-[#E6F4EA]">{{ __('asic-landing.btn_submit_abstract') }} <img src="{{ asset('images/asic/icon-arrow-up-right.svg') }}" width="16" height="16" alt=""></a>
        </div>
    </div>

    {{-- Hitung mundur: aktif bila tanggal event sudah ada --}}
    <div class="relative z-10 bg-[#0f3d24]" x-data="{
        target: @js($asic['countdown_target']), d: 0, h: 0, m: 0, s: 0, done: false,
        tick() { if (!this.target) return; const diff = new Date(this.target) - new Date(); if (diff <= 0) { this.done = true; return; }
            this.d = Math.floor(diff / 864e5); this.h = Math.floor(diff / 36e5) % 24; this.m = Math.floor(diff / 6e4) % 60; this.s = Math.floor(diff / 1e3) % 60; },
        init() { this.tick(); setInterval(() => this.tick(), 1000); }
    }">
        <div class="flex flex-col gap-4 px-6 py-7 md:flex-row md:items-center md:justify-between lg:px-16">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/asic/icon-calendar-clock.svg') }}" width="32" height="32" alt="">
                <div>
                    <p class="font-poppins text-xl font-semibold">{{ $asic['countdown_target'] ? $L['countdown_title'] : __('asic-landing.strip_title') }}</p>
                    <p class="mt-2 text-sm">{{ $asic['countdown_target'] ? $L['countdown_desc'] : __('asic-landing.strip_desc') }}</p>
                </div>
            </div>
            @if($asic['countdown_target'])
                <p x-show="done" class="text-sm font-semibold uppercase text-[#F5C518]" style="display: none;">{{ $L['countdown_done'] }}</p>
                <div x-show="!done" class="flex gap-3 font-poppins text-[#F5C518]" aria-live="off">
                    @foreach(['d' => 'days', 'h' => 'hours', 'm' => 'minutes', 's' => 'seconds'] as $k => $label)
                        <div class="min-w-[64px] rounded-xl bg-white/10 px-3 py-2 text-center"><span class="block text-2xl font-semibold" x-text="String({{ $k }}).padStart(2, '0')">00</span><span class="text-[11px] uppercase tracking-wider text-white/80">{{ $L['cd'][$label] }}</span></div>
                    @endforeach
                </div>
            @else
                <p class="text-sm font-semibold uppercase text-[#F5C518]">{{ __('asic-landing.strip_tag') }}</p>
            @endif
        </div>
    </div>
</header>

{{-- TENTANG --}}
<section id="about" class="bg-[#F7FAF8] px-6 py-20 lg:px-16">
    <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-[2px] text-[#168348]">{{ __('asic-landing.nav_about') }}</p>
            <h2 class="mt-3 font-poppins text-3xl font-semibold leading-tight text-[#182820] sm:text-4xl">{{ __('asic-landing.about_title') }}</h2>
            <div class="mt-5 space-y-4 text-[#3b4a40]">
                @forelse($aboutBlocks as $block)
                    @if($block->localized('judul'))<h3 class="font-bold text-[#0B5D2E]">{{ $block->localized('judul') }}</h3>@endif
                    <p class="leading-relaxed">{!! nl2br(e($block->localized('isi'))) !!}</p>
                @empty
                    <p class="leading-relaxed">{{ $L['about_default'] }}</p>
                @endforelse
            </div>
        </div>
        <div class="space-y-6">
            <dl class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                @foreach([['apartment', 'fact_organizer'], ['location_on', 'fact_location'], ['devices', 'fact_format']] as [$icon, $key])
                    <div class="flex items-center gap-4 rounded-2xl border border-[#dce4da] bg-white p-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E6F4EA] text-[#0B5D2E]"><span class="material-symbols-outlined">{{ $icon }}</span></span>
                        <div><dt class="text-xs uppercase tracking-wider text-[#66746C]">{{ __('asic-landing.'.$key.'_label') }}</dt><dd class="font-bold text-[#0B5D2E]">{{ __('asic-landing.'.$key.'_val') }}</dd></div>
                    </div>
                @endforeach
            </dl>
            <div class="rounded-2xl bg-[#0B5D2E] p-5 text-white">
                <p class="text-xs font-bold uppercase tracking-wider text-[#F5C518]">{{ $L['history'] }}</p>
                <ol class="mt-3 grid grid-cols-4 gap-2 text-center">
                    @foreach(['1st' => '2018', '2nd' => '2022', '3rd' => '2024', '4th' => '2027'] as $ed => $year)
                        <li class="rounded-xl {{ $loop->last ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white/10' }} px-2 py-3"><span class="block font-poppins text-lg font-semibold">{{ $ed }}</span><span class="text-xs">{{ $year }}</span></li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</section>

{{-- CALL FOR ABSTRACTS: 6 scope resmi panitia --}}
<section id="cfa" class="bg-white px-6 py-20 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[2px] text-[#168348]">{{ __('asic-landing.nav_cfa') }}</p>
                <h2 class="mt-3 font-poppins text-3xl font-semibold text-[#182820] sm:text-4xl">{{ __('asic-landing.themes_title') }}</h2>
                <p class="mt-2 max-w-2xl text-[#66746C]">{{ $L['cfa_lead'] }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('auth.participant.register') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#F5C518] px-5 text-sm font-bold text-[#0f3d24] hover:bg-[#fed649]">{{ __('asic-landing.btn_submit_abstract') }} <span class="material-symbols-outlined text-[18px]">upload_file</span></a>
                @if($pages['downloads'] ?? false)<a href="{{ route('public.downloads') }}" class="inline-flex h-11 items-center gap-2 rounded-xl border border-[#0B5D2E] px-5 text-sm font-bold text-[#0B5D2E] hover:bg-[#E6F4EA]"><span class="material-symbols-outlined text-[18px]">download</span>{{ $L['templates'] }}</a>@endif
            </div>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($scopes as $code => $name)
                @php $extra = $scopeText[mb_strtolower($name)] ?? null; @endphp
                <article class="rounded-2xl border border-[#dce4da] bg-[#F7FAF8] p-5">
                    <span class="inline-flex h-10 w-12 items-center justify-center rounded-xl bg-[#0B5D2E] font-poppins font-semibold text-[#F5C518]">{{ $code }}</span>
                    <h3 class="mt-4 font-bold text-[#182820]">{{ $name }}</h3>
                    @if($extra?->localized('isi'))<p class="mt-2 text-sm text-[#3b4a40]">{{ $extra->localized('isi') }}</p>@endif
                </article>
            @endforeach
            @foreach($scopeBlocks->reject(fn ($b) => in_array(mb_strtolower((string) $b->localized('judul')), array_map('mb_strtolower', $scopes), true)) as $block)
                <article class="rounded-2xl border border-[#dce4da] bg-[#F7FAF8] p-5">
                    <h3 class="font-bold text-[#182820]">{{ $block->localized('judul') }}</h3>
                    @if($block->localized('isi'))<p class="mt-2 text-sm text-[#3b4a40]">{!! nl2br(e($block->localized('isi'))) !!}</p>@endif
                </article>
            @endforeach
        </div>
        <ul class="mt-8 grid gap-3 text-sm text-[#3b4a40] sm:grid-cols-3">
            @foreach($L['cfa_rules'] as $rule)
                <li class="flex gap-2"><span class="material-symbols-outlined text-[18px] text-[#168348]">check_circle</span>{{ $rule }}</li>
            @endforeach
        </ul>
        @if($guideBlocks->isNotEmpty())
            <div class="mt-8 grid gap-4 md:grid-cols-2">
                @foreach($guideBlocks as $block)
                    <article class="rounded-2xl border-l-4 border-[#F5C518] bg-[#FEF6D8]/50 p-5"><h3 class="font-bold text-[#0B5D2E]">{{ $block->localized('judul') ?: __('asic-content.sections.'.$block->section) }}</h3><p class="mt-2 text-sm text-[#3b4a40]">{!! nl2br(e($block->localized('isi'))) !!}</p></article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- PEMBICARA UTAMA --}}
@if($speakers->isNotEmpty())
<section id="speakers" class="bg-[#0B5D2E] px-6 py-20 text-white lg:px-16">
    <div class="mx-auto max-w-6xl">
        <p class="text-xs font-bold uppercase tracking-[2px] text-[#F5C518]">{{ $L['nav_speakers'] }}</p>
        <h2 class="mt-3 font-poppins text-3xl font-semibold sm:text-4xl">{{ $L['speakers_title'] }}</h2>
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($speakers as $speaker)
                <article class="flex gap-4 rounded-2xl bg-white/10 p-5 ring-1 ring-white/15">
                    @if($speaker->foto)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($speaker->foto) }}" alt="" class="h-16 w-16 shrink-0 rounded-full object-cover">
                    @else
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#F5C518] font-poppins text-lg font-semibold text-[#0f3d24]" aria-hidden="true">{{ $initials($speaker->nama_speaker) }}</span>
                    @endif
                    <div>
                        <h3 class="font-poppins font-semibold">{{ $speaker->nama_speaker }}</h3>
                        <p class="text-sm text-white/80">{{ collect([$speaker->institusi, $speaker->negara])->filter()->implode(', ') }}</p>
                        @if($speaker->deskripsi)<p class="mt-2 text-sm italic text-[#F5C518]">“{{ $speaker->deskripsi }}”</p>@endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- TANGGAL PENTING --}}
@if($importantDates->isNotEmpty())
<section id="dates" class="bg-[#F7FAF8] px-6 py-20 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-poppins text-3xl font-semibold text-[#182820] sm:text-4xl">{{ __('asic-public.important_dates') }}</h2>
        <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($importantDates as $date)
                @php $past = ($date->tanggal_selesai ?? $date->tanggal_mulai)?->endOfDay()->isPast(); @endphp
                <li class="relative rounded-2xl border p-5 {{ $past ? 'border-[#dce4da] bg-white text-[#66746C]' : 'border-[#168348] bg-white' }}">
                    <span class="absolute right-4 top-4 h-2.5 w-2.5 rounded-full {{ $past ? 'bg-[#dce4da]' : 'bg-[#F5C518]' }}" aria-hidden="true"></span>
                    <p class="font-poppins text-lg font-semibold {{ $past ? '' : 'text-[#0B5D2E]' }}">{{ \App\Support\DateRange::format($date->tanggal_mulai, $date->tanggal_selesai) }}</p>
                    <p class="mt-1 text-sm font-semibold">{{ $date->localized('label') }}</p>
                    @if($past)<p class="mt-2 text-[11px] uppercase tracking-wider">{{ $L['passed'] }}</p>@endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endif

{{-- PROGRAM --}}
@if($programDays->isNotEmpty())
<section id="program" class="bg-white px-6 py-20 lg:px-16" x-data="{ day: 0 }">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-poppins text-3xl font-semibold text-[#182820] sm:text-4xl">{{ $L['program_title'] }}</h2>
        <div class="mt-8 flex flex-wrap gap-2" role="tablist">
            @foreach($programDays as $date => $items)
                <button type="button" role="tab" @click="day = {{ $loop->index }}" :aria-selected="(day === {{ $loop->index }}).toString()"
                    :class="day === {{ $loop->index }} ? 'bg-[#0B5D2E] text-white' : 'bg-[#E6F4EA] text-[#0B5D2E]'" class="rounded-xl px-4 py-2.5 text-sm font-semibold">
                    {{ $L['day'] }} {{ $loop->iteration }} · {{ \Illuminate\Support\Carbon::parse($date)->locale($locale)->translatedFormat('D, j M Y') }}
                </button>
            @endforeach
        </div>
        @foreach($programDays as $date => $items)
            <ol x-show="day === {{ $loop->index }}" class="mt-6 divide-y divide-[#dce4da] rounded-2xl border border-[#dce4da]" @if(! $loop->first) style="display: none;" @endif>
                @foreach($items as $item)
                    <li class="grid gap-2 p-4 sm:grid-cols-[140px_1fr_auto] sm:items-center">
                        <span class="font-mono text-sm font-semibold text-[#168348]">{{ substr($item->waktu_mulai, 0, 5) }}@if($item->waktu_selesai) – {{ substr($item->waktu_selesai, 0, 5) }}@endif</span>
                        <div><p class="font-semibold text-[#182820]">{{ $item->nama_kegiatan }}</p>@if($item->deskripsi)<p class="text-sm text-[#66746C]">{{ $item->deskripsi }}</p>@endif</div>
                        @if($item->lokasi)<span class="w-fit rounded-full bg-[#F7FAF8] px-3 py-1 text-xs text-[#3b4a40]">{{ $item->lokasi }}</span>@endif
                    </li>
                @endforeach
            </ol>
        @endforeach
    </div>
</section>
@endif

{{-- CARA BERGABUNG --}}
<section class="bg-[#F7FAF8] px-6 py-20 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-poppins text-3xl font-semibold text-[#182820] sm:text-4xl">{{ __('asic-landing.steps_title') }}</h2>
        <ol class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([1, 2, 3, 4] as $n)
                <li class="rounded-2xl bg-white p-5 ring-1 ring-[#dce4da]">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0B5D2E] font-poppins text-lg font-semibold text-[#F5C518]">{{ $n }}</span>
                    <h3 class="mt-4 font-bold text-[#0B5D2E]">{{ __('asic-landing.step_'.$n.'_title') }}</h3>
                    <p class="mt-1 text-sm text-[#3b4a40]">{{ __('asic-landing.step_'.$n.'_desc') }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- BIAYA --}}
<section id="fees" class="bg-[#0f3d24] px-6 py-20 text-white lg:px-16">
    <div class="mx-auto max-w-6xl">
        <p class="text-xs font-bold uppercase tracking-[2px] text-[#F5C518]">{{ __('asic-landing.nav_fees') }}</p>
        <h2 class="mt-3 font-poppins text-3xl font-semibold sm:text-4xl">{{ __('asic-landing.fees_title') }}</h2>
        @if(empty($feeTable['rows']))
            <p class="mt-6 text-white/80">{{ __('asic-public.fees_not_announced') }}</p>
        @else
            <div class="mt-8 overflow-x-auto rounded-2xl bg-white text-[#182820]">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-[#E6F4EA] text-xs uppercase tracking-wider text-[#0B5D2E]">
                        <tr>
                            <th class="px-5 py-4">{{ $L['fee_category'] }}</th><th class="px-4 py-4">{{ $L['fee_mode'] }}</th>
                            @foreach($feeTable['periods'] as $period)
                                <th class="px-4 py-4">{{ $period['name'] }}@if($period['active']) <span class="ml-1 rounded-full bg-[#F5C518] px-2 py-0.5 text-[10px] text-[#0f3d24]">{{ $L['fee_active'] }}</span>@endif</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#dce4da]">
                        @foreach($feeTable['rows'] as $row)
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $typeLabel($row['type']) }}</td><td class="px-4 py-4">{{ $modeLabel($row['mode']) }}</td>
                                @foreach($row['cells'] as $cell)
                                    <td class="px-4 py-4 font-mono">@forelse($cell as $currency => $amount)<span class="block {{ $currency === 'USD' ? 'text-[#66746C]' : 'font-semibold text-[#0B5D2E]' }}">{{ $amount }}</span>@empty — @endforelse</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-sm text-white/80">{{ $L['fee_note'] }}</p>
        @endif
    </div>
</section>

{{-- PUBLIKASI & FASILITAS --}}
@if($publicationBlocks->isNotEmpty() || $facilityBlocks->isNotEmpty())
<section id="publication" class="bg-white px-6 py-20 lg:px-16">
    <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-2">
        @if($publicationBlocks->isNotEmpty())
            <div>
                <h2 class="font-poppins text-3xl font-semibold text-[#182820]">{{ $L['publication_title'] }}</h2>
                <ul class="mt-6 space-y-3">
                    @foreach($publicationBlocks as $block)
                        <li class="flex gap-4 rounded-2xl border border-[#dce4da] p-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E6F4EA] text-[#0B5D2E]"><span class="material-symbols-outlined">menu_book</span></span>
                            <div><h3 class="font-bold">@if($block->tautan)<a href="{{ $block->tautan }}" target="_blank" rel="noopener" class="hover:underline">{{ $block->localized('judul') }}</a>@else{{ $block->localized('judul') }}@endif</h3>@if($block->localized('isi'))<p class="text-sm text-[#66746C]">{{ $block->localized('isi') }}</p>@endif</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if($facilityBlocks->isNotEmpty())
            <div>
                <h2 class="font-poppins text-3xl font-semibold text-[#182820]">{{ $L['facilities_title'] }}</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach($facilityBlocks as $block)
                        <article class="rounded-2xl bg-[#F7FAF8] p-5 ring-1 ring-[#dce4da]"><h3 class="font-bold text-[#0B5D2E]">{{ $block->localized('judul') }}</h3><p class="mt-2 text-sm text-[#3b4a40]">{!! nl2br(e($block->localized('isi'))) !!}</p></article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endif

{{-- VENUE --}}
<section id="venue" class="bg-[#F7FAF8] px-6 py-20 lg:px-16">
    <div class="mx-auto grid max-w-6xl items-center gap-10 lg:grid-cols-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-[2px] text-[#168348]">{{ $L['nav_venue'] }}</p>
            <h2 class="mt-3 font-poppins text-3xl font-semibold text-[#182820] sm:text-4xl">{{ $event?->lokasi ?: __('asic-landing.hero_info.venue_default') }}</h2>
            <p class="mt-2 text-[#66746C]">{{ $heroDates }} · {{ __('asic-landing.hero_info.field_trip') }}</p>
            @foreach($venueBlocks as $block)
                <div class="mt-4"><h3 class="font-bold text-[#0B5D2E]">{{ $block->localized('judul') }}</h3><p class="mt-1 text-sm text-[#3b4a40]">{!! nl2br(e($block->localized('isi'))) !!}</p></div>
            @endforeach
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode('Convention Hall Universitas Andalas, Limau Manis, Padang') }}" target="_blank" rel="noopener" class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#0B5D2E] px-5 text-sm font-bold text-white hover:bg-[#168348]"><span class="material-symbols-outlined text-[18px]">map</span>{{ $L['open_map'] }}</a>
                @foreach(['venue', 'travel'] as $page)
                    @if($pages[$page] ?? false)<a href="{{ route('public.'.$page) }}" class="inline-flex h-11 items-center rounded-xl border border-[#0B5D2E] px-5 text-sm font-bold text-[#0B5D2E] hover:bg-[#E6F4EA]">{{ __('asic-public.nav.'.$page) }}</a>@endif
                @endforeach
            </div>
        </div>
        <div class="relative overflow-hidden rounded-3xl">
            <img src="{{ asset('images/asic/hero-rice-terraces.jpg') }}" alt="" class="h-72 w-full object-cover lg:h-96" loading="lazy">
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[rgba(7,43,27,0.9)] p-5 text-white"><p class="text-xs uppercase tracking-wider text-[#F5C518]">{{ $L['host'] }}</p><p class="font-poppins font-semibold">{{ __('asic-landing.fact_organizer_val') }}</p></div>
        </div>
    </div>
</section>

{{-- PANITIA --}}
@if($committeeBlocks->isNotEmpty())
<section id="committee" class="bg-white px-6 py-20 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-poppins text-3xl font-semibold text-[#182820] sm:text-4xl">{{ $L['committee_title'] }}</h2>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($committeeBlocks as $block)
                <article class="rounded-2xl border border-[#dce4da] p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#168348]">{{ $block->localized('judul') }}</p>
                    <p class="mt-2 text-sm font-semibold text-[#182820]">{!! nl2br(e($block->localized('isi'))) !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- SPONSORSHIP --}}
<section id="sponsorship" class="bg-[#E6F4EA] px-6 py-20 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-poppins text-3xl font-semibold text-[#182820] sm:text-4xl">{{ __('asic-public.sponsors') }}</h2>
                <p class="mt-2 max-w-2xl text-[#3b4a40]">{{ $L['sponsor_lead'] }}</p>
            </div>
            <a href="{{ route('public.sponsorship') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#F5C518] px-5 text-sm font-bold text-[#0f3d24] hover:bg-[#fed649]">{{ $L['sponsor_cta'] }} <img src="{{ asset('images/asic/icon-arrow-up-right.svg') }}" width="16" height="16" alt=""></a>
        </div>
        @if($packages->isNotEmpty())
            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach($packages as $package)
                    <article class="rounded-2xl bg-white p-6 ring-1 ring-[#cfe6d6]">
                        <h3 class="font-poppins text-xl font-semibold text-[#0B5D2E]">{{ $package->nama }}</h3>
                        @if($package->harga_teks)<p class="mt-1 font-mono text-sm font-semibold">{{ $package->harga_teks }}</p>@endif
                        @if($package->localized('manfaat'))<p class="mt-3 text-sm text-[#3b4a40]">{!! nl2br(e($package->localized('manfaat'))) !!}</p>@endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

@include('public._landing_content')
@endsection
