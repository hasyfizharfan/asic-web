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
    @include('partials.fonts')
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/asic/logo.svg') }}">
    @include('partials.asic-i18n')
    <script>window.ASIC_STATUS = @js(trans('asic-common.status_values')); window.asicStatus = (v) => (v && window.ASIC_STATUS[v]) || v || '';</script>
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
            <footer class="flex flex-col items-center justify-between gap-2 border-t border-surface-container-highest bg-surface-container-lowest px-6 py-4 text-xs text-on-surface-variant sm:flex-row">
                <div>&copy; {{ date('Y') }} ASIC 2027 · {{ __('asic-landing.fact_organizer_val') }}</div>
                <a href="{{ route('portal.landing') }}" class="font-semibold text-primary hover:underline">{{ __('asic-auth.back_to_home') }}</a>
            </footer>
        </div>
    </div>

    <!-- Global Toast Component -->
    <x-ui.toast />
    <x-ui.pdf-viewer />
    @stack('scripts')
</body>
</html>

