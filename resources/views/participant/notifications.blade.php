@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Notifikasi', 'nav' => $participantNav])

@section('content')
<main class="mx-auto max-w-3xl space-y-5">
    <header><h1 class="text-2xl font-bold text-on-surface">Notifikasi</h1></header>
    <div role="status" class="rounded-2xl border border-secondary/30 bg-secondary-container p-5 text-sm text-on-secondary-container">
        Belum tersedia
    </div>
    <button type="button" disabled class="rounded-xl bg-surface-container-high px-5 py-2.5 text-sm font-bold text-on-surface-variant opacity-60">Tandai semua dibaca</button>
</main>
@endsection
