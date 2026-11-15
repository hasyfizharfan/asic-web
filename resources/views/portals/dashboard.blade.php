@php
    $portalLabels = [
        'participant' => 'Peserta',
        'secretariat' => 'Sekretariat',
        'treasurer' => 'Bendahara',
        'reviewer' => 'Tim Abstrak',
        'admin' => 'Admin',
    ];
    $dashboardRoute = 'portal.'.$role.'.dashboard';
    $navigation = [[
        'label' => 'Ringkasan',
        'route' => $dashboardRoute,
        'icon' => 'dashboard',
    ]];
@endphp

@extends('layouts.portal', [
    'role' => $role,
    'roleName' => $roleName,
    'userName' => $userName,
    'nav' => $navigation,
    'title' => $roleName.' - ASIC 2027',
])

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6 shadow-xs">
        <p class="text-xs font-bold uppercase tracking-wider text-primary">ASIC 2027 · {{ $portalLabels[$role] ?? $roleName }}</p>
        <h1 class="mt-2 text-2xl font-bold text-on-surface">Selamat datang, {{ $userName }}</h1>
        <p class="mt-2 text-sm text-on-surface-variant">Ringkasan ini dimuat dari data konferensi dan akun Anda.</p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan data">
        @foreach ([
            'registrations' => 'Registrasi',
            'abstracts' => 'Abstrak',
            'pending_payments' => 'Tagihan menunggu',
            'active_events' => 'Event aktif',
        ] as $key => $label)
            <article class="rounded-xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $label }}</p>
                <p class="mt-3 text-3xl font-bold text-primary">{{ number_format($stats[$key]) }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-surface-container-high bg-surface-container-lowest">
            <div class="border-b border-surface-container-high px-5 py-4">
                <h2 class="font-bold text-on-surface">Registrasi terbaru</h2>
            </div>
            @forelse ($latestRegistrations as $registration)
                <article class="border-b border-surface-container-high px-5 py-4 last:border-b-0">
                    <p class="font-semibold text-on-surface">{{ $registration->participant?->full_name ?? 'Peserta' }}</p>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        {{ $registration->event?->nama_event ?? 'Event' }} · {{ $registration->participation_type }} · {{ $registration->registration_status }}
                    </p>
                </article>
            @empty
                <p class="px-5 py-6 text-sm text-on-surface-variant">Belum ada data registrasi.</p>
            @endforelse
        </div>

        <div class="overflow-hidden rounded-xl border border-surface-container-high bg-surface-container-lowest">
            <div class="border-b border-surface-container-high px-5 py-4">
                <h2 class="font-bold text-on-surface">Tagihan terbaru</h2>
            </div>
            @forelse ($latestInvoices as $invoice)
                <article class="flex items-center justify-between gap-4 border-b border-surface-container-high px-5 py-4 last:border-b-0">
                    <div>
                        <p class="font-semibold text-on-surface">{{ $invoice->nomor_invoice }}</p>
                        <p class="mt-1 text-xs text-on-surface-variant">{{ $invoice->registration?->participant?->full_name ?? 'Peserta' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-mono text-sm font-bold text-primary">Rp {{ number_format((float) $invoice->total_tagihan, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-on-surface-variant">{{ $invoice->status_pembayaran }}</p>
                    </div>
                </article>
            @empty
                <p class="px-5 py-6 text-sm text-on-surface-variant">Belum ada data tagihan.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
