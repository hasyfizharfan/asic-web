@php($t = trans('asic-participant.portal.dashboard'))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => $participant?->full_name ?: __('asic-nav.portal_participant'), 'userName' => $participant?->full_name ?: auth()->user()->email])

@section('content')
<main class="space-y-6">
    <header class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6">
        <h1 class="text-2xl font-bold">{{ __('asic-participant.welcome_back', ['name' => $participant?->full_name ?: auth()->user()->email]) }}</h1>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $participant?->institution ?: 'ASIC 2027' }}</p>
    </header>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            [$t['stat_registrations'], $registrations->count()],
            [$t['stat_abstracts'], $registrations->filter(fn($r) => $r->abstractSubmission)->count()],
            [$t['stat_invoices'], $registrations->filter(fn($r) => $r->invoice && $r->invoice->status_pembayaran !== 'Terverifikasi')->count()],
            [$t['stat_documents'], $documents->count()],
        ] as [$label, $value])
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5"><p class="text-sm text-on-surface-variant">{{ $label }}</p><p class="mt-2 text-3xl font-bold text-primary">{{ $value }}</p></article>
        @endforeach
    </section>
    <section class="space-y-3">
        <h2 class="text-lg font-bold">{{ $t['my_registrations'] }}</h2>
        @forelse($registrations as $item)
            <article class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="font-bold">{{ $item->event?->nama_event ?? 'ASIC 2027' }}</h3><p class="mt-1 text-sm text-on-surface-variant">#{{ $item->id_registration }} · {{ $item->participation_type }} · {{ $item->registration_status }}</p></div>
                    @if($item->invoice)<span class="rounded-full bg-surface-container-low px-3 py-1 text-xs font-semibold">{{ $item->invoice->status_pembayaran }}</span>@endif
                </div>
                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    @if($item->isPresenter())<a class="font-semibold text-primary" href="{{ route('portal.participant.abstract', ['id_registration' => $item->id_registration]) }}">{{ $t['abstract'] }}: {{ $item->abstractSubmission?->status_abstrak ?? $t['not_submitted'] }}</a>@endif
                    @if($item->invoice)<a class="font-semibold text-primary" href="{{ route('portal.participant.payment.checkout', ['id_registration' => $item->id_registration]) }}">Invoice {{ $item->invoice->nomor_invoice }}</a>@elseif($item->isPresenter())<span class="text-on-surface-variant">{{ __('asic-participant.flow.invoice_after_acceptance') }}</span>@endif
                    <a class="font-semibold text-primary" href="{{ route('portal.participant.documents', ['id_registration' => $item->id_registration]) }}">{{ $t['documents'] }} ({{ $documents->where('id_registration', $item->id_registration)->count() }})</a>
                    <a class="font-semibold text-primary" href="{{ route('portal.participant.certificate', ['id_registration' => $item->id_registration]) }}">{{ __('asic-documents.certificate.page_title') }}</a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6 text-sm text-on-surface-variant">{{ __('asic-participant.portal.no_registration') }} <a class="font-semibold text-primary" href="{{ route('portal.participant.registration') }}">{{ $t['start_registration'] }}</a></div>
        @endforelse
    </section>
</main>
@endsection
