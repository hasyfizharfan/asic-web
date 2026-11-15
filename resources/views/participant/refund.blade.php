@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Refund'])

@section('content')
<main class="mx-auto max-w-3xl space-y-5">
    <header><h1 class="text-2xl font-bold text-on-surface">Pengajuan refund</h1></header>
    <div role="status" class="rounded-2xl border border-surface-container-highest bg-surface-container-low p-5 text-sm text-on-surface-variant">Belum tersedia</div>
    <button type="button" disabled class="rounded-xl bg-surface-container-high px-5 py-2.5 text-sm font-bold text-on-surface-variant opacity-60">Ajukan refund</button>
</main>
@endsection
