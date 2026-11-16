@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => 'Bendahara'])

@section('content')
<main class="space-y-6">
    <header><h1 class="text-2xl font-bold text-on-surface">Ringkasan Bendahara</h1><p class="mt-1 text-sm text-on-surface-variant">Ringkasan invoice dan bukti pembayaran dari sistem.</p></header>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5"><p class="text-sm text-on-surface-variant">Bukti menunggu verifikasi</p><p class="mt-2 text-3xl font-bold text-primary">{{ $pendingProofs }}</p></article>
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5"><p class="text-sm text-on-surface-variant">Total pembayaran terverifikasi (IDR)</p><p class="mt-2 text-2xl font-bold text-primary">Rp {{ number_format((float) $verifiedTotal, 0, ',', '.') }}</p></article>
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5"><p class="text-sm text-on-surface-variant">Invoice kedaluwarsa</p><p class="mt-2 text-3xl font-bold text-error">{{ $expiredInvoices }}</p></article>
    </section>
    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <h2 class="font-bold">Pembayaran terverifikasi per kategori</h2>
            <div class="mt-4 space-y-3">
                @forelse($categorySummary as $row)
                    <div class="flex items-center justify-between gap-4 border-b border-surface-container-highest pb-3 text-sm"><span>{{ $row->category }}</span><span class="text-right font-semibold">{{ $row->invoice_count }} invoice · Rp {{ number_format((float) $row->amount, 0, ',', '.') }}</span></div>
                @empty
                    <p class="text-sm text-on-surface-variant">Belum ada pembayaran terverifikasi.</p>
                @endforelse
            </div>
        </article>
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <div class="flex items-center justify-between gap-3"><h2 class="font-bold">Bukti terbaru menunggu verifikasi</h2><a href="{{ route('portal.treasurer.payments.index') }}" class="text-sm font-semibold text-primary">Buka antrean</a></div>
            <div class="mt-4 space-y-3">
                @forelse($latestPending as $invoice)
                    <a href="{{ route('portal.treasurer.payments.show', $invoice->id_invoice) }}" class="flex items-center justify-between gap-3 border-b border-surface-container-highest pb-3 text-sm"><span><strong>{{ $invoice->nomor_invoice }}</strong><span class="block text-xs text-on-surface-variant">{{ $invoice->registration?->participant?->full_name }}</span></span><span class="font-semibold">Rp {{ number_format((float) $invoice->total_tagihan, 0, ',', '.') }}</span></a>
                @empty
                    <p class="text-sm text-on-surface-variant">Tidak ada bukti menunggu verifikasi.</p>
                @endforelse
            </div>
        </article>
    </section>
</main>
@endsection
