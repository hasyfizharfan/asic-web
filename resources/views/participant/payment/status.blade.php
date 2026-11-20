@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.portal.payment_status.title')])

@section('content')
<main class="mx-auto max-w-3xl space-y-5">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ __('asic-participant.portal.payment_status.title') }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ __('asic-participant.portal.payment_status.subtitle') }}</p></header>
    @forelse($registrations as $item)
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <h2 class="font-bold">{{ $item->event?->nama_event ?? 'ASIC' }} · {{ __('asic-participant.portal.registration_no', ['id' => $item->id_registration]) }}</h2>
            @if($item->invoice)
                <p class="mt-2 text-sm">Invoice {{ $item->invoice->nomor_invoice }} · {{ \App\Support\StatusLabel::of($item->invoice->status_pembayaran) }}</p>
                <p class="mt-1 text-sm">Total {{ \App\Support\Currency::format($item->invoice->total_tagihan, $item->invoice->mata_uang) }}</p>
                <a href="{{ route('portal.participant.payment.checkout', ['id_registration' => $item->id_registration]) }}" class="mt-3 inline-flex rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white">{{ __('asic-participant.portal.payment_status.open_invoice') }}</a>
            @else
                <p class="mt-2 text-sm text-on-surface-variant">{{ $item->isPresenter() ? __('asic-participant.flow.invoice_after_acceptance') : __('asic-participant.flow.invoice_unavailable') }}</p>
            @endif
        </article>
    @empty
        <p class="rounded-xl border border-surface-container-highest p-5 text-sm text-on-surface-variant">{{ __('asic-participant.portal.no_registration') }}</p>
    @endforelse
</main>
@endsection
