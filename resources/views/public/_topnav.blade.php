{{-- Navbar halaman publik non-landing: pil melayang + baris tautan informasi. --}}
@include('public.partials.navbar', ['overlay' => false])
<div class="h-24" aria-hidden="true"></div>
<div class="mx-auto flex max-w-7xl flex-wrap gap-2 px-4 pb-2 text-sm">
    <a href="{{ route('public.downloads') }}" class="rounded-full bg-white px-4 py-2 font-semibold text-[#0B5D2E] ring-1 ring-[#dce4da] hover:ring-[#0B5D2E]">{{ __('asic-documents.public.title') }}</a>
    <a href="{{ route('public.archive') }}" class="rounded-full bg-white px-4 py-2 font-semibold text-[#0B5D2E] ring-1 ring-[#dce4da] hover:ring-[#0B5D2E]">{{ __('asic-documents.archive.title') }}</a>
    @foreach(($publicPages ?? []) as $name => $available)
        @if($available)<a href="{{ route('public.'.$name) }}" class="rounded-full bg-white px-4 py-2 font-semibold text-[#0B5D2E] ring-1 ring-[#dce4da] hover:ring-[#0B5D2E] {{ request()->routeIs('public.'.$name) ? 'bg-[#E6F4EA] ring-[#0B5D2E]' : '' }}">{{ __('asic-public.nav.'.$name) }}</a>@endif
    @endforeach
</div>
