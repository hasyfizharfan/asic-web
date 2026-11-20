{{-- Bagian bawah landing berbasis konten panitia: setiap bagian hanya muncul bila ada data terpublikasi. --}}
@if($faqs->isNotEmpty() || $contacts->isNotEmpty() || $channels->isNotEmpty())
<section id="faq" class="bg-white px-6 py-20 text-[#182820] lg:px-16">
    <div class="mx-auto grid max-w-6xl gap-10 md:grid-cols-2">
        @if($faqs->isNotEmpty())
            <div>
                <h2 class="font-poppins text-3xl font-semibold">{{ __('asic-public.faq_short') }}</h2>
                <div class="mt-6 space-y-3">
                    @foreach($faqs as $faq)
                        <details class="group rounded-2xl border border-[#dce4da] p-4 open:bg-[#F7FAF8]"><summary class="cursor-pointer list-none font-semibold marker:hidden"><span class="flex items-center justify-between gap-3">{{ $faq->localized('pertanyaan') }}<span class="material-symbols-outlined text-[#168348] transition group-open:rotate-45">add</span></span></summary><p class="mt-2 text-sm text-[#3b4a40]">{!! nl2br(e($faq->localized('jawaban'))) !!}</p></details>
                    @endforeach
                </div>
                <a href="{{ route('public.faq') }}" class="mt-4 inline-block text-sm font-semibold text-[#0B5D2E] underline">{{ __('asic-public.see_all') }}</a>
            </div>
        @endif
        <div class="space-y-6">
            @if($contacts->isNotEmpty())
                <div>
                    <h2 class="font-poppins text-3xl font-semibold">{{ __('asic-public.contacts') }}</h2>
                    <div class="mt-6">@include('public._contacts', ['contacts' => $contacts])</div>
                </div>
            @endif
            @if($channels->isNotEmpty())
                <div class="flex flex-wrap gap-3">
                    @foreach($channels as $channel)
                        <a href="{{ $channel->url }}" target="_blank" rel="noopener" class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#0B5D2E] px-5 text-sm font-bold text-white hover:bg-[#168348]"><span class="material-symbols-outlined text-[18px]">forum</span>{{ $channel->nama_kanal }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endif

@if($photos->isNotEmpty())
<section class="bg-[#F7FAF8] px-6 py-20 lg:px-16">
    <div class="mx-auto max-w-6xl">
        <h2 class="font-poppins text-3xl font-semibold text-[#182820]">{{ __('asic-public.gallery') }}</h2>
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3">
            @foreach($photos as $photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->file_path) }}" alt="{{ $photo->localized('caption') }}" loading="lazy" class="aspect-[4/3] w-full rounded-2xl object-cover">
            @endforeach
        </div>
        <a href="{{ route('public.gallery') }}" class="mt-4 inline-block text-sm font-semibold text-[#0B5D2E] underline">{{ __('asic-public.see_all') }}</a>
    </div>
</section>
@endif
