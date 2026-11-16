@extends('layouts.public', ['title' => $detail['nama_event'].' — '.__('asic-documents.archive.title'), 'metaDescription' => $detail['tema'] ?: __('asic-documents.archive.description')])

@section('content')
@include('public._topnav')
<div class="px-6 py-14 lg:px-16">
    <div class="mx-auto max-w-5xl space-y-6">
        <a href="{{ route('public.archive') }}" class="text-sm font-semibold text-[#005226] underline">← {{ __('asic-documents.archive.back') }}</a>
        <header>
            <h1 class="font-display text-3xl font-bold text-[#0F3D24]">{{ $detail['nama_event'] }}</h1>
            @if($detail['tema'])<p class="mt-1 italic text-[#3b4a40]">{{ $detail['tema'] }}</p>@endif
            <p class="mt-2 text-sm text-[#3b4a40]">{{ collect([$detail['tanggal_mulai'].' – '.$detail['tanggal_selesai'], $detail['lokasi']])->filter()->implode(' · ') }} · {{ __('asic-documents.archive.participants', ['count' => $detail['jumlah_peserta']]) }}</p>
        </header>
        @if($detail['deskripsi'])<p class="rounded-2xl bg-white p-6 text-sm leading-relaxed">{!! nl2br(e($detail['deskripsi'])) !!}</p>@endif
        @if($detail['speakers'])
            <section class="rounded-2xl bg-white p-6"><h2 class="text-xl font-bold text-[#0F3D24]">{{ __('asic-documents.archive.speakers') }}</h2>
                <ul class="mt-3 grid gap-2 text-sm md:grid-cols-2">@foreach($detail['speakers'] as $speaker)<li><strong>{{ $speaker['nama_speaker'] }}</strong> <span class="text-[#3b4a40]">{{ collect([$speaker['institusi'] ?? null, $speaker['negara'] ?? null])->filter()->implode(', ') }}</span></li>@endforeach</ul>
            </section>
        @endif
        @if($detail['schedules'])
            <section class="rounded-2xl bg-white p-6"><h2 class="text-xl font-bold text-[#0F3D24]">{{ __('asic-documents.archive.schedule') }}</h2>
                <ul class="mt-3 space-y-1 text-sm">@foreach($detail['schedules'] as $row)<li>{{ $row['tanggal'] }} {{ substr((string) $row['waktu_mulai'], 0, 5) }} · {{ $row['nama_kegiatan'] }}</li>@endforeach</ul>
            </section>
        @endif
        @if($detail['photos'])
            <section><h2 class="text-xl font-bold text-[#0F3D24]">{{ __('asic-documents.archive.gallery') }}</h2>
                <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-3">@foreach($detail['photos'] as $photo)<img src="{{ $photo['url'] }}" alt="{{ $photo['caption'] }}" loading="lazy" class="aspect-[4/3] w-full rounded-xl object-cover">@endforeach</div>
            </section>
        @endif
    </div>
</div>
@endsection
