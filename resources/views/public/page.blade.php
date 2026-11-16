@extends('layouts.public', [
    'title' => __('asic-public.pages.'.$page.'.title').' — ASIC 2027',
    'metaDescription' => __('asic-public.pages.'.$page.'.description'),
])

@section('content')
@include('public._topnav')
<div class="px-6 py-14 lg:px-16">
    <div class="mx-auto max-w-4xl space-y-6">
        <header>
            <h1 class="font-display text-3xl font-bold text-[#0F3D24] sm:text-4xl">{{ __('asic-public.pages.'.$page.'.title') }}</h1>
            <p class="mt-2 text-sm text-[#3b4a40]">{{ __('asic-public.pages.'.$page.'.description') }}</p>
        </header>

        @switch($page)
            @case('venue')
                @if($event?->lokasi)
                    <article class="rounded-2xl border border-[#d9e3d0] bg-white p-6"><p class="text-xs font-bold uppercase tracking-wider text-[#0F3D24]/70">{{ __('asic-public.location') }}</p><p class="mt-2 text-lg font-semibold text-[#0F3D24]">{{ $event->lokasi }}</p></article>
                @endif
                @include('public._blocks', ['blocks' => $blocks])
                @if(! $event?->lokasi && $blocks->isEmpty())<p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>@endif
                @break

            @case('guidelines')
                @include('public._blocks', ['blocks' => $blocks])
                @php($guides = $event ? \App\Models\DocumentTemplate::where('id_event', $event->id_event)->where('jenis_dokumen', 'Panduan')->where('status_template', 'Aktif')->get() : collect())
                @if($guides->isNotEmpty())
                    <section class="rounded-2xl border border-[#d9e3d0] bg-white p-6"><h2 class="text-lg font-bold text-[#0F3D24]">{{ __('asic-documents.types.Panduan') }}</h2>
                        <ul class="mt-2 space-y-1 text-sm">@foreach($guides as $guide)<li><a href="{{ route('public.downloads.file', $guide->id_template) }}" class="text-[#005226] underline">{{ $guide->nama_template }}@if($guide->subjenis) · {{ __('asic-documents.subtypes.'.$guide->subjenis) }}@endif @if($guide->bahasa) · {{ strtoupper($guide->bahasa) }}@endif</a></li>@endforeach</ul>
                    </section>
                @endif
                @if($blocks->isEmpty() && $guides->isEmpty())<p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>@endif
                @break

            @case('faq')
                @forelse($items as $faq)
                    <details class="rounded-2xl border border-[#d9e3d0] bg-white p-5">
                        <summary class="cursor-pointer font-semibold text-[#0F3D24]">{{ $faq->localized('pertanyaan') }}</summary>
                        <p class="mt-3 text-sm leading-relaxed text-[#2f3d33]">{!! nl2br(e($faq->localized('jawaban'))) !!}</p>
                    </details>
                @empty
                    <p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>
                @endforelse
                @break

            @case('contact')
                @include('public._contacts', ['contacts' => $contacts])
                @if($contacts->isEmpty())<p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>@endif
                @break

            @case('sponsorship')
                @include('public._blocks', ['blocks' => $blocks])
                @if($packages->isNotEmpty())
                    <section class="grid gap-4 md:grid-cols-2">
                        @foreach($packages as $package)
                            <article class="rounded-2xl border border-[#d9e3d0] bg-white p-6">
                                <h2 class="text-lg font-bold text-[#0F3D24]">{{ $package->nama }}</h2>
                                @if($package->localized('deskripsi'))<p class="mt-2 text-sm text-[#2f3d33]">{!! nl2br(e($package->localized('deskripsi'))) !!}</p>@endif
                                @php($benefits = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $package->localized('manfaat'))))))
                                @if($benefits)<ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-[#2f3d33]">@foreach($benefits as $benefit)<li>{{ $benefit }}</li>@endforeach</ul>@endif
                                <p class="mt-3 text-sm font-semibold text-[#0F3D24]">{{ $package->harga_teks }}</p>
                                @if($package->kuota !== null)<p class="text-xs text-[#3b4a40]">{{ __('asic-public.quota', ['count' => $package->kuota]) }}</p>@endif
                            </article>
                        @endforeach
                    </section>
                @endif
                @if($blocks->isEmpty() && $packages->isEmpty())<p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>@endif
                @if($event)@include('public._sponsorship_form', ['event' => $event, 'packages' => $packages])@endif
                @break

            @case('travel')
                @include('public._blocks', ['blocks' => $blocks])
                @if($blocks->isEmpty())<p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>@endif
                @if($contacts->isNotEmpty())<h2 class="pt-4 text-xl font-bold text-[#0F3D24]">{{ __('asic-public.contacts') }}</h2>@include('public._contacts', ['contacts' => $contacts])@endif
                @break

            @case('gallery')
                @if($items->isEmpty())
                    <p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>
                @else
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                        @foreach($items as $photo)
                            <figure class="overflow-hidden rounded-xl bg-white">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->file_path) }}" alt="{{ $photo->localized('caption') }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                                @if($photo->localized('caption'))<figcaption class="p-2 text-xs text-[#3b4a40]">{{ $photo->localized('caption') }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                @endif
                @break

            @case('terms')
            @case('privacy')
            @case('ethics')
                @if($legal)
                    <article class="rounded-2xl border border-[#d9e3d0] bg-white p-6">
                        <h2 class="text-xl font-bold text-[#0F3D24]">{{ $legal->localized('judul') }}</h2>
                        <p class="mt-1 text-xs text-[#3b4a40]">{{ __('asic-public.legal_version', ['version' => $legal->versi, 'date' => $legal->berlaku_mulai?->translatedFormat('d F Y')]) }}</p>
                        <div class="mt-4 text-sm leading-relaxed text-[#2f3d33]">{!! nl2br(e($legal->localized('isi'))) !!}</div>
                    </article>
                @else
                    <p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.legal_unpublished') }}</p>
                @endif
                @break

            @default
                @include('public._blocks', ['blocks' => $blocks])
                @if($blocks->isEmpty())<p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>@endif
        @endswitch
    </div>
</div>
@endsection
