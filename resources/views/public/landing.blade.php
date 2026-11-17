@extends('layouts.public', ['title' => __('asic-landing.title')])

@section('content')
@php
    $L = trans('asic-landing.sec');
    $V = trans('asic-landing.v2');
    $locale = app()->getLocale();
    $heroDates = $event ? \App\Support\DateRange::format($event->tanggal_mulai, $event->tanggal_selesai) : __('asic-landing.hero_info.dates_default');
    $scopes = config('abstract-scopes', []);
    $scopeText = $scopeBlocks->mapWithKeys(fn ($b) => [mb_strtolower((string) $b->localized('judul')) => $b])->all();
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim(preg_replace('/\b(Prof|Dr|Assoc|Ir|M\.?Sc|Ph\.?D)\.?/i', '', $name))))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $photo = fn (string $name) => asset('images/asic/photos/'.$name.'.jpg');
    $hasFees = ! empty($feeCards);
    $currencies = collect($feeCards)->flatMap(fn ($byMode) => collect($byMode)->flatMap(fn ($byCur) => array_keys($byCur)))->unique()->sort()->values()->all();
    $firstCurrency = in_array('IDR', $currencies, true) ? 'IDR' : ($currencies[0] ?? 'IDR');
    $modes = collect(['Luring', 'Daring'])->filter(fn ($m) => collect($feeCards)->contains(fn ($byMode) => isset($byMode[$m])))->values()->all();
@endphp

{{-- HERO (Figma "Conference hero") --}}
<header id="top" class="relative overflow-hidden bg-[#0f3d24] text-white">
    <img src="{{ asset('images/asic/hero-rice-terraces.jpg') }}" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover" fetchpriority="high">
    <div class="absolute inset-0 bg-gradient-to-r from-[rgba(7,43,27,0.94)] to-[rgba(9,43,27,0.27)]" aria-hidden="true"></div>

    @include('public.partials.navbar', ['overlay' => true])

    <div class="relative z-10 flex flex-col gap-6 px-6 pb-14 pt-32 lg:px-16 lg:pt-40">
        @if($isDemo)
            <p class="w-fit rounded-full bg-[#F5C518] px-3 py-1 text-xs font-bold text-[#0f3d24]" role="note">{{ $L['demo_banner'] }}</p>
        @endif
        <p class="flex items-center gap-3 text-[13px] font-semibold uppercase tracking-[2px]"><span class="h-[3px] w-8 bg-[#F5C518]" aria-hidden="true"></span>ASIC 2027 · {{ __('asic-landing.location_badge') }}</p>
        <h1 class="max-w-[1000px] font-serif text-[44px] font-semibold leading-[1.05] tracking-[-1px] sm:text-6xl lg:text-[80px] lg:tracking-[-2px]">
            @if($locale === 'id') Konferensi Internasional <span class="italic text-[#F5C518]">Sistem Agrifood</span> @else Agrifood System <span class="italic text-[#F5C518]">International Conference</span> @endif
        </h1>
        <p class="max-w-[680px] text-base leading-relaxed text-white/90 sm:text-lg">{{ __('asic-landing.hero_lead') }} {{ $L['organized_by'] }}</p>
        <dl class="grid max-w-4xl grid-cols-1 gap-4 py-2 sm:grid-cols-3 sm:gap-8">
            <div class="border-l-2 border-[#F5C518] pl-4"><dt class="text-xs uppercase tracking-wider text-white/70">{{ __('asic-landing.hero_info.theme') }}</dt><dd class="mt-1.5 text-[15px] font-semibold leading-snug">{{ $event?->tema ?: __('asic-landing.hero_info.tba') }}</dd></div>
            <div class="border-l-2 border-[#F5C518] pl-4"><dt class="text-xs uppercase tracking-wider text-white/70">{{ __('asic-landing.hero_info.dates') }}</dt><dd class="mt-1.5 text-[15px] font-semibold">{{ $heroDates }}<span class="block font-normal text-white/80">{{ __('asic-landing.hero_info.field_trip') }}</span></dd></div>
            <div class="border-l-2 border-[#F5C518] pl-4"><dt class="text-xs uppercase tracking-wider text-white/70">{{ __('asic-landing.hero_info.venue') }}</dt><dd class="mt-1.5 text-[15px] font-semibold">{{ $event?->lokasi ?: __('asic-landing.hero_info.venue_default') }}</dd></div>
        </dl>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('auth.participant.register') }}" class="inline-flex items-center gap-3 rounded-full bg-[#F5C518] px-7 py-3.5 text-[15px] font-bold text-[#0f3d24] transition hover:bg-[#fed649]">{{ __('asic-landing.btn_register') }} <img src="{{ asset('images/asic/icon-arrow-up-right.svg') }}" width="16" height="16" alt=""></a>
            <a href="#cfa" class="inline-flex items-center gap-3 rounded-full bg-white px-7 py-3.5 text-[15px] font-bold text-[#0f3d24] transition hover:bg-[#E6F4EA]">{{ __('asic-landing.btn_submit_abstract') }} <img src="{{ asset('images/asic/icon-arrow-up-right.svg') }}" width="16" height="16" alt=""></a>
        </div>
    </div>

    <div class="relative z-10 border-t border-white/10 bg-[#0f3d24]/95" x-data="{
        target: @js($asic['countdown_target']), d: 0, h: 0, m: 0, s: 0, done: false,
        tick() { if (!this.target) return; const diff = new Date(this.target) - new Date(); if (diff <= 0) { this.done = true; return; }
            this.d = Math.floor(diff / 864e5); this.h = Math.floor(diff / 36e5) % 24; this.m = Math.floor(diff / 6e4) % 60; this.s = Math.floor(diff / 1e3) % 60; },
        init() { this.tick(); setInterval(() => this.tick(), 1000); }
    }">
        <div class="flex flex-col gap-4 px-6 py-6 md:flex-row md:items-center md:justify-between lg:px-16">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/asic/icon-calendar-clock.svg') }}" width="32" height="32" alt="">
                <div>
                    <p class="font-serif text-xl font-semibold">{{ $asic['countdown_target'] ? $L['countdown_title'] : __('asic-landing.strip_title') }}</p>
                    <p class="mt-1 text-sm text-white/80">{{ $asic['countdown_target'] ? $L['countdown_desc'] : __('asic-landing.strip_desc') }}</p>
                </div>
            </div>
            @if($asic['countdown_target'])
                <p x-show="done" class="text-sm font-semibold uppercase text-[#F5C518]" style="display: none;">{{ $L['countdown_done'] }}</p>
                <div x-show="!done" class="flex gap-2 sm:gap-3" aria-live="off">
                    @foreach(['d' => 'days', 'h' => 'hours', 'm' => 'minutes', 's' => 'seconds'] as $k => $label)
                        <div class="min-w-[68px] rounded-2xl bg-white/10 px-3 py-2 text-center ring-1 ring-white/10"><span class="block font-serif text-2xl font-semibold tabular-nums text-[#F5C518]" x-text="String({{ $k }}).padStart(2, '0')">00</span><span class="text-[11px] uppercase tracking-wider text-white/70">{{ $L['cd'][$label] }}</span></div>
                    @endforeach
                </div>
            @else
                <p class="text-sm font-semibold uppercase text-[#F5C518]">{{ __('asic-landing.strip_tag') }}</p>
            @endif
        </div>
    </div>
</header>

{{-- TENTANG --}}
<section id="about" class="scroll-mt-24 px-6 py-24 lg:px-16">
    <div class="mx-auto grid max-w-7xl items-center gap-14 lg:grid-cols-12">
        <div class="lg:col-span-6">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#168348]">{{ __('asic-landing.nav_about') }}</p>
            <h2 class="mt-4 font-serif text-4xl font-semibold leading-[1.1] text-[#182820] sm:text-5xl">{{ __('asic-landing.about_title') }}</h2>
            <div class="mt-6 space-y-4 text-[17px] leading-relaxed text-[#3b4a40]">
                @forelse($aboutBlocks as $block)
                    <p>{!! nl2br(e($block->localized('isi'))) !!}</p>
                @empty
                    <p>{{ $L['about_default'] }}</p>
                @endforelse
            </div>
            <dl class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-4">
                @foreach([['4th', $V['stat_edition']], [count($scopes), $V['stat_scopes']], [$speakers->count() ?: '—', $V['stat_speakers']], ['2+1', $V['stat_days']]] as [$num, $label])
                    <div><dt class="sr-only">{{ $label }}</dt><dd><span class="block font-serif text-4xl font-semibold text-[#0B5D2E]">{{ $num }}</span><span class="mt-1 block text-sm text-[#66746C]">{{ $label }}</span></dd></div>
                @endforeach
            </dl>
        </div>
        <div class="relative lg:col-span-6">
            <img src="{{ $photo('conference-hall') }}" alt="{{ $V['alt_conference'] }}" class="h-[420px] w-full rounded-[28px] object-cover shadow-xl sm:h-[500px]" loading="lazy">
            <div class="absolute -bottom-8 -left-4 hidden w-56 overflow-hidden rounded-3xl border-[6px] border-[#F7FAF8] shadow-xl sm:block">
                <img src="{{ $photo('smart-farming-lab') }}" alt="{{ $V['alt_lab'] }}" class="h-44 w-full object-cover" loading="lazy">
            </div>
            <div class="absolute right-4 top-4 rounded-2xl bg-white/95 p-4 shadow-lg backdrop-blur">
                <p class="text-xs font-bold uppercase tracking-wider text-[#168348]">{{ $L['history'] }}</p>
                <ol class="mt-2 flex gap-3 text-center">
                    @foreach(['1st' => '2018', '2nd' => '2022', '3rd' => '2024', '4th' => '2027'] as $ed => $year)
                        <li><span class="block font-serif text-lg font-semibold {{ $loop->last ? 'text-[#0B5D2E]' : 'text-[#9aaa9e]' }}">{{ $ed }}</span><span class="text-xs text-[#66746C]">{{ $year }}</span></li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</section>

{{-- PITA TEMA --}}
<section class="relative overflow-hidden px-6 py-24 text-white lg:px-16" aria-label="{{ $V['theme_label'] }}">
    <img src="{{ $photo('smart-farming-team') }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
    <div class="absolute inset-0 bg-[#072b1b]/80" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-5xl text-center">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#F5C518]">{{ $V['theme_label'] }}</p>
        <blockquote class="mt-6 font-serif text-3xl font-medium italic leading-snug sm:text-5xl">“{{ $event?->tema ?: $V['theme_default'] }}”</blockquote>
    </div>
</section>

{{-- CALL FOR ABSTRACTS (bento) --}}
<section id="cfa" class="scroll-mt-24 px-6 py-24 lg:px-16">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#168348]">{{ __('asic-landing.nav_cfa') }}</p>
                <h2 class="mt-4 font-serif text-4xl font-semibold text-[#182820] sm:text-5xl">{{ __('asic-landing.themes_title') }}</h2>
                <p class="mt-3 text-[17px] text-[#3b4a40]">{{ $L['cfa_lead'] }}</p>
            </div>
            @if($pages['downloads'] ?? false)
                <a href="{{ route('public.downloads') }}" class="inline-flex items-center gap-2 rounded-full px-5 py-3 text-[15px] font-bold text-[#0B5D2E] ring-1 ring-[#0B5D2E] hover:bg-[#E6F4EA]"><span class="material-symbols-outlined text-[20px]">download</span>{{ $L['templates'] }}</a>
            @endif
        </div>
        <div class="mt-12 grid gap-4 lg:grid-cols-4">
            <div class="relative min-h-[380px] overflow-hidden rounded-[28px] lg:row-span-2">
                <img src="{{ $photo('farmers-planting') }}" alt="{{ $V['alt_farmers'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-[#072b1b] via-[#072b1b]/40 to-transparent" aria-hidden="true"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                    <p class="font-serif text-2xl font-semibold leading-tight">{{ $V['cfa_card_title'] }}</p>
                    <ul class="mt-4 space-y-1.5 text-sm text-white/90">
                        @foreach($L['cfa_rules'] as $rule)<li class="flex gap-2"><span class="material-symbols-outlined text-[18px] text-[#F5C518]">check</span>{{ $rule }}</li>@endforeach
                    </ul>
                    <a href="{{ route('auth.participant.register') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-[#F5C518] px-5 py-3 text-sm font-bold text-[#0f3d24] hover:bg-[#fed649]">{{ __('asic-landing.btn_submit_abstract') }} <span class="material-symbols-outlined text-[18px]">arrow_outward</span></a>
                </div>
            </div>
            @foreach($scopes as $code => $name)
                @php $extra = $scopeText[mb_strtolower($name)] ?? null; @endphp
                <article class="group rounded-[24px] bg-white p-6 ring-1 ring-[#dce4da] transition hover:-translate-y-0.5 hover:shadow-lg hover:ring-[#168348]">
                    <div class="flex items-center justify-between">
                        <span class="font-serif text-3xl font-semibold text-[#0B5D2E]">{{ $code }}</span>
                        <span class="text-sm font-semibold text-[#9aaa9e]">0{{ $loop->iteration }}</span>
                    </div>
                    <h3 class="mt-6 text-lg font-bold leading-snug text-[#182820]">{{ $name }}</h3>
                    @if($extra?->localized('isi'))<p class="mt-2 text-sm leading-relaxed text-[#66746C]">{{ $extra->localized('isi') }}</p>@endif
                </article>
            @endforeach
        </div>
        @php $otherScopes = $scopeBlocks->reject(fn ($b) => in_array(mb_strtolower((string) $b->localized('judul')), array_map('mb_strtolower', $scopes), true)); @endphp
        @if($otherScopes->isNotEmpty() || $guideBlocks->isNotEmpty())
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach($otherScopes as $block)
                    <article class="rounded-[24px] bg-white p-6 ring-1 ring-[#dce4da]"><h3 class="font-bold text-[#182820]">{{ $block->localized('judul') }}</h3>@if($block->localized('isi'))<p class="mt-2 text-sm text-[#66746C]">{!! nl2br(e($block->localized('isi'))) !!}</p>@endif</article>
                @endforeach
                @foreach($guideBlocks as $block)
                    <article class="flex gap-4 rounded-[24px] bg-[#FEF6D8] p-6">
                        <span class="material-symbols-outlined text-[28px] text-[#7a5d00]">{{ $block->section === 'presentation_poster' ? 'image' : 'co_present' }}</span>
                        <div><h3 class="font-bold text-[#182820]">{{ $block->localized('judul') ?: __('asic-content.sections.'.$block->section) }}</h3><p class="mt-1 text-sm text-[#3b4a40]">{!! nl2br(e($block->localized('isi'))) !!}</p></div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- PEMBICARA --}}
@if($speakers->isNotEmpty())
<section id="speakers" class="scroll-mt-24 bg-[#0f3d24] px-6 py-24 text-white lg:px-16">
    <div class="mx-auto max-w-7xl">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#F5C518]">{{ $L['nav_speakers'] }}</p>
        <h2 class="mt-4 font-serif text-4xl font-semibold sm:text-5xl">{{ $L['speakers_title'] }}</h2>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($speakers as $speaker)
                @if($speaker->foto)
                    <article class="group overflow-hidden rounded-[28px] bg-white/[0.06] ring-1 ring-white/10">
                        <div class="relative aspect-square overflow-hidden">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($speaker->foto) }}" alt="{{ $speaker->nama_speaker }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#072b1b] via-[#072b1b]/20 to-transparent" aria-hidden="true"></div>
                            <div class="absolute inset-x-0 bottom-0 p-5">
                                <h3 class="text-lg font-bold leading-tight">{{ $speaker->nama_speaker }}</h3>
                                <p class="mt-0.5 text-sm text-white/80">{{ collect([$speaker->institusi, $speaker->negara])->filter()->implode(', ') }}</p>
                            </div>
                        </div>
                        @if($speaker->deskripsi)<p class="p-5 font-serif text-lg italic leading-snug text-[#F5C518]">“{{ $speaker->deskripsi }}”</p>@endif
                    </article>
                @else
                    <article class="rounded-[28px] bg-white/[0.06] p-6 ring-1 ring-white/10">
                        <div class="flex items-center gap-4">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#F5C518] to-[#e0a800] font-serif text-xl font-semibold text-[#0f3d24]" aria-hidden="true">{{ $initials($speaker->nama_speaker) }}</span>
                            <div>
                                <h3 class="text-lg font-bold leading-tight">{{ $speaker->nama_speaker }}</h3>
                                <p class="mt-0.5 text-sm text-white/70">{{ collect([$speaker->institusi, $speaker->negara])->filter()->implode(', ') }}</p>
                            </div>
                        </div>
                        @if($speaker->deskripsi)<p class="mt-5 border-t border-white/10 pt-4 font-serif text-lg italic leading-snug text-[#F5C518]">“{{ $speaker->deskripsi }}”</p>@endif
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- TANGGAL PENTING (linimasa) --}}
@if($importantDates->isNotEmpty())
<section id="dates" class="scroll-mt-24 px-6 py-24 lg:px-16">
    <div class="mx-auto max-w-7xl">
        <h2 class="font-serif text-4xl font-semibold text-[#182820] sm:text-5xl">{{ __('asic-public.important_dates') }}</h2>
        <ol class="mt-12 flex snap-x gap-4 overflow-x-auto pb-4">
            @foreach($importantDates as $date)
                @php $past = ($date->tanggal_selesai ?? $date->tanggal_mulai)?->endOfDay()->isPast(); @endphp
                <li class="relative w-60 shrink-0 snap-start">
                    <div class="flex items-center">
                        <span class="h-4 w-4 shrink-0 rounded-full {{ $past ? 'bg-[#cfd8d1]' : 'bg-[#F5C518] ring-4 ring-[#FEF6D8]' }}" aria-hidden="true"></span>
                        @if(! $loop->last)<span class="h-0.5 flex-1 bg-[#dce4da]" aria-hidden="true"></span>@endif
                    </div>
                    <p class="mt-4 font-serif text-xl font-semibold {{ $past ? 'text-[#9aaa9e]' : 'text-[#0B5D2E]' }}">{{ \App\Support\DateRange::format($date->tanggal_mulai, $date->tanggal_selesai) }}</p>
                    <p class="mt-1 pr-4 text-[15px] font-semibold {{ $past ? 'text-[#9aaa9e]' : 'text-[#182820]' }}">{{ $date->localized('label') }}</p>
                    @if($past)<p class="mt-1 text-xs uppercase tracking-wider text-[#9aaa9e]">{{ $L['passed'] }}</p>@endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endif

{{-- PROGRAM --}}
@if($programDays->isNotEmpty())
<section id="program" class="scroll-mt-24 bg-white px-6 py-24 lg:px-16" x-data="{ day: 0 }">
    <div class="mx-auto max-w-5xl">
        <h2 class="font-serif text-4xl font-semibold text-[#182820] sm:text-5xl">{{ $L['program_title'] }}</h2>
        <div class="mt-8 inline-flex flex-wrap gap-1 rounded-full bg-[#F1F6F2] p-1" role="tablist">
            @foreach($programDays as $date => $items)
                <button type="button" role="tab" @click="day = {{ $loop->index }}" :aria-selected="(day === {{ $loop->index }}).toString()"
                    :class="day === {{ $loop->index }} ? 'bg-[#0B5D2E] text-white shadow' : 'text-[#0B5D2E] hover:bg-white'" class="rounded-full px-5 py-2.5 text-sm font-semibold transition">
                    {{ $L['day'] }} {{ $loop->iteration }} · {{ \Illuminate\Support\Carbon::parse($date)->locale($locale)->translatedFormat('j M') }}
                </button>
            @endforeach
        </div>
        @foreach($programDays as $date => $items)
            <ol x-show="day === {{ $loop->index }}" class="mt-8 space-y-3" @if(! $loop->first) style="display: none;" @endif>
                @foreach($items as $item)
                    <li class="grid gap-3 rounded-2xl bg-[#F7FAF8] p-5 ring-1 ring-[#e3ebe5] sm:grid-cols-[150px_1fr_auto] sm:items-center">
                        <span class="text-[15px] font-bold tabular-nums text-[#168348]">{{ substr($item->waktu_mulai, 0, 5) }}@if($item->waktu_selesai) – {{ substr($item->waktu_selesai, 0, 5) }}@endif</span>
                        <div><p class="text-[17px] font-bold text-[#182820]">{{ $item->nama_kegiatan }}</p>@if($item->deskripsi)<p class="mt-0.5 text-sm text-[#66746C]">{{ $item->deskripsi }}</p>@endif</div>
                        @if($item->lokasi)<span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#3b4a40] ring-1 ring-[#dce4da]">{{ $item->lokasi }}</span>@endif
                    </li>
                @endforeach
            </ol>
        @endforeach
    </div>
</section>
@endif

{{-- BIAYA (kartu harga) --}}
<section id="fees" class="relative scroll-mt-24 overflow-hidden bg-[#0f3d24] px-6 py-24 text-white lg:px-16"
         x-data="{ mode: @js($modes[0] ?? 'Luring'), cur: @js($firstCurrency) }">
    <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-[#168348]/40 blur-3xl" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#F5C518]">{{ __('asic-landing.nav_fees') }}</p>
                <h2 class="mt-4 font-serif text-4xl font-semibold sm:text-5xl">{{ __('asic-landing.fees_title') }}</h2>
                <p class="mt-3 max-w-xl text-white/80">{{ $V['fees_lead'] }}</p>
            </div>
            @if($hasFees)
                <div class="flex flex-wrap gap-3">
                    @if(count($modes) > 1)
                        <div class="inline-flex rounded-full bg-white/10 p-1 ring-1 ring-white/15" role="group" aria-label="{{ $L['fee_mode'] }}">
                            @foreach($modes as $m)
                                <button type="button" @click="mode = @js($m)" :aria-pressed="(mode === @js($m)).toString()" :class="mode === @js($m) ? 'bg-white text-[#0f3d24]' : 'text-white/80 hover:text-white'" class="rounded-full px-4 py-2 text-sm font-bold transition">{{ $L['mode'][$m] ?? $m }}</button>
                            @endforeach
                        </div>
                    @endif
                    @if(count($currencies) > 1)
                        <div class="inline-flex rounded-full bg-white/10 p-1 ring-1 ring-white/15" role="group" aria-label="{{ $V['currency'] }}">
                            @foreach($currencies as $c)
                                <button type="button" @click="cur = @js($c)" :aria-pressed="(cur === @js($c)).toString()" :class="cur === @js($c) ? 'bg-[#F5C518] text-[#0f3d24]' : 'text-white/80 hover:text-white'" class="rounded-full px-4 py-2 text-sm font-bold transition">{{ $c }} <span class="font-normal opacity-80">· {{ $c === 'IDR' ? $V['domestic'] : $V['international'] }}</span></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if(! $hasFees)
            <p class="mt-10 rounded-2xl bg-white/10 p-6 text-white/80">{{ __('asic-public.fees_not_announced') }}</p>
        @else
            <div class="mt-12 grid gap-5 lg:grid-cols-3">
                @foreach(['Student', 'Regular'] as $type)
                    @foreach($feeCards[$type] ?? [] as $m => $byCurrency)
                        @foreach($byCurrency as $c => $card)
                            <article x-show="mode === @js($m) && cur === @js($c)" @if(! ($m === ($modes[0] ?? '') && $c === $firstCurrency)) style="display: none;" @endif
                                class="flex flex-col rounded-[28px] p-7 {{ $type === 'Regular' ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white text-[#182820]' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-xl font-bold">{{ $L['type'][$type] }}</h3>
                                        <p class="mt-1 text-sm opacity-75">{{ $V['type_desc'][$type] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $type === 'Regular' ? 'bg-[#0f3d24] text-white' : 'bg-[#E6F4EA] text-[#0B5D2E]' }}">{{ $card['current']['name'] }}</span>
                                </div>
                                <p class="mt-8 font-serif text-5xl font-semibold tabular-nums tracking-tight">{{ $card['current']['base'] }}</p>
                                <p class="mt-1 text-sm opacity-75">{{ $V['base_note'] }}@if($card['current']['until']) · {{ $V['until'] }} {{ \App\Support\DateRange::format($card['current']['until']) }}@endif</p>
                                <ul class="mt-6 space-y-2.5 border-t pt-5 text-[15px] {{ $type === 'Regular' ? 'border-[#0f3d24]/15' : 'border-[#e3ebe5]' }}">
                                    @if($card['current']['proceedings'])<li class="flex justify-between gap-3"><span>{{ $V['with_proceedings'] }}</span><b class="tabular-nums">{{ $card['current']['proceedings'] }}</b></li>@endif
                                    @if($card['current']['journal'])<li class="flex justify-between gap-3"><span>{{ $V['with_journal'] }}</span><b class="tabular-nums">{{ $card['current']['journal'] }}</b></li>@endif
                                    @foreach($card['others'] as $other)
                                        <li class="flex justify-between gap-3 opacity-75"><span>{{ $V['period_price'] }} {{ $other['name'] }}</span><span class="tabular-nums">{{ $other['base'] }}</span></li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('auth.participant.register') }}" class="mt-8 inline-flex h-12 items-center justify-center gap-2 rounded-full text-[15px] font-bold transition {{ $type === 'Regular' ? 'bg-[#0f3d24] text-white hover:bg-[#0B5D2E]' : 'bg-[#0B5D2E] text-white hover:bg-[#168348]' }}">{{ __('asic-landing.btn_register') }} <span class="material-symbols-outlined text-[18px]">arrow_outward</span></a>
                            </article>
                        @endforeach
                    @endforeach
                @endforeach
                <article class="rounded-[28px] bg-white/[0.06] p-7 ring-1 ring-white/10">
                    <h3 class="text-xl font-bold">{{ $V['included'] }}</h3>
                    @foreach(['Luring', 'Daring'] as $m)
                        <ul x-show="mode === @js($m)" class="mt-5 space-y-3 text-[15px] text-white/90" @if($m !== ($modes[0] ?? 'Luring')) style="display: none;" @endif>
                            @foreach($V['includes'][$m] as $item)<li class="flex gap-3"><span class="material-symbols-outlined text-[20px] text-[#F5C518]">check_circle</span>{{ $item }}</li>@endforeach
                        </ul>
                    @endforeach
                    <p class="mt-6 border-t border-white/10 pt-5 text-sm text-white/70">{{ $L['fee_note'] }}</p>
                </article>
            </div>
        @endif
    </div>
</section>

{{-- PUBLIKASI & FASILITAS --}}
@if($publicationBlocks->isNotEmpty() || $facilityBlocks->isNotEmpty())
<section id="publication" class="scroll-mt-24 px-6 py-24 lg:px-16">
    <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2">
        @if($publicationBlocks->isNotEmpty())
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#168348]">{{ $V['publication_eyebrow'] }}</p>
                <h2 class="mt-4 font-serif text-4xl font-semibold text-[#182820]">{{ $L['publication_title'] }}</h2>
                <ol class="mt-8 divide-y divide-[#dce4da] rounded-[24px] bg-white ring-1 ring-[#dce4da]">
                    @foreach($publicationBlocks as $block)
                        <li class="flex items-center gap-5 p-5">
                            <span class="font-serif text-2xl font-semibold text-[#9aaa9e]">0{{ $loop->iteration }}</span>
                            <div class="flex-1"><h3 class="font-bold text-[#182820]">@if($block->tautan)<a href="{{ $block->tautan }}" target="_blank" rel="noopener" class="hover:underline">{{ $block->localized('judul') }}</a>@else{{ $block->localized('judul') }}@endif</h3>@if($block->localized('isi'))<p class="text-sm text-[#66746C]">{{ $block->localized('isi') }}</p>@endif</div>
                            <span class="material-symbols-outlined text-[#168348]">menu_book</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif
        @if($facilityBlocks->isNotEmpty())
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#168348]">{{ $V['facilities_eyebrow'] }}</p>
                <h2 class="mt-4 font-serif text-4xl font-semibold text-[#182820]">{{ $L['facilities_title'] }}</h2>
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach($facilityBlocks as $block)
                        <article class="rounded-[24px] p-6 {{ $loop->first ? 'bg-[#0B5D2E] text-white' : 'bg-white ring-1 ring-[#dce4da]' }}">
                            <span class="material-symbols-outlined text-[28px] {{ $loop->first ? 'text-[#F5C518]' : 'text-[#168348]' }}">{{ $loop->first ? 'apartment' : 'videocam' }}</span>
                            <h3 class="mt-3 text-lg font-bold">{{ $block->localized('judul') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed {{ $loop->first ? 'text-white/85' : 'text-[#3b4a40]' }}">{!! nl2br(e($block->localized('isi'))) !!}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endif

{{-- VENUE & FIELD TRIP --}}
<section id="venue" class="scroll-mt-24 px-6 pb-24 lg:px-16 {{ ($publicationBlocks->isEmpty() && $facilityBlocks->isEmpty()) ? 'pt-24' : '' }}">
    <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-5">
        <article class="relative min-h-[460px] overflow-hidden rounded-[28px] text-white lg:col-span-3">
            <img src="{{ $photo('conference-hall') }}" alt="{{ $V['alt_conference'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-[#072b1b] via-[#072b1b]/50 to-transparent" aria-hidden="true"></div>
            <div class="absolute inset-x-0 bottom-0 p-8">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#F5C518]">{{ $L['nav_venue'] }}</p>
                <h2 class="mt-3 font-serif text-3xl font-semibold leading-tight sm:text-4xl">{{ $event?->lokasi ?: __('asic-landing.hero_info.venue_default') }}</h2>
                <p class="mt-2 text-white/85">{{ $heroDates }}</p>
                @foreach($venueBlocks as $block)<p class="mt-2 max-w-xl text-sm text-white/80">{{ $block->localized('isi') }}</p>@endforeach
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode('Convention Hall Universitas Andalas, Limau Manis, Padang') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-[#0f3d24] hover:bg-[#E6F4EA]"><span class="material-symbols-outlined text-[18px]">map</span>{{ $L['open_map'] }}</a>
                    @foreach(['venue', 'travel'] as $page)
                        @if($pages[$page] ?? false)<a href="{{ route('public.'.$page) }}" class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-bold ring-1 ring-white/50 hover:bg-white/10">{{ __('asic-public.nav.'.$page) }}</a>@endif
                    @endforeach
                </div>
            </div>
        </article>
        <article class="relative min-h-[460px] overflow-hidden rounded-[28px] text-white lg:col-span-2">
            <img src="{{ $photo('jam-gadang') }}" alt="{{ $V['alt_jamgadang'] }}" class="absolute inset-0 h-full w-full object-cover object-[center_85%]" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-[#072b1b] via-[#072b1b]/30 to-transparent" aria-hidden="true"></div>
            <div class="absolute inset-x-0 bottom-0 p-8">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#F5C518]">Field trip</p>
                <h2 class="mt-3 font-serif text-3xl font-semibold leading-tight">{{ $V['fieldtrip_title'] }}</h2>
                <p class="mt-2 text-sm text-white/85">{{ $V['fieldtrip_desc'] }}</p>
            </div>
        </article>
    </div>
</section>

{{-- PANITIA --}}
@if($committeeBlocks->isNotEmpty())
<section id="committee" class="scroll-mt-24 bg-white px-6 py-24 lg:px-16">
    <div class="mx-auto max-w-7xl">
        <h2 class="font-serif text-4xl font-semibold text-[#182820] sm:text-5xl">{{ $L['committee_title'] }}</h2>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($committeeBlocks as $block)
                <article class="rounded-[22px] bg-[#F7FAF8] p-5 ring-1 ring-[#e3ebe5]">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#168348]">{{ $block->localized('judul') }}</p>
                    <p class="mt-2 font-semibold text-[#182820]">{!! nl2br(e($block->localized('isi'))) !!}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- SPONSORSHIP --}}
<section id="sponsorship" class="relative scroll-mt-24 overflow-hidden px-6 py-24 text-white lg:px-16">
    <img src="{{ $photo('audience') }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
    <div class="absolute inset-0 bg-gradient-to-r from-[#072b1b] via-[#072b1b]/90 to-[#072b1b]/60" aria-hidden="true"></div>
    <div class="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-2 lg:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#F5C518]">{{ __('asic-public.sponsors') }}</p>
            <h2 class="mt-4 font-serif text-4xl font-semibold leading-tight sm:text-5xl">{{ $V['sponsor_title'] }}</h2>
            <p class="mt-4 max-w-lg text-[17px] text-white/85">{{ $L['sponsor_lead'] }}</p>
            <a href="{{ route('public.sponsorship') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-[#F5C518] px-6 py-3.5 text-[15px] font-bold text-[#0f3d24] hover:bg-[#fed649]">{{ $L['sponsor_cta'] }} <span class="material-symbols-outlined text-[18px]">arrow_outward</span></a>
        </div>
        @if($packages->isNotEmpty())
            <div class="grid gap-3">
                @foreach($packages as $package)
                    <article class="flex items-start justify-between gap-4 rounded-[22px] bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur">
                        <div><h3 class="font-serif text-xl font-semibold">{{ $package->nama }}</h3>@if($package->localized('manfaat'))<p class="mt-1 text-sm text-white/80">{{ $package->localized('manfaat') }}</p>@endif</div>
                        @if($package->harga_teks)<span class="shrink-0 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $package->harga_teks }}</span>@endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- AJAKAN BERGABUNG --}}
<section class="px-6 py-24 lg:px-16">
    <div class="mx-auto grid max-w-7xl overflow-hidden rounded-[32px] bg-white ring-1 ring-[#dce4da] lg:grid-cols-2">
        <div class="p-8 sm:p-12">
            <h2 class="font-serif text-4xl font-semibold leading-tight text-[#182820]">{{ __('asic-landing.steps_title') }}</h2>
            <ol class="mt-8 space-y-5">
                @foreach([1, 2, 3, 4] as $n)
                    <li class="flex gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#0B5D2E] font-serif text-lg font-semibold text-[#F5C518]">{{ $n }}</span>
                        <div><h3 class="font-bold text-[#182820]">{{ __('asic-landing.step_'.$n.'_title') }}</h3><p class="text-[15px] text-[#66746C]">{{ __('asic-landing.step_'.$n.'_desc') }}</p></div>
                    </li>
                @endforeach
            </ol>
            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('auth.participant.register') }}" class="inline-flex items-center gap-2 rounded-full bg-[#0B5D2E] px-6 py-3.5 text-[15px] font-bold text-white hover:bg-[#168348]">{{ __('asic-landing.btn_register') }} <span class="material-symbols-outlined text-[18px]">arrow_outward</span></a>
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-full px-6 py-3.5 text-[15px] font-bold text-[#0B5D2E] ring-1 ring-[#0B5D2E] hover:bg-[#E6F4EA]">{{ __('asic-landing.btn_login') }}</a>
            </div>
        </div>
        <div class="relative min-h-[320px]">
            <img src="{{ $photo('farmer-portrait') }}" alt="{{ $V['alt_farmer'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
        </div>
    </div>
</section>

@include('public._landing_content')
@endsection
