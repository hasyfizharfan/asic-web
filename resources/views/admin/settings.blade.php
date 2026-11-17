@extends('layouts.portal', ['title' => 'Pengaturan', 'role' => 'admin', 'roleName' => 'Admin', 'nav' => $adminNav])

@section('content')
<main class="space-y-5">
    <header><h1 class="text-2xl font-bold">Pengaturan</h1><p class="mt-1 text-sm text-on-surface-variant">Pengaturan hanya ditampilkan jika didukung oleh backend.</p></header>
    @include('settings._periods_payment')
</main>
@endsection
