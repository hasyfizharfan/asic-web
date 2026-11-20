{{--
  Navbar publik. Transparan di atas hero landing ($overlay = true), lalu berubah menjadi pil melayang saat di-scroll.
  Di halaman publik lain langsung berbentuk pil.
--}}
@php
    $overlay = $overlay ?? false;
    $base = $overlay ? '' : route('portal.landing');
    $locale = app()->getLocale();
    $N = trans('asic-landing.sec');
    $links = [
        ['#about', __('asic-landing.nav_about'), ''], ['#cfa', __('asic-landing.nav_cfa'), ''], ['#program', $N['nav_program'], ''],
        ['#speakers', $N['nav_speakers'], ''], ['#fees', __('asic-landing.nav_fees'), ''], ['#venue', $N['nav_venue'], ''],
        ['#committee', $N['nav_committee'], 'wide'], ['#sponsorship', $N['nav_sponsorship'], 'wide'], ['#contact', __('asic-landing.nav_contact'), ''],
    ];
@endphp
<div x-data="{ scrolled: {{ $overlay ? 'false' : 'true' }}, open: false, overlay: {{ $overlay ? 'true' : 'false' }} }"
     x-init="if (overlay) scrolled = window.scrollY > 24"
     @scroll.window.passive="if (overlay) scrolled = window.scrollY > 24"
     @keydown.escape.window="open = false"
     class="fixed inset-x-0 top-0 z-50 px-3 transition-[padding] duration-300 sm:px-4" :class="scrolled ? 'pt-3' : 'pt-0'">
    <nav aria-label="{{ $N['nav_label'] }}"
         class="mx-auto flex items-center justify-between gap-4 text-white transition-all duration-300"
         :class="scrolled ? 'h-16 max-w-[1360px] rounded-full bg-[#0f3d24]/92 px-4 shadow-[0_12px_40px_-12px_rgba(0,0,0,0.45)] ring-1 ring-white/10 backdrop-blur-md lg:px-6' : 'h-20 max-w-none px-3 lg:h-24 lg:px-7'">
        <a href="{{ $overlay ? '#top' : route('portal.landing') }}" class="flex shrink-0 items-center gap-2.5 leading-none">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 p-1"><img src="{{ asset('images/asic/logo.svg') }}" alt="" class="h-full w-full"></span>
            <span><span class="block font-serif text-[22px] font-semibold">ASIC 2027</span><span class="mt-0.5 block text-[10px] font-semibold tracking-[1.6px] text-white/80">UNIVERSITAS ANDALAS</span></span>
        </a>
        <div class="hidden min-w-0 items-center text-[15px] font-medium xl:flex">
            @foreach($links as [$href, $label, $size])
                @if($size === 'wide')
                    {{-- Menu tambahan hanya muat di layar lebar saat navbar belum berbentuk pil. --}}
                    <a href="{{ $base.$href }}" class="hidden whitespace-nowrap rounded-full px-3 py-2 transition hover:bg-white/10 hover:text-[#F5C518]" :class="scrolled ? '' : 'min-[1600px]:inline-flex'">{{ $label }}</a>
                @else
                    <a href="{{ $base.$href }}" class="inline-flex whitespace-nowrap rounded-full px-2.5 py-2 transition hover:bg-white/10 hover:text-[#F5C518] 2xl:px-3">{{ $label }}</a>
                @endif
            @endforeach
        </div>
        <div class="hidden shrink-0 items-center gap-2 xl:flex">
            <div class="flex rounded-full bg-white/10 p-1 text-[13px] font-bold ring-1 ring-white/20">
                <a href="{{ route('portal.language', 'id') }}" class="rounded-full px-3 py-1.5 {{ $locale === 'id' ? 'bg-[#F5C518] text-[#0f3d24]' : 'text-white/80 hover:text-white' }}">ID</a>
                <a href="{{ route('portal.language', 'en') }}" class="rounded-full px-3 py-1.5 {{ $locale === 'en' ? 'bg-[#F5C518] text-[#0f3d24]' : 'text-white/80 hover:text-white' }}">EN</a>
            </div>
            <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-[15px] font-semibold hover:bg-white/10">{{ __('asic-landing.btn_login') }}</a>
            <a href="{{ route('auth.participant.register') }}" class="rounded-full bg-[#F5C518] px-5 py-2.5 text-[15px] font-bold text-[#0f3d24] transition hover:bg-[#fed649]">{{ __('asic-landing.btn_register') }}</a>
        </div>
        <button type="button" @click="open = !open" class="rounded-full p-2 hover:bg-white/10 xl:hidden" :aria-expanded="open.toString()" aria-controls="mobile-nav" aria-label="{{ $N['menu'] }}">
            <span class="material-symbols-outlined" x-text="open ? 'close' : 'menu'">menu</span>
        </button>
    </nav>
    <div id="mobile-nav" x-show="open" x-transition @click.outside="open = false" style="display: none;"
         class="mx-auto mt-2 max-w-[1360px] rounded-3xl bg-[#0f3d24] p-4 text-white shadow-2xl ring-1 ring-white/10 xl:hidden">
        <div class="grid gap-1 sm:grid-cols-2">
            @foreach($links as [$href, $label])
                <a href="{{ $base.$href }}" @click="open = false" class="rounded-xl px-3 py-2.5 text-base hover:bg-white/10">{{ $label }}</a>
            @endforeach
        </div>
        <div class="mt-3 flex items-center gap-2 border-t border-white/15 pt-3">
            <a href="{{ route('portal.language', 'id') }}" class="rounded-full px-3 py-1.5 text-sm font-bold {{ $locale === 'id' ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white/10' }}">ID</a>
            <a href="{{ route('portal.language', 'en') }}" class="rounded-full px-3 py-1.5 text-sm font-bold {{ $locale === 'en' ? 'bg-[#F5C518] text-[#0f3d24]' : 'bg-white/10' }}">EN</a>
            <a href="{{ route('login') }}" class="ml-auto rounded-full px-4 py-2 text-sm font-semibold ring-1 ring-white/30">{{ __('asic-landing.btn_login') }}</a>
            <a href="{{ route('auth.participant.register') }}" class="rounded-full bg-[#F5C518] px-4 py-2 text-sm font-bold text-[#0f3d24]">{{ __('asic-landing.btn_register') }}</a>
        </div>
    </div>
</div>
