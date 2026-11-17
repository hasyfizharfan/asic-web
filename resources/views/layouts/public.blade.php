<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ASIC 2027 - Agrifood System International Conference' }}</title>
    @isset($metaDescription)<meta name="description" content="{{ $metaDescription }}">@endisset
    @include('partials.fonts')
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/asic/logo.svg') }}">
    <style>
        ::-webkit-scrollbar { display: none !important; width: 0px !important; }
        * { scrollbar-width: none !important; -ms-overflow-style: none !important; }
    </style>
    @include('partials.asic-i18n')
    @vite(['resources/css/asic.css', 'resources/js/asic.js'])
    @stack('styles')
</head>
<body class="bg-[#F7FAF8] text-[#182820] antialiased font-sans selection:bg-[#0B5D2E] selection:text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Main Content (Hero with Integrated Navbar + Sections) -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        @php $F = trans('asic-landing.footer'); @endphp
        <footer id="contact" class="relative overflow-hidden bg-[#072b1b] text-white/80">
            <div class="mx-auto max-w-7xl px-6 pb-10 pt-16 lg:px-8">
                <div class="grid gap-10 md:grid-cols-12">
                    <div class="space-y-4 md:col-span-5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white p-1.5"><img src="{{ asset('images/asic/logo.svg') }}" alt="" class="h-full w-full"></span>
                            <div>
                                <p class="font-serif text-2xl font-semibold text-white">ASIC 2027</p>
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#a7f4b6]">Agrifood System International Conference</p>
                            </div>
                        </div>
                        <p class="max-w-md text-[15px] leading-relaxed text-white/70">{{ $F['about'] }}</p>
                        <p class="flex items-center gap-2 text-sm text-white/70"><span class="material-symbols-outlined text-[18px] text-[#F5C518]">location_on</span>{{ $F['address'] }}</p>
                    </div>
                    <div class="md:col-span-3">
                        <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-white">{{ __('asic-public.nav.information') }}</h4>
                        <ul class="space-y-2.5 text-sm">
                            <li><a href="{{ route('portal.landing') }}" class="hover:text-white">{{ __('asic-public.nav.home') }}</a></li>
                            <li><a href="{{ route('public.downloads') }}" class="hover:text-white">{{ __('asic-documents.public.title') }}</a></li>
                            <li><a href="{{ route('public.archive') }}" class="hover:text-white">{{ __('asic-documents.archive.title') }}</a></li>
                            @foreach(($publicPages ?? []) as $name => $available)
                                @if($available && ! in_array($name, ['terms', 'privacy', 'ethics'], true))<li><a href="{{ route('public.'.$name) }}" class="hover:text-white">{{ __('asic-public.nav.'.$name) }}</a></li>@endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="md:col-span-4">
                        <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-white">{{ __('asic-public.nav.legal') }}</h4>
                        <ul class="space-y-2.5 text-sm">
                            @foreach(['terms', 'privacy', 'ethics'] as $name)
                                @if(($publicPages[$name] ?? false))<li><a href="{{ route('public.'.$name) }}" class="hover:text-white">{{ __('asic-public.nav.'.$name) }}</a></li>@endif
                            @endforeach
                        </ul>
                        @if(config('asic.contact_email'))
                            <a href="mailto:{{ config('asic.contact_email') }}" class="mt-4 inline-flex items-center gap-2 text-sm text-[#a7f4b6] hover:underline"><span class="material-symbols-outlined text-[18px]">mail</span>{{ config('asic.contact_email') }}</a>
                        @endif
                        <a href="{{ route('auth.participant.register') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#F5C518] px-5 py-2.5 text-sm font-bold text-[#0f3d24] hover:bg-[#fed649]">{{ __('asic-landing.btn_register') }} <span class="material-symbols-outlined text-[18px]">arrow_outward</span></a>
                    </div>
                </div>
                <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-white/50 sm:flex-row">
                    <p>© {{ date('Y') }} ASIC 2027 · {{ __('asic-landing.fact_organizer_val') }}</p>
                    <p>{{ $F['photo_credit'] }}</p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
