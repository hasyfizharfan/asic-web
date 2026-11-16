{{-- Bagian landing berbasis konten panitia: setiap bagian hanya muncul bila ada data terpublikasi. --}}
@if($importantDates->isNotEmpty())
<section id="dates" class="bg-white px-6 py-16 text-[#17261D] lg:px-16">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-display text-3xl font-bold text-[#0F3D24]">{{ __('asic-public.important_dates') }}</h2>
        <ul class="mt-6 grid gap-3 md:grid-cols-2">
            @foreach($importantDates as $date)
                <li class="flex items-center justify-between gap-4 border-b border-[#c9d6c2] py-3">
                    <span class="font-semibold">{{ $date->localized('label') }}</span>
                    <span class="font-mono text-sm text-[#0F3D24]">{{ $date->tanggal_mulai?->translatedFormat('d M Y') }}@if($date->tanggal_selesai) – {{ $date->tanggal_selesai->translatedFormat('d M Y') }}@endif</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

@if(($pages['venue'] ?? false) || ($pages['travel'] ?? false) || ($pages['guidelines'] ?? false))
<section class="bg-[#F1F5EC] px-6 py-12 lg:px-16">
    <div class="mx-auto flex max-w-6xl flex-wrap gap-3">
        @foreach(['venue', 'guidelines', 'travel'] as $name)
            @if($pages[$name] ?? false)<a href="{{ route('public.'.$name) }}" class="rounded-xl border border-[#0F3D24] px-5 py-3 text-sm font-semibold text-[#0F3D24] hover:bg-white">{{ __('asic-public.nav.'.$name) }}</a>@endif
        @endforeach
    </div>
</section>
@endif

@if($faqs->isNotEmpty() || $contacts->isNotEmpty())
<section class="bg-white px-6 py-16 text-[#17261D] lg:px-16">
    <div class="mx-auto grid max-w-6xl gap-10 md:grid-cols-2">
        @if($faqs->isNotEmpty())
            <div>
                <h2 class="font-display text-2xl font-bold text-[#0F3D24]">{{ __('asic-public.faq_short') }}</h2>
                <div class="mt-4 space-y-3">
                    @foreach($faqs as $faq)
                        <details class="rounded-xl border border-[#d9e3d0] p-4"><summary class="cursor-pointer font-semibold">{{ $faq->localized('pertanyaan') }}</summary><p class="mt-2 text-sm">{!! nl2br(e($faq->localized('jawaban'))) !!}</p></details>
                    @endforeach
                </div>
                <a href="{{ route('public.faq') }}" class="mt-4 inline-block text-sm font-semibold text-[#005226] underline">{{ __('asic-public.see_all') }}</a>
            </div>
        @endif
        @if($contacts->isNotEmpty())
            <div>
                <h2 class="font-display text-2xl font-bold text-[#0F3D24]">{{ __('asic-public.contacts') }}</h2>
                <div class="mt-4">@include('public._contacts', ['contacts' => $contacts])</div>
            </div>
        @endif
    </div>
</section>
@endif

@if($packages->isNotEmpty())
<section class="bg-[#F1F5EC] px-6 py-16 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-display text-2xl font-bold text-[#0F3D24]">{{ __('asic-public.sponsors') }}</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @foreach($packages as $package)
                <article class="rounded-xl border border-[#d9e3d0] bg-white p-5"><h3 class="font-bold text-[#0F3D24]">{{ $package->nama }}</h3>@if($package->harga_teks)<p class="mt-1 text-sm">{{ $package->harga_teks }}</p>@endif</article>
            @endforeach
        </div>
        <a href="{{ route('public.sponsorship') }}" class="mt-4 inline-block text-sm font-semibold text-[#005226] underline">{{ __('asic-public.see_all') }}</a>
    </div>
</section>
@endif

@if($photos->isNotEmpty())
<section class="bg-white px-6 py-16 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-display text-2xl font-bold text-[#0F3D24]">{{ __('asic-public.gallery') }}</h2>
        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3">
            @foreach($photos as $photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->file_path) }}" alt="{{ $photo->localized('caption') }}" loading="lazy" class="aspect-[4/3] w-full rounded-xl object-cover">
            @endforeach
        </div>
        <a href="{{ route('public.gallery') }}" class="mt-4 inline-block text-sm font-semibold text-[#005226] underline">{{ __('asic-public.see_all') }}</a>
    </div>
</section>
@endif
