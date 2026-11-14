@props([
    'role' => 'participant', // participant, secretariat, treasurer, admin
    'roleName' => null,
    'userName' => null,
    'nav' => null
])

@php
    $effectiveNav = !empty($nav) ? $nav : match($role) {
        'reviewer' => $reviewerNav ?? [],
        'secretariat' => $secNav ?? [],
        'treasurer' => $treasurerNav ?? [],
        'admin' => $adminNav ?? [],
        default => $participantNav ?? [],
    };

    $effectiveRoleName = $roleName ?? match($role) {
        'reviewer' => __('asic-nav.portal_reviewer'),
        'secretariat' => __('asic-nav.portal_secretariat'),
        'treasurer' => __('asic-nav.portal_treasurer'),
        'admin' => __('asic-nav.portal_admin'),
        default => ($isListener ?? false) ? __('asic-participant.role_listener') : __('asic-participant.role_oral'),
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal - ASIC 2027' }}</title>
    
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
<body x-data="{ sidebarOpen: false }" class="bg-surface text-on-surface min-h-screen flex flex-col font-sans selection:bg-primary selection:text-white">
    <div class="flex flex-1 min-h-screen">
        <!-- Sidebar Component (Desktop & Mobile Drawer) -->
        <x-layout.sidebar :role="$role" :role-name="$effectiveRoleName" :nav="$effectiveNav" />

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-60">
            <!-- Topbar Component -->
            <x-layout.topbar :role="$role" :role-name="$effectiveRoleName" :user-name="$userName" />

            <!-- Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>

            <!-- Portal Footer -->
            <footer class="py-4 px-6 border-t border-surface-container-highest text-xs text-on-surface-variant flex flex-col sm:flex-row items-center justify-between gap-2 bg-surface-container-lowest">
                <div>&copy; {{ date('Y') }} ASIC 2027 — Faculty of Agriculture, Universitas Andalas.</div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>System Online (ASIC 2027 Portal)</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Global Toast Component -->
    <x-ui.toast />
    @stack('scripts')
</body>
</html>

