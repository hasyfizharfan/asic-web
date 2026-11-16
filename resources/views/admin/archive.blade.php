@extends('layouts.portal', ['title' => 'Arsip', 'role' => 'admin', 'roleName' => 'Admin', 'nav' => $adminNav])

@section('content')
<main class="space-y-5">
    <header><h1 class="text-2xl font-bold">Arsip</h1><p class="mt-1 text-sm text-on-surface-variant">Pengelolaan arsip belum tersedia pada backend.</p></header>
    <section class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5"><p class="text-sm">Belum tersedia</p><button type="button" disabled class="mt-4 rounded-lg border px-4 py-2 text-sm opacity-60">Belum tersedia</button></section>
</main>
@endsection
