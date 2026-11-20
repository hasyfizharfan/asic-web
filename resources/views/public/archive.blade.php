@extends('layouts.public', ['title' => __('asic-documents.archive.title').' — ASIC', 'metaDescription' => __('asic-documents.archive.description')])

@section('content')
@include('public._topnav')
<div class="px-6 py-14 lg:px-16">
    <div class="mx-auto max-w-5xl space-y-6">
        <header>
            <h1 class="font-display text-3xl font-bold text-[#0B5D2E] sm:text-4xl">{{ __('asic-documents.archive.title') }}</h1>
            <p class="mt-2 text-sm text-[#3b4a40]">{{ __('asic-documents.archive.description') }}</p>
        </header>
        <form method="GET" action="{{ route('public.archive') }}" class="flex flex-wrap gap-3 rounded-2xl bg-white p-4">
            <input name="q" value="{{ $q }}" maxlength="100" placeholder="{{ __('asic-documents.archive.search') }}" class="min-w-[14rem] flex-1 rounded-xl border-[#c9d6c2] text-sm">
            <select name="year" class="rounded-xl border-[#c9d6c2] text-sm" aria-label="{{ __('asic-documents.archive.year') }}">
                <option value="">{{ __('asic-documents.archive.all_years') }}</option>
                @foreach($years as $option)<option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>@endforeach
            </select>
            <button class="rounded-xl bg-[#0B5D2E] px-4 py-2 text-sm font-bold text-white">{{ __('asic-documents.archive.filter') }}</button>
        </form>
        @forelse($events as $event)
            <a href="{{ route('public.archive.show', $event->id_event) }}" class="block rounded-2xl border border-[#d9e3d0] bg-white p-5 hover:border-[#0B5D2E]">
                <h2 class="text-lg font-bold text-[#0B5D2E]">{{ $event->nama_event }}</h2>
                @if($event->tema)<p class="text-sm italic text-[#3b4a40]">{{ $event->tema }}</p>@endif
                <p class="mt-1 text-xs text-[#3b4a40]">{{ collect([$event->tanggal_mulai, $event->lokasi])->filter()->implode(' · ') }} · {{ __('asic-documents.archive.participants', ['count' => $archive->stats($event)['registrations']]) }}</p>
            </a>
        @empty
            <p class="rounded-2xl bg-white p-6 text-sm text-[#3b4a40]">{{ __('asic-documents.archive.empty') }}</p>
        @endforelse
        {{ $events->links() }}
    </div>
</div>
@endsection
