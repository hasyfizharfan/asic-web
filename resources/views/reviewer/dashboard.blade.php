@extends('layouts.portal', ['role' => 'reviewer', 'title' => __('asic-nav.dashboard') ?? 'Dashboard Reviewer - ASIC 2027'])

@section('content')
<div class="space-y-6">
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs">
        <h1 class="text-2xl sm:text-3xl font-bold text-on-surface font-display">{{ __('asic-nav.dashboard') ?? 'Dashboard' }}</h1>
        <p class="text-sm text-on-surface-variant mt-1">Ringkasan antrean review abstrak.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach([['Menunggu Review', 'pending'], ['Saya Review', 'reviewed_by_me'], ['Diterima', 'accepted'], ['Ditolak', 'rejected']] as [$label, $key])
        <div class="bg-surface-container-lowest p-5 rounded-2xl border border-surface-container-highest shadow-xs"><div class="text-xs font-semibold text-on-surface-variant">{{ $label }}</div><div class="text-3xl font-extrabold text-primary font-mono mt-2">{{ $stats[$key] }}</div></div>
        @endforeach
    </div>
    <div class="bg-surface-container-lowest rounded-2xl border border-surface-container-highest shadow-xs overflow-hidden">
        <div class="p-5 border-b border-surface-container-highest flex items-center justify-between"><h2 class="text-lg font-bold text-on-surface">Lima abstrak terbaru yang diajukan</h2><a class="text-xs font-bold text-primary" href="{{ route('portal.reviewer.queue') }}">Buka antrean</a></div>
        <div class="divide-y divide-surface-container-highest">
            @forelse($latestSubmissions as $abstract)
            <a href="{{ route('portal.reviewer.abstracts.show', ['id' => $abstract->id_abstract]) }}" class="block p-4 sm:px-5 hover:bg-surface-container-low/50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2"><div><div class="text-xs font-mono text-primary">#{{ $abstract->id_abstract }}</div><div class="font-bold text-on-surface">{{ $abstract->judul_abstrak }}</div><div class="text-xs text-on-surface-variant">{{ config('abstract-scopes.'.$abstract->scope, $abstract->scope) }}</div></div><time class="text-xs text-on-surface-variant">{{ $abstract->tanggal_submit?->format('Y-m-d H:i') ?? '—' }}</time></div>
            </a>
            @empty
            <div class="p-8 text-center text-sm text-on-surface-variant">Belum ada abstrak dalam antrean.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
