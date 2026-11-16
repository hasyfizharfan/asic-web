@extends('layouts.portal', ['title' => 'Dashboard Admin', 'role' => 'admin', 'roleName' => $roleName, 'nav' => $adminNav])

@section('content')
<main class="space-y-6">
    <header><h1 class="text-2xl font-bold">Dashboard Admin</h1><p class="mt-1 text-sm text-on-surface-variant">Ringkasan berdasarkan data tersimpan.</p></header>

    @if($activePeriod)
        <section class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-4">
            <h2 class="font-semibold">Periode registrasi aktif</h2>
            <p class="mt-1 text-sm">{{ $activePeriod->nama_periode }}{{ $activePeriod->event ? ' · '.$activePeriod->event->nama_event : '' }}</p>
            <p class="text-sm text-on-surface-variant">{{ $activePeriod->tanggal_mulai }} – {{ $activePeriod->tanggal_selesai }}</p>
        </section>
    @else
        <section class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-4"><h2 class="font-semibold">Periode registrasi aktif</h2><p class="mt-1 text-sm text-on-surface-variant">Tidak ada periode aktif.</p></section>
    @endif

    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <h2 class="text-sm font-semibold text-on-surface-variant">Registrasi · {{ $registrations['total'] }}</h2>
            <ul class="mt-3 space-y-1 text-sm">@forelse($registrations['by_status'] as $row)<li>{{ $row['key'] ?? 'Tanpa status' }}: {{ $row['total'] }}</li>@empty<li>Belum ada data registrasi.</li>@endforelse</ul>
            <h3 class="mt-4 text-xs font-semibold">Jenis partisipasi</h3>
            <ul class="mt-1 space-y-1 text-sm">@forelse($registrations['by_participation_type'] as $row)<li>{{ $row['key'] ?? 'Tidak ditentukan' }}: {{ $row['total'] }}</li>@empty<li>Belum ada data partisipasi.</li>@endforelse</ul>
        </article>
        <article class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <h2 class="text-sm font-semibold text-on-surface-variant">Abstrak · {{ $abstracts['total'] }}</h2>
            <ul class="mt-3 space-y-1 text-sm">@forelse($abstracts['by_status'] as $row)<li>{{ $row['key'] ?? 'Tanpa status' }}: {{ $row['total'] }}</li>@empty<li>Belum ada data abstrak.</li>@endforelse</ul>
            <p class="mt-4 text-sm">Tingkat penerimaan: {{ $abstracts['selection']['acceptance_rate_percent'] === null ? 'Belum ada keputusan' : $abstracts['selection']['acceptance_rate_percent'].'%' }}</p>
        </article>
        @if($canSeePayments && $payments)
            <article class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <h2 class="text-sm font-semibold text-on-surface-variant">Pembayaran</h2>
                <dl class="mt-3 space-y-2 text-sm">@foreach(\App\Support\Currency::ALL as $currency)<div class="flex justify-between"><dt>Terverifikasi ({{ $currency }})</dt><dd>{{ \App\Support\Currency::format($payments['amount_verified_by_currency'][$currency], $currency) }}</dd></div><div class="flex justify-between"><dt>Belum lunas ({{ $currency }})</dt><dd>{{ \App\Support\Currency::format($payments['amount_outstanding_by_currency'][$currency], $currency) }}</dd></div>@endforeach<div class="flex justify-between"><dt>Menunggu verifikasi</dt><dd>{{ $payments['awaiting_verification'] }}</dd></div></dl>
            </article>
        @endif
    </section>

    <section class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-semibold">Aktivitas terbaru</h2><p class="text-sm text-on-surface-variant">Registrasi, verifikasi pembayaran, dan review abstrak.</p></div><a href="/api/exports/recap" class="rounded-lg border px-3 py-2 text-sm">Ekspor laporan</a></div>
        @if($activity->isEmpty())<p class="mt-4 text-sm text-on-surface-variant">Belum ada aktivitas.</p>@else
            <ul class="mt-4 divide-y divide-surface-container-highest">@foreach($activity as $item)<li class="flex flex-wrap justify-between gap-2 py-3 text-sm"><span><strong>{{ $item['type'] }}</strong> · {{ $item['description'] }}</span><time class="text-on-surface-variant">{{ $item['timestamp']?->format('Y-m-d H:i') }}</time></li>@endforeach</ul>
        @endif
    </section>
</main>
@endsection
