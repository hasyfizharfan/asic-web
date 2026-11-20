@extends('layouts.portal', ['role' => 'secretariat', 'roleName' => 'Rekapitulasi', 'nav' => $secNav, 'title' => __('asic-secretariat.recap.title')])

@section('content')
<main class="space-y-6" x-data="{
    registrations: null, participants: null, abstracts: null, attendance: null, invoices: null, payments: null,
    loading: true, loadError: '',
    async init() {
        const requests = [
            window.asicApi.get('/api/recap/registrations'), window.asicApi.get('/api/recap/participants'),
            window.asicApi.get('/api/recap/abstracts'), window.asicApi.get('/api/recap/attendance')
            @if($canSeePayments)
            , window.asicApi.get('/api/recap/invoices'), window.asicApi.get('/api/recap/payments')
            @endif
        ];
        const responses = await Promise.all(requests); this.loading = false;
        const failed = responses.find(response => !response.ok);
        if (failed) { this.loadError = failed.error || 'Rekap gagal dimuat.'; return; }
        this.registrations = responses[0].data?.data; this.participants = responses[1].data?.data;
        this.abstracts = responses[2].data?.data; this.attendance = responses[3].data?.data;
        @if($canSeePayments)
        this.invoices = responses[4].data?.data; this.payments = responses[5].data?.data;
        @endif
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="mb-1 text-xs font-bold uppercase tracking-widest text-primary">Secretariat desk · sinkron langsung</p><h1 class="text-[27px] font-bold leading-tight sm:text-[34px]">{{ __('asic-secretariat.recap.title') }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ __('asic-secretariat.recap.subtitle') }}</p></div>
        <nav class="flex flex-wrap gap-2" aria-label="Ekspor rekap">
            <a href="/api/exports/participants" class="rounded-lg border border-surface-container-highest bg-surface-container-lowest px-3 py-2 text-xs font-semibold">Ekspor peserta</a>
            <a href="/api/exports/abstracts" class="rounded-lg border border-surface-container-highest bg-surface-container-lowest px-3 py-2 text-xs font-semibold">Ekspor abstrak</a>
            @if($canSeePayments)
                <a href="/api/exports/payments" class="rounded-lg border border-surface-container-highest bg-surface-container-lowest px-3 py-2 text-xs font-semibold">Ekspor pembayaran</a>
                <a href="/api/exports/invoices" class="rounded-lg border border-surface-container-highest bg-surface-container-lowest px-3 py-2 text-xs font-semibold">Ekspor invoice</a>
                <a href="/api/exports/recap" class="inline-flex min-h-10 items-center gap-1.5 rounded-[9px] bg-[#F5C518] px-4 py-2 text-sm font-bold text-[#182820] hover:bg-[#fed649]"><span class="material-symbols-outlined text-[18px]">table_view</span>Ekspor rekap lengkap (Excel)</a>
            @endif
        </nav>
    </header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat rekap…</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container" style="display: none;"></p>
    <section x-show="registrations && abstracts" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><div class="flex items-start justify-between gap-2"><p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Registrasi</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined text-[22px]">how_to_reg</span></span></div><p class="mt-2 text-3xl font-bold text-primary" x-text="registrations?.total ?? 0"></p><p class="text-sm">Jumlah pendaftaran</p><template x-for="row in registrations?.by_status || []" :key="row.key"><p class="mt-2 flex justify-between border-t pt-2 text-xs"><span x-text="row.key"></span><b x-text="row.total"></b></p></template></article>
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><div class="flex items-start justify-between gap-2"><p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Partisipasi</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined text-[22px]">groups</span></span></div><p class="mt-2 text-3xl font-bold text-primary" x-text="participants?.total_participants ?? 0"></p><p class="text-sm">Peserta unik</p><template x-for="row in registrations?.by_participation_type || []" :key="row.key"><p class="mt-2 flex justify-between border-t pt-2 text-xs"><span x-text="row.key || 'Tidak diketahui'"></span><b x-text="row.total"></b></p></template></article>
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><div class="flex items-start justify-between gap-2"><p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Abstrak</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FEF6D8] text-[#7a5d00]"><span class="material-symbols-outlined text-[22px]">description</span></span></div><p class="mt-2 text-3xl font-bold text-[#7a5d00]" x-text="abstracts?.total ?? 0"></p><p class="text-sm">Pengajuan masuk</p><template x-for="row in abstracts?.by_status || []" :key="row.key"><p class="mt-2 flex justify-between border-t pt-2 text-xs"><span x-text="row.key"></span><b x-text="row.total"></b></p></template></article>
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><div class="flex items-start justify-between gap-2"><p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Kehadiran</p><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined text-[22px]">qr_code_scanner</span></span></div><p class="mt-2 text-3xl font-bold text-primary" x-text="attendance?.total_check_ins ?? 0"></p><p class="text-sm">Check-in tercatat</p><template x-for="row in attendance?.sessions || []" :key="row.id_session"><p class="mt-2 flex justify-between border-t pt-2 text-xs"><span class="truncate" x-text="row.nama_sesi"></span><b x-text="row.check_ins"></b></p></template></article>
    </section>
    <section class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><h2 class="font-bold">Abstrak menurut bidang</h2><ul class="mt-3 space-y-3"><template x-for="row in abstracts?.by_scope || []" :key="row.key"><li><div class="flex justify-between text-sm"><span class="font-semibold" x-text="row.key || 'Belum ditentukan'"></span><b class="font-mono text-xs" x-text="row.total"></b></div><div class="mt-1 h-2 rounded-full bg-[#E6F4EA]"><div class="h-2 rounded-full bg-[#168348]" :style="'width:' + (100 * row.total / Math.max(1, ...(abstracts?.by_scope || []).map(r => r.total))) + '%'"></div></div></li></template></ul><p x-show="abstracts && abstracts.by_scope.length===0" class="py-3 text-sm text-on-surface-variant">Belum ada data bidang abstrak.</p></article>
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><h2 class="font-bold">Partisipasi menurut negara</h2><p class="mb-3 text-xs text-on-surface-variant">Jumlah peserta dikelompokkan menurut negara di profil.</p><template x-for="row in participants?.by_country || []" :key="row.key"><div class="flex justify-between border-t py-2 text-sm"><span x-text="row.key"></span><b x-text="row.total"></b></div></template><p class="mt-3 text-xs font-semibold">Kategori registrasi</p><template x-for="row in participants?.by_category_and_period || []" :key="row.period + row.registration_type"><div class="flex justify-between border-t py-2 text-sm"><span x-text="row.period + ' · ' + row.registration_type"></span><b x-text="row.total"></b></div></template></article>
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><h2 class="font-bold">Kehadiran per sesi</h2><template x-for="row in attendance?.sessions || []" :key="row.id_session"><div class="border-t py-3"><div class="flex justify-between gap-3 text-sm"><strong x-text="row.nama_sesi"></strong><b x-text="row.check_ins + ' check-in'"></b></div><div class="mt-1 h-2 rounded-full bg-[#FEF6D8]"><div class="h-2 rounded-full bg-[#F5C518]" :style="'width:' + (100 * row.check_ins / Math.max(1, ...(attendance?.sessions || []).map(r => r.check_ins))) + '%'"></div></div><p class="mt-1 text-xs text-on-surface-variant" x-text="row.nama_event + ' · ' + row.waktu + ' · presentasi selesai: ' + row.presentations_completed"></p></div></template><p x-show="attendance && attendance.sessions.length===0" class="py-4 text-sm text-on-surface-variant">Belum ada sesi yang memiliki data check-in.</p></article>
    </section>
    @if($canSeePayments)
    <section class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><h2 class="font-bold">Invoice</h2><p class="mt-2 text-3xl font-bold" x-text="invoices?.total ?? 0"></p><p class="text-sm text-on-surface-variant" x-text="'Total tagihan: ' + (invoices?.total_tagihan ?? '0')"></p><template x-for="row in invoices?.by_status || []" :key="row.key"><p class="mt-2 flex justify-between border-t pt-2 text-sm"><span x-text="row.key"></span><b x-text="row.total + ' · ' + row.total_tagihan"></b></p></template></article>
        <article class="rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5 shadow-sm"><h2 class="font-bold">Pembayaran</h2><template x-for="item in [{label:'Bukti masuk', value:payments?.proofs_total},{label:'Menunggu verifikasi', value:payments?.awaiting_verification},{label:'Valid', value:payments?.verified_valid},{label:'Tidak valid', value:payments?.verified_invalid},{label:'Nominal terverifikasi', value:payments?.amount_verified}]" :key="item.label"><p class="flex justify-between border-t py-2 text-sm"><span x-text="item.label"></span><b x-text="item.value ?? 0"></b></p></template></article>
    </section>
    @endif
</main>
@endsection
