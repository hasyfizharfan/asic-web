@extends('layouts.portal', ['role' => 'reviewer', 'title' => __('asic-reviewer.dash.title')])

@php
    $t = trans('asic-reviewer.dash');
    $scopes = config('abstract-scopes', []);
    $codeOf = fn (?string $scope) => array_search($scope, $scopes, true) ?: $scope;
    $scopeItems = collect($scopes)->map(fn ($name, $code) => ['label' => $code.' · '.$name, 'value' => (int) ($byScope[$code] ?? 0) + (int) ($byScope[$name] ?? 0)])->values();
    $badge = fn (?string $s) => match ($s) { 'Diterima' => 'success', 'Ditolak' => 'danger', default => 'warning' };
@endphp

@section('content')
<div class="space-y-6">
    <header>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">{{ $t['eyebrow'] }}</p>
        <h1 class="mt-1 text-[27px] font-bold leading-tight sm:text-[34px]">{{ $t['heading'] }}</h1>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p>
    </header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card :title="$t['total']" :value="$total" icon="description" />
        <x-ui.stat-card :title="$t['pending']" :value="$stats['pending']" icon="hourglass_top" color="warning" />
        <x-ui.stat-card :title="$t['accepted']" :value="$stats['accepted']" icon="check_circle" />
        <x-ui.stat-card :title="$t['rejected']" :value="$stats['rejected']" icon="cancel" color="error" :description="$t['reviewed_by_me'].': '.$stats['reviewed_by_me']" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-bold">{{ $t['by_scope'] }}</h2>
            <p class="mb-4 text-xs text-on-surface-variant">{{ $t['by_scope_hint'] }}</p>
            <x-ui.bar-list :items="$scopeItems" :empty="$t['empty_scope']" />
        </section>

        <section class="overflow-hidden rounded-2xl border border-surface-container-highest bg-surface-container-lowest shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between gap-3 p-5">
                <h2 class="font-bold">{{ $t['queue'] }}</h2>
                <a href="{{ route('portal.reviewer.queue') }}" class="inline-flex min-h-10 items-center rounded-[9px] bg-[#F5C518] px-4 py-2 text-sm font-bold text-[#182820] hover:bg-[#fed649]">{{ $t['full_list'] }}</a>
            </div>
            <div class="relative overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-surface-container-low text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">
                        <tr><th class="px-5 py-3">ID</th><th class="px-3 py-3">{{ $t['col_title'] }}</th><th class="px-3 py-3">Scope</th><th class="px-3 py-3">{{ $t['col_type'] }}</th><th class="px-3 py-3">Status</th><th class="px-5 py-3 text-right"><span class="sr-only">{{ $t['col_action'] }}</span></th></tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-highest">
                        @forelse($latestSubmissions as $abstract)
                            <tr>
                                <td class="px-5 py-3 font-mono text-xs text-primary">#{{ $abstract->id_abstract }}</td>
                                <td class="max-w-[260px] truncate px-3 py-3 font-semibold" title="{{ $abstract->judul_abstrak }}">{{ $abstract->judul_abstrak }}</td>
                                <td class="px-3 py-3">{{ $codeOf($abstract->scope) }}</td>
                                <td class="px-3 py-3">{{ $abstract->metode_presentasi }}</td>
                                <td class="px-3 py-3"><x-ui.badge size="sm" :variant="$badge($abstract->status_abstrak)">{{ \App\Support\StatusLabel::of($abstract->status_abstrak) }}</x-ui.badge></td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('portal.reviewer.abstracts.show', ['id' => $abstract->id_abstract]) }}" class="inline-flex min-h-9 items-center rounded-[9px] px-4 py-1.5 text-xs font-bold {{ $abstract->status_abstrak === 'Diajukan' ? 'bg-[#F5C518] text-[#182820] hover:bg-[#fed649]' : 'border border-surface-container-highest text-primary hover:border-primary' }}">{{ $abstract->status_abstrak === 'Diajukan' ? $t['review'] : $t['view'] }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">{{ $t['empty_queue'] }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
