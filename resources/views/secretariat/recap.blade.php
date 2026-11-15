@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => 'Rekapitulasi', 'nav' => $secNav])

@section('content')
<main class="space-y-6" x-data="{
    registrations: null, abstracts: null, loading: true, loadError: '',
    async init() {
        const [registrationResponse, abstractResponse] = await Promise.all([
            window.asicApi.get('/api/recap/registrations'), window.asicApi.get('/api/recap/abstracts')
        ]);
        this.loading = false;
        if (!registrationResponse.ok || !abstractResponse.ok) { this.loadError = registrationResponse.error || abstractResponse.error || 'Rekap gagal dimuat.'; return; }
        this.registrations = registrationResponse.data?.data;
        this.abstracts = abstractResponse.data?.data;
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-2xl font-bold text-on-surface">Rekapitulasi ASIC</h1><p class="mt-1 text-sm text-on-surface-variant">Ringkasan dihitung dari registrasi dan abstrak di database.</p></div><div class="flex gap-2"><a href="/api/exports/participants" class="rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold">Ekspor peserta</a><a href="/api/exports/abstracts" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white">Ekspor abstrak</a></div></header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat rekap…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <section x-show="registrations && abstracts" class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5"><h2 class="font-bold">Registrasi</h2><p class="mt-3 text-3xl font-bold text-primary" x-text="registrations?.total ?? 0"></p><p class="text-sm text-on-surface-variant">Jumlah pendaftaran</p><template x-for="status in registrations?.by_status || []" :key="status.key"><p class="mt-2 flex justify-between text-sm"><span x-text="status.key"></span><span class="font-semibold" x-text="status.total"></span></p></template></article>
        <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5"><h2 class="font-bold">Abstrak</h2><p class="mt-3 text-3xl font-bold text-primary" x-text="abstracts?.total ?? 0"></p><p class="text-sm text-on-surface-variant">Jumlah abstrak</p><template x-for="status in abstracts?.by_status || []" :key="status.key"><p class="mt-2 flex justify-between text-sm"><span x-text="status.key"></span><span class="font-semibold" x-text="status.total"></span></p></template></article>
    </section>
</main>
@endsection
