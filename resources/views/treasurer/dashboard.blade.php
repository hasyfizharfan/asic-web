@php($t = trans('asic-treasurer.dashboard_page'))
@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => $t['title'], 'title' => $t['title']])

@section('content')
<main class="space-y-6">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p></header>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-ui.stat-card :title="$t['pending']" :value="$pendingProofs" icon="pending_actions" color="warning" />
        <x-ui.stat-card :title="$t['verified_total']" :value="collect($verifiedTotals)->map(fn ($amount, $currency) => \App\Support\Currency::format($amount, $currency))->implode(' · ') ?: '0'" icon="verified" />
        <x-ui.stat-card :title="$t['expired']" :value="$expiredInvoices" icon="event_busy" color="error" />
    </section>
    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <h2 class="font-bold">{{ $t['by_category'] }}</h2>
            <div class="mt-4 space-y-3">
                @forelse($categorySummary as $row)
                    <div class="flex items-center justify-between gap-4 border-b border-surface-container-highest pb-3 text-sm"><span>{{ $row->category }}</span><span class="text-right font-semibold">{{ $row->invoice_count }} invoice · {{ \App\Support\Currency::format($row->amount, $row->currency) }}</span></div>
                @empty
                    <p class="text-sm text-on-surface-variant">{{ $t['no_verified'] }}</p>
                @endforelse
            </div>
        </article>
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <div class="flex items-center justify-between gap-3"><h2 class="font-bold">{{ $t['latest_pending'] }}</h2><a href="{{ route('portal.treasurer.payments.index') }}" class="text-sm font-semibold text-primary">{{ $t['open_queue'] }}</a></div>
            <div class="mt-4 space-y-3">
                @forelse($latestPending as $invoice)
                    <a href="{{ route('portal.treasurer.payments.show', $invoice->id_invoice) }}" class="flex items-center justify-between gap-3 border-b border-surface-container-highest pb-3 text-sm"><span><strong>{{ $invoice->nomor_invoice }}</strong><span class="block text-xs text-on-surface-variant">{{ $invoice->registration?->participant?->full_name }}@if($invoice->latestPaymentProof?->metode_pembayaran) · {{ __('asic-common.payment_methods.'.$invoice->latestPaymentProof->metode_pembayaran) }}@endif</span></span><span class="font-semibold">{{ \App\Support\Currency::format($invoice->total_tagihan, $invoice->mata_uang) }}</span></a>
                @empty
                    <p class="text-sm text-on-surface-variant">{{ $t['none_pending'] }}</p>
                @endforelse
            </div>
        </article>
    </section>
</main>
@endsection
