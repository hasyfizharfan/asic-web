@extends('layouts.portal', ['title' => 'Template dokumen', 'role' => 'admin', 'roleName' => 'Admin', 'nav' => $adminNav])

@section('content')
<main class="space-y-5">
    <header><h1 class="text-2xl font-bold">Template dokumen</h1><p class="mt-1 text-sm text-on-surface-variant">Pengelolaan template belum terhubung ke API.</p></header>
    <section class="grid gap-4 md:grid-cols-2">
        <article class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5"><h2 class="font-semibold">Dokumen terbit</h2><p class="mt-2 text-sm">LOA, Penolakan, Invoice, Receipt, Sertifikat</p><button type="button" disabled class="mt-4 rounded-lg border px-4 py-2 text-sm opacity-60">Belum tersedia</button></article>
        <article class="rounded-xl border border-surface-container-highest bg-surface-container-lowest p-5"><h2 class="font-semibold">Template berkas unduhan</h2><p class="mt-2 text-sm">Abstrak, Presentasi, Artikel</p><button type="button" disabled class="mt-4 rounded-lg border px-4 py-2 text-sm opacity-60">Belum tersedia</button></article>
    </section>
</main>
@endsection
