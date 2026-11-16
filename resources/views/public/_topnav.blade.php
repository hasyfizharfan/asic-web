<nav class="bg-[#0F3D24] px-6 py-4 text-white lg:px-16">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3">
        <a href="{{ route('portal.landing') }}" class="font-display text-lg font-bold">ASIC 2027</a>
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <a href="{{ route('portal.landing') }}" class="text-white/85 hover:text-white">{{ __('asic-public.nav.home') }}</a>
            @foreach(($publicPages ?? []) as $name => $available)
                @if($available)<a href="{{ route('public.'.$name) }}" class="text-white/85 hover:text-white">{{ __('asic-public.nav.'.$name) }}</a>@endif
            @endforeach
            <span class="flex gap-1 text-xs font-bold">
                <a href="{{ route('portal.language', 'id') }}" class="rounded px-2 py-1 {{ app()->getLocale() === 'id' ? 'bg-white text-[#0F3D24]' : 'text-white/80' }}">ID</a>
                <a href="{{ route('portal.language', 'en') }}" class="rounded px-2 py-1 {{ app()->getLocale() === 'en' ? 'bg-white text-[#0F3D24]' : 'text-white/80' }}">EN</a>
            </span>
        </div>
    </div>
</nav>
