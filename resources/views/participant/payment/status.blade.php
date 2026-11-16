@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Status Pembayaran'])

@section('content')
<main class="mx-auto max-w-3xl space-y-5">
    <header><h1 class="text-2xl font-bold text-on-surface">Status pembayaran</h1><p class="mt-1 text-sm text-on-surface-variant">Status berdasarkan invoice pendaftaran Anda.</p></header>
    @forelse($registrations as $item)
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <h2 class="font-bold">{{ $item->event?->nama_event ?? 'ASIC' }} · Registrasi #{{ $item->id_registration }}</h2>
            @if($item->invoice)
                <p class="mt-2 text-sm">Invoice {{ $item->invoice->nomor_invoice }} · {{ $item->invoice->status_pembayaran }}</p>
                <p class="mt-1 text-sm">Total {{ \App\Support\Currency::format($item->invoice->total_tagihan, $item->invoice->mata_uang) }}</p>
                <a href="{{ route('portal.participant.payment.checkout', ['id_registration' => $item->id_registration]) }}" class="mt-3 inline-flex rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white">Buka invoice</a>
            @else
                <p class="mt-2 text-sm text-on-surface-variant">{{ $item->isPresenter() ? __('asic-participant.flow.invoice_after_acceptance') : __('asic-participant.flow.invoice_unavailable') }}</p>
            @endif
        </article>
    @empty
        <p class="rounded-xl border border-surface-container-highest p-5 text-sm text-on-surface-variant">Belum ada registrasi.</p>
    @endforelse
</main>
@endsection
