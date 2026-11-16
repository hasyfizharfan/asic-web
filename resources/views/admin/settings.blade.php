@extends('layouts.portal', ['title' => 'Pengaturan', 'role' => 'admin', 'roleName' => 'Admin', 'nav' => $adminNav])

@section('content')
<main class="space-y-5">
    <header><h1 class="text-2xl font-bold">Pengaturan</h1><p class="mt-1 text-sm text-on-surface-variant">Pengaturan hanya ditampilkan jika didukung oleh backend.</p></header>
    <section class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5"><h2 class="font-semibold">Pengaturan website</h2><p class="mt-2 text-sm text-on-surface-variant">Belum tersedia</p><button type="button" disabled class="mt-4 rounded-lg border px-4 py-2 text-sm opacity-60">Belum tersedia</button></section>
</main>
@endsection
