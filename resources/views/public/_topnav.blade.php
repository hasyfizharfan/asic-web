<nav class="bg-[#0B5D2E] px-6 py-5 text-white lg:px-16">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3">
        <a href="{{ route('portal.landing') }}" class="font-poppins text-2xl font-bold">ASIC 2027</a>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[15px] font-medium">
            <a href="{{ route('portal.landing') }}" class="text-white/85 hover:text-white">{{ __('asic-public.nav.home') }}</a>
            <a href="{{ route('public.downloads') }}" class="text-white/85 hover:text-white">{{ __('asic-documents.public.title') }}</a>
            <a href="{{ route('public.archive') }}" class="text-white/85 hover:text-white">{{ __('asic-documents.archive.title') }}</a>
            @foreach(($publicPages ?? []) as $name => $available)
                @if($available)<a href="{{ route('public.'.$name) }}" class="text-white/85 hover:text-white">{{ __('asic-public.nav.'.$name) }}</a>@endif
            @endforeach
            <span class="flex gap-1 rounded-xl border border-white/30 p-1 text-[13px] font-bold">
                <a href="{{ route('portal.language', 'id') }}" class="rounded-lg px-3 py-1.5 {{ app()->getLocale() === 'id' ? 'bg-white text-[#0B5D2E]' : 'text-white/80' }}">ID</a>
                <a href="{{ route('portal.language', 'en') }}" class="rounded-lg px-3 py-1.5 {{ app()->getLocale() === 'en' ? 'bg-white text-[#0B5D2E]' : 'text-white/80' }}">EN</a>
            </span>
        </div>
    </div>
</nav>
