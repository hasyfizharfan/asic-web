<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Login - ASIC 2027' }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Carlito:ital,wght@0,400;0,700;1,400;1,700&family=JetBrains+Mono:wght@400;500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/asic/logo.svg') }}">
    <style>::-webkit-scrollbar{display:none !important;width:0px !important;}*{scrollbar-width:none !important;-ms-overflow-style:none !important;}</style>
    @vite(['resources/css/asic.css', 'resources/js/asic.js'])
    @stack('styles')
</head>
<body class="bg-surface-container-low text-on-surface min-h-screen flex flex-col font-sans selection:bg-primary selection:text-white">
    <!-- Top header with Back and Lang Switcher -->
    <header class="w-full px-4 sm:px-8 py-4 flex items-center justify-between border-b border-surface-container-highest bg-surface-container-lowest">
        <a href="{{ route('fe.home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary-container transition">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            <span>{{ __('asic-auth.back_to_home') ?? 'Back to ASIC 2027' }}</span>
        </a>

        <div class="flex items-center gap-3">
            <x-layout.lang-switcher theme="light" />
        </div>
    </header>

    <!-- Main Auth Container -->
    <main class="flex-1 flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8 w-full">
        <div class="w-full max-w-5xl flex justify-center">
            @yield('content')
        </div>
        
        <p class="mt-8 text-center text-xs text-on-surface-variant">
            &copy; {{ date('Y') }} ASIC 2027 — Universitas Andalas.
        </p>
    </main>

    <x-ui.toast />
    @stack('scripts')
</body>
</html>

