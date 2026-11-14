<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ASIC 2027 - Agrifood System International Conference' }}</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Carlito:ital,wght@0,400;0,700;1,400;1,700&family=JetBrains+Mono:wght@400;500;600&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/asic/logo.svg') }}">
    <style>
        ::-webkit-scrollbar { display: none !important; width: 0px !important; }
        * { scrollbar-width: none !important; -ms-overflow-style: none !important; }
    </style>
    @vite(['resources/css/asic.css', 'resources/js/asic.js'])
    @stack('styles')
</head>
<body class="bg-[#F1F5EC] text-[#17261D] antialiased font-sans selection:bg-[#005226] selection:text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Main Content (Hero with Integrated Navbar + Sections) -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer id="contact" class="bg-[#0A2B19] text-white/80 border-t border-white/10 pt-16 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/asic/logo.svg') }}" alt="ASIC 2027 Logo" class="w-10 h-10 rounded-lg">
                            <div>
                                <h3 class="text-xl font-bold text-white font-display">ASIC 2027</h3>
                                <p class="text-xs text-[#a7f4b6] uppercase tracking-wider font-semibold">Agrifood System International Conference</p>
                            </div>
                        </div>
                        <p class="text-sm text-white/70 max-w-md leading-relaxed">
                            Organized by Faculty of Agriculture, Universitas Andalas. Connecting scientists, policymakers, and practitioners worldwide to shape sustainable agrifood systems.
                        </p>
                        <div class="flex items-center gap-4 text-xs text-white/60">
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm text-[#F5C518]">location_on</span> Padang, West Sumatra, Indonesia</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Navigation</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="#about" class="hover:text-white transition">{{ __('asic-landing.nav_about') }}</a></li>
                            <li><a href="#cfa" class="hover:text-white transition">{{ __('asic-landing.nav_cfa') }}</a></li>
                            <li><a href="#fees" class="hover:text-white transition">{{ __('asic-landing.nav_fees') }}</a></li>
                            <li><a href="#contact" class="hover:text-white transition">{{ __('asic-landing.nav_contact') }}</a></li>
                            <li><a href="{{ route('fe.dev.ui') }}" class="text-[#F5C518] hover:underline font-mono">Component Gallery (_ui)</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Secretariat</h4>
                        <p class="text-xs text-white/70 leading-relaxed">
                            Faculty of Agriculture<br>
                            Universitas Andalas, Limau Manih<br>
                            Padang, West Sumatra 25163<br>
                            <a href="mailto:{{ $asic['email'] }}" class="text-[#a7f4b6] hover:underline mt-2 inline-block font-mono">{{ $asic['email'] }}</a>
                        </p>
                    </div>
                </div>

                <div class="border-t border-white/10 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-white/50 gap-4">
                    <p>&copy; {{ date('Y') }} ASIC 2027. All rights reserved. Universitas Andalas.</p>
                    <p class="font-mono text-[11px]">Laravel 11 • Blade • Tailwind CSS • Alpine.js</p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
