@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Notifikasi', 'nav' => $participantNav])

@section('content')
<main class="mx-auto max-w-3xl space-y-5">
    <header><h1 class="text-2xl font-bold text-on-surface">Notifikasi</h1></header>
    <div role="status" class="rounded-2xl border border-secondary/30 bg-secondary-container p-5 text-sm text-on-secondary-container">
        Notifikasi belum tersedia karena backend belum memiliki layanan notifikasi.
    </div>
</main>
@endsection
