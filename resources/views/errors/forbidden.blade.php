@php
    $en = app()->getLocale() === 'en';
    $T = $en
        ? ['title' => 'Access denied', 'body' => 'Your account does not have permission to open this page. If you believe this is a mistake, please contact the ASIC 2027 committee.', 'back' => 'Go back', 'home' => 'ASIC 2027 home']
        : ['title' => 'Akses ditolak', 'body' => 'Akun Anda tidak memiliki hak akses untuk membuka halaman ini. Jika menurut Anda ini keliru, silakan hubungi panitia ASIC 2027.', 'back' => 'Kembali', 'home' => 'Beranda ASIC 2027'];
@endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'id' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $T['title'] }} - ASIC 2027</title>
    @include('partials.fonts')
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/asic/logo.svg') }}">
    @vite(['resources/css/asic.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[#F7FAF8] p-4 font-sans text-on-surface antialiased">
<main class="w-full max-w-md rounded-2xl border border-surface-container-highest bg-white p-8 text-center shadow-sm">
    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-error/10 text-error">
        <span class="material-symbols-outlined text-3xl" aria-hidden="true">block</span>
    </span>
    <p class="mt-4 text-xs font-bold uppercase tracking-[0.2em] text-on-surface-variant">403</p>
    <h1 class="mt-1 text-2xl font-bold text-primary">{{ $T['title'] }}</h1>
    <p class="mt-3 text-sm leading-relaxed text-on-surface-variant">{{ $T['body'] }}</p>
    <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="javascript:history.back()" class="rounded-xl border border-primary px-4 py-2 text-sm font-semibold text-primary">{{ $T['back'] }}</a>
        <a href="{{ route('portal.landing') }}" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white">{{ $T['home'] }}</a>
    </div>
</main>
</body>
</html>
