@extends('layouts.public', ['title' => __('asic-documents.public.title').' — ASIC 2027', 'metaDescription' => __('asic-documents.public.description')])

@section('content')
@include('public._topnav')
<div class="px-6 py-14 lg:px-16">
    <div class="mx-auto max-w-4xl space-y-6">
        <header>
            <h1 class="font-display text-3xl font-bold text-[#0B5D2E] sm:text-4xl">{{ __('asic-documents.public.title') }}</h1>
            <p class="mt-2 text-sm text-[#3b4a40]">{{ __('asic-documents.public.description') }}</p>
        </header>
        @forelse($groups as $jenis => $items)
            <section class="rounded-2xl border border-[#d9e3d0] bg-white p-6">
                <h2 class="text-xl font-bold text-[#0B5D2E]">{{ __('asic-documents.types.'.$jenis) }}</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach($items as $item)
                        <li class="flex flex-wrap items-center justify-between gap-2 border-t border-[#e3eadc] pt-2">
                            <span>{{ $item->nama_template }}@if($item->subjenis) · {{ __('asic-documents.subtypes.'.$item->subjenis) }}@endif @if($item->bahasa) · {{ strtoupper($item->bahasa) }}@endif @if($item->versi) · v{{ $item->versi }}@endif</span>
                            <a href="{{ route('public.downloads.file', $item->id_template) }}" class="font-semibold text-[#0B5D2E] underline">{{ __('asic-documents.public.download') }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-public.empty_page') }}</p>
        @endforelse
    </div>
</div>
@endsection
