@php($t = trans('asic-treasurer.dashboard_page'))
@php($money = fn ($amount, $currency) => \App\Support\Currency::format($amount, $currency))
@php($count = fn (int $n, string $key = 'invoice_count') => trans_choice('asic-treasurer.dashboard_page.'.$key, $n, ['count' => $n]))
@php($label = fn (string $category) => $t['categories'][$category] ?? $category)
@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => $t['title'], 'title' => $t['title']])

@section('content')
@php($grid = 'grid grid-cols-2 gap-x-4 gap-y-1 sm:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)] sm:items-center')
<main class="space-y-6">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('portal.treasurer.payments.index') }}" class="block rounded-2xl transition hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
            <x-ui.stat-card :title="$t['pending']" :value="$pendingProofs" icon="pending_actions" color="warning" :description="$t['pending_hint']" class="h-full" />
        </a>

        {{-- Total terverifikasi: satu baris per mata uang, tidak dijumlahkan lintas mata uang. --}}
        <article class="flex flex-col rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm sm:order-last sm:col-span-2 xl:order-none xl:col-span-1">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">{{ $t['verified_total'] }}</p>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined text-[22px]" aria-hidden="true">verified</span></div>
            </div>
            <dl class="-mt-2 divide-y divide-surface-container-highest">
                @foreach($verifiedTotals as $currency => $row)
                    <div class="flex items-baseline justify-between gap-3 py-2">
                        <dt class="sr-only">{{ $currency }}</dt>
                        <dd class="min-w-0 truncate font-display text-[26px] font-bold leading-tight tabular-nums {{ $row['count'] ? 'text-primary' : 'text-on-surface-variant/60' }}">{{ $money($row['amount'], $currency) }}</dd>
                        <dd class="shrink-0 text-xs font-semibold text-on-surface-variant">{{ $count($row['count']) }}</dd>
                    </div>
                @endforeach
            </dl>
        </article>

        <x-ui.stat-card :title="$t['expired']" :value="$expiredInvoices" icon="event_busy" color="error" :description="$t['expired_hint']" />
    </section>

    <section class="grid gap-6 lg:grid-cols-5">
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm lg:col-span-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-bold">{{ $t['by_category'] }}</h2>
                @if($verifiedCount)
                    <span class="rounded-full bg-[#E6F4EA] px-3 py-1 text-xs font-bold text-primary">{{ $count($verifiedCount, 'verified_invoices') }}</span>
                @endif
            </div>

            @if($categorySummary->isEmpty())
                <p class="mt-4 text-sm text-on-surface-variant">{{ $t['no_verified'] }}</p>
            @else
                <div class="mt-4 text-sm">
                    <div class="{{ $grid }} hidden border-b border-surface-container-highest pb-2 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant sm:grid">
                        <span>{{ $t['col_category'] }}</span><span>{{ $t['col_invoices'] }}</span><span class="text-right">IDR</span><span class="text-right">USD</span>
                    </div>
                    <ul>
                        @foreach($categorySummary as $row)
                            @php($share = $verifiedCount ? round($row['count'] / $verifiedCount * 100) : 0)
                            <li class="{{ $grid }} border-b border-surface-container-highest py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-on-surface">{{ $label($row['category']) }}</p>
                                    @if($label($row['category']) !== $row['category'])<p class="text-xs text-on-surface-variant">{{ $row['category'] }}</p>@endif
                                </div>
                                <div class="text-right sm:text-left" title="{{ __('asic-treasurer.dashboard_page.share', ['percent' => $share]) }}">
                                    <p><span class="font-display text-xl font-bold tabular-nums text-on-surface">{{ $row['count'] }}</span> <span class="text-xs text-on-surface-variant">{{ trans_choice('asic-treasurer.dashboard_page.invoice_word', $row['count']) }}</span></p>
                                    <div class="mt-1 hidden h-1.5 max-w-[120px] rounded-full bg-[#E6F4EA] sm:block" aria-hidden="true"><div class="h-1.5 rounded-full bg-[#168348]" style="width: {{ $share }}%"></div></div>
                                </div>
                                @foreach(\App\Support\Currency::ALL as $currency)
                                    <p class="tabular-nums sm:text-right {{ $loop->last ? 'text-right' : '' }}">
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant sm:hidden">{{ $currency }}</span>
                                        @if($row['amounts'][$currency] > 0)
                                            <span class="font-semibold text-on-surface">{{ $money($row['amounts'][$currency], $currency) }}</span>
                                        @else
                                            <span class="text-on-surface-variant/60">—</span>
                                        @endif
                                    </p>
                                @endforeach
                            </li>
                        @endforeach
                    </ul>
                    <div class="{{ $grid }} -mx-3 mt-3 rounded-xl bg-surface-container-low px-3 py-3 font-bold">
                        <span>{{ $t['total'] }}</span>
                        <span class="text-right tabular-nums sm:text-left">{{ $count($verifiedCount) }}</span>
                        @foreach(\App\Support\Currency::ALL as $currency)
                            <p class="tabular-nums sm:text-right {{ $loop->last ? 'text-right' : '' }}">
                                <span class="block text-[10px] uppercase tracking-wider text-on-surface-variant sm:hidden">{{ $currency }}</span>
                                {{ $verifiedTotals[$currency]['count'] ? $money($verifiedTotals[$currency]['amount'], $currency) : '—' }}
                            </p>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>

        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-bold">{{ $t['latest_pending'] }}</h2>
                <a href="{{ route('portal.treasurer.payments.index') }}" class="shrink-0 text-sm font-semibold text-primary hover:underline">{{ $t['open_queue'] }}</a>
            </div>
            <ul class="mt-3 divide-y divide-surface-container-highest">
                @forelse($latestPending as $invoice)
                    @php($proof = $invoice->latestPaymentProof)
                    <li>
                        <a href="{{ route('portal.treasurer.payments.show', $invoice->id_invoice) }}" class="-mx-2 flex items-center justify-between gap-3 rounded-xl px-2 py-3 text-sm transition hover:bg-surface-container-low">
                            <span class="min-w-0">
                                <strong class="block truncate">{{ $invoice->nomor_invoice }}</strong>
                                <span class="block truncate text-xs text-on-surface-variant">{{ $invoice->registration?->participant?->full_name }}@if($proof?->metode_pembayaran) · {{ __('asic-common.payment_methods.'.$proof->metode_pembayaran) }}@endif</span>
                                @if($proof?->tanggal_upload)<span class="block text-xs text-on-surface-variant">{{ \Illuminate\Support\Carbon::parse($proof->tanggal_upload)->diffForHumans() }}</span>@endif
                            </span>
                            <span class="flex shrink-0 items-center gap-1 font-semibold tabular-nums">{{ $money($invoice->total_tagihan, $invoice->mata_uang) }}<span class="material-symbols-outlined text-[18px] text-on-surface-variant" aria-hidden="true">chevron_right</span></span>
                        </a>
                    </li>
                @empty
                    <li class="py-3 text-sm text-on-surface-variant">{{ $t['none_pending'] }}</li>
                @endforelse
            </ul>
        </article>
    </section>
</main>
@endsection
