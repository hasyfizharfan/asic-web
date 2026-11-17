@extends('layouts.portal', ['title' => __('asic-admin.dash.title'), 'role' => 'admin', 'roleName' => $roleName, 'nav' => $adminNav])

@php
    $t = trans('asic-admin.dash');
    $count = fn (array $rows, string $key) => (int) (collect($rows)->firstWhere('key', $key)['total'] ?? 0);
    $presenters = $count($registrations['by_participation_type'], 'Oral Presenter') + $count($registrations['by_participation_type'], 'Poster Presenter');
    $listeners = $count($registrations['by_participation_type'], 'Listener');
    $typeItems = collect($registrations['by_participation_type'])->map(fn ($r) => ['label' => $r['key'] ?? '-', 'value' => $r['total']])->values();
    $scopeItems = collect($abstracts['by_scope'])->map(fn ($r) => ['label' => $r['key'] ?? '-', 'value' => $r['total']])->values();
    $invoiceItems = $payments ? collect($payments['by_invoice_status'])->map(fn ($r) => ['label' => $r['key'] ?? '-', 'value' => $r['total']])->values() : collect();
    $activityStyle = [
        'Registrasi' => ['icon' => 'person_add', 'label' => $t['act_registration'], 'class' => 'bg-[#E6F4EA] text-primary'],
        'Verifikasi pembayaran' => ['icon' => 'verified', 'label' => $t['act_payment'], 'class' => 'bg-[#FEF6D8] text-[#7a5d00]'],
        'Review abstrak' => ['icon' => 'rate_review', 'label' => $t['act_review'], 'class' => 'bg-[#E8F0FE] text-[#1a4fa0]'],
    ];
@endphp

@section('content')
<main class="space-y-6">
    <section class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-surface-container-highest bg-surface-container-low px-5 py-4">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5C518] text-[#182820]"><span class="material-symbols-outlined">event_available</span></span>
            <div>
                <p class="text-sm font-bold">{{ $t['active_period'] }}</p>
                @if($activePeriod)
                    <p class="text-xs text-on-surface-variant">{{ $activePeriod->nama_periode }}{{ $activePeriod->event ? ' · '.$activePeriod->event->nama_event : '' }} · {{ \App\Support\DateRange::format($activePeriod->tanggal_mulai, $activePeriod->tanggal_selesai) }}</p>
                @else
                    <p class="text-xs text-on-surface-variant">{{ $t['no_period'] }}</p>
                @endif
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container-lowest px-3 py-1 font-mono text-xs font-semibold text-primary"><span class="h-2 w-2 rounded-full bg-[#168348]"></span>UTC+7 (Padang)</span>
    </section>

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="flex items-center gap-1 text-xs font-bold uppercase tracking-[0.18em] text-primary"><span class="material-symbols-outlined text-[16px]">monitoring</span>{{ $t['eyebrow'] }}</p>
            <h1 class="mt-1 text-[27px] font-bold leading-tight sm:text-[34px]">{{ $t['heading'] }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p>
        </div>
        <a href="/api/exports/recap" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] bg-[#F5C518] px-5 py-2 text-sm font-bold text-[#182820] hover:bg-[#fed649]"><span class="material-symbols-outlined text-[18px]">download</span>{{ $t['export'] }}</a>
    </header>

    <section class="grid gap-4 md:grid-cols-3">
        <x-ui.stat-card :title="$t['registrants']" :value="$registrations['total']" icon="groups" :description="$t['presenters'].': '.$presenters.' · '.$t['listeners'].': '.$listeners" />
        <x-ui.stat-card :title="$t['abstracts']" :value="$abstracts['total']" icon="description" color="warning"
            :description="$t['pending'].': '.$abstracts['selection']['menunggu_seleksi'].' · '.$t['accepted'].': '.$abstracts['selection']['diterima'].' · '.$t['rejected'].': '.$abstracts['selection']['ditolak']" />
        @if($canSeePayments && $payments)
            <article class="flex flex-col justify-between rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">{{ $t['payments'] }}</p>
                        @foreach(\App\Support\Currency::ALL as $currency)
                            <p class="mt-1 text-lg font-bold text-primary">{{ \App\Support\Currency::format($payments['amount_verified_by_currency'][$currency], $currency) }}</p>
                        @endforeach
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined text-[22px]">payments</span></span>
                </div>
                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 border-t border-surface-container-highest pt-3 text-xs">
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[#168348]"></span>{{ $t['verified'] }}</span>
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[#F5C518]"></span>{{ $t['awaiting'] }}: {{ $payments['awaiting_verification'] }}</span>
                    @foreach(\App\Support\Currency::ALL as $currency)
                        <span class="text-on-surface-variant">{{ $t['outstanding'] }} {{ $currency }}: {{ \App\Support\Currency::format($payments['amount_outstanding_by_currency'][$currency], $currency) }}</span>
                    @endforeach
                </div>
            </article>
        @endif
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-bold">{{ $t['by_type'] }}</h2>
            <p class="mb-4 text-xs text-on-surface-variant">{{ $t['by_type_hint'] }}</p>
            <x-ui.bar-list :items="$typeItems" :empty="$t['empty']" />
        </article>
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-bold">{{ $t['by_scope'] }}</h2>
            <p class="mb-4 text-xs text-on-surface-variant">{{ $t['acceptance'] }}: {{ $abstracts['selection']['acceptance_rate_percent'] === null ? $t['no_decision'] : $abstracts['selection']['acceptance_rate_percent'].'%' }}</p>
            <x-ui.bar-list :items="$scopeItems" :empty="$t['empty']" />
        </article>
        @if($canSeePayments && $payments)
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm">
                <h2 class="font-bold">{{ $t['by_invoice'] }}</h2>
                <p class="mb-4 text-xs text-on-surface-variant">{{ $t['by_invoice_hint'] }}</p>
                <x-ui.bar-list :items="$invoiceItems" :empty="$t['empty']" tone="accent" />
            </article>
        @endif
    </section>

    <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">history</span>
            <div><h2 class="font-bold">{{ $t['activity'] }}</h2><p class="text-xs text-on-surface-variant">{{ $t['activity_hint'] }}</p></div>
        </div>
        @if($activity->isEmpty())
            <p class="mt-4 text-sm text-on-surface-variant">{{ $t['no_activity'] }}</p>
        @else
            <ul class="mt-4 divide-y divide-surface-container-highest">
                @foreach($activity as $item)
                    @php $style = $activityStyle[$item['type']] ?? ['icon' => 'info', 'label' => $item['type'], 'class' => 'bg-surface-container text-on-surface-variant']; @endphp
                    <li class="flex items-center gap-3 py-3 text-sm">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $style['class'] }}"><span class="material-symbols-outlined text-[20px]">{{ $style['icon'] }}</span></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate">{{ $item['description'] }}</p>
                            <span class="mt-0.5 inline-block rounded px-1.5 py-0.5 text-[11px] font-semibold {{ $style['class'] }}">{{ $style['label'] }}</span>
                        </div>
                        <time class="shrink-0 text-xs text-on-surface-variant" datetime="{{ $item['timestamp']?->toIso8601String() }}">{{ $item['timestamp']?->diffForHumans() }}</time>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</main>
@endsection
