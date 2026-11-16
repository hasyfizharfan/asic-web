@extends('layouts.portal', ['role' => 'participant', 'roleName' => $participant?->full_name ?: __('asic-nav.portal_participant'), 'userName' => $participant?->full_name ?: auth()->user()->email])

@php
    $t = trans('asic-participant.portal.dashboard');
    $reg = $registration;
    $abstract = $reg?->abstractSubmission;
    $invoice = $reg?->invoice;
    $current = $journey['current'] ?? null;
    $q = $reg ? ['id_registration' => $reg->id_registration] : [];
    $trackLabel = match ($reg?->participation_type) {
        'Oral Presenter' => __('asic-participant.role_oral'),
        'Poster Presenter' => __('asic-participant.role_poster'),
        'Listener' => __('asic-participant.role_listener'),
        default => $reg?->participation_type,
    };
    $event = $reg?->event;
    $eventDate = $event?->tanggal_mulai ? \App\Support\DateRange::format($event->tanggal_mulai, $event->tanggal_selesai) : __('asic-landing.hero_info.tba');
    $due = $invoice?->tanggal_jatuh_tempo_baru ?? $invoice?->tanggal_jatuh_tempo;
    $actionKey = match (true) {
        $journey === null => null,
        $current === null => 'done',
        $current === 'selection' && $abstract?->status_abstrak === 'Ditolak' => 'selection_failed',
        $current === 'payment' && $invoice?->status_pembayaran === 'Ditolak' => 'payment_rejected',
        $current === 'payment' && in_array($invoice?->status_pembayaran, ['Kedaluwarsa', 'Kadaluwarsa'], true) => 'payment_failed',
        default => $current,
    };
    $actionRoute = match ($actionKey) {
        'abstract', 'selection' => 'portal.participant.abstract',
        'payment', 'payment_rejected', 'payment_failed', 'verification' => 'portal.participant.payment.checkout',
        'reregistration', 'seminar' => 'portal.participant.attendance',
        'certificate', 'done' => 'portal.participant.certificate',
        default => 'portal.participant.documents',
    };
    $actionParams = [
        'title' => $abstract?->judul_abstrak ?? '-',
        'invoice' => $invoice?->nomor_invoice ?? '',
        'due' => $due ? \Illuminate\Support\Carbon::parse($due)->translatedFormat('j F Y') : '-',
    ];
    $quick = collect($participantNav ?? [])->reject(fn ($item) => ($item['route'] ?? '') === 'portal.participant.dashboard');
    $stepIcons = ['account' => 'person', 'registration' => 'how_to_reg', 'abstract' => 'description', 'selection' => 'rule', 'loa_invoice' => 'receipt_long', 'payment' => 'payments', 'verification' => 'verified', 'reregistration' => 'qr_code_scanner', 'seminar' => 'groups', 'certificate' => 'workspace_premium'];
    $statusTone = fn (?string $s) => match ($s) {
        'Diterima', 'Terverifikasi', 'Terdaftar' => 'bg-[#E6F4EA] text-primary',
        'Ditolak', 'Kedaluwarsa', 'Kadaluwarsa', 'Dibatalkan' => 'bg-error-container text-on-error-container',
        'Diajukan', 'Menunggu Verifikasi' => 'bg-[#E8F0FE] text-[#1a4fa0]',
        default => 'bg-[#FEF6D8] text-[#7a5d00]',
    };
@endphp

@section('content')
<main class="space-y-6">
    {{-- Akses cepat --}}
    <section class="rounded-2xl border border-[#cfe6d6] bg-[#E6F4EA]/70 p-5">
        <h2 class="text-lg font-bold text-primary">{{ $t['quick_access'] }}</h2>
        <p class="text-xs text-on-surface-variant">{{ $t['quick_access_hint'] }}</p>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach($quick as $item)
                @php $href = \Illuminate\Support\Facades\Route::has($item['route'] ?? '') ? route($item['route'], $q) : '#'; @endphp
                <a href="{{ $href }}" class="group flex min-h-[72px] flex-col justify-between rounded-xl border border-surface-container-highest bg-surface-container-lowest p-3 shadow-sm transition hover:border-primary">
                    <span class="material-symbols-outlined text-[22px] text-primary">{{ $item['icon'] ?? 'circle' }}</span>
                    <span class="mt-2 text-sm font-semibold leading-tight text-on-surface group-hover:text-primary">{{ $item['label'] }} <span aria-hidden="true">↗</span></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Sambutan --}}
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="rounded-full bg-[#E6F4EA] px-2.5 py-1 font-semibold text-primary">{{ $t['portal_badge'] }}</span>
                @if($reg)<span class="font-mono text-on-surface-variant">ID: #{{ $reg->id_registration }}</span>@endif
            </div>
            <h1 class="mt-2 text-[27px] font-bold leading-tight sm:text-[34px]">{{ __('asic-participant.welcome_back', ['name' => $participant?->full_name ?: auth()->user()->email]) }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                {{ $participant?->institution ?: 'ASIC 2027' }}
                @if($reg) · <span class="font-semibold text-primary">{{ $trackLabel }}</span> · {{ $t['conference_date'] }}: {{ $eventDate }}@endif
            </p>
        </div>
        @if($reg?->isPresenter())
            <a href="{{ route('portal.participant.abstract', $q) }}" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-container">
                <span class="material-symbols-outlined text-[18px]">visibility</span>{{ $t['view_abstract'] }}
            </a>
        @endif
    </header>

    @if(! $reg)
        <div class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-6 text-sm text-on-surface-variant">{{ __('asic-participant.portal.no_registration') }} <a class="font-semibold text-primary" href="{{ route('portal.participant.registration') }}">{{ $t['start_registration'] }}</a></div>
    @else
        @if($journey['cancelled'])
            <div role="alert" class="rounded-2xl border border-error/30 bg-error-container p-4 text-sm text-on-error-container">{{ $t['cancelled'] }}</div>
        @endif

        {{-- Stepper tahapan --}}
        <section aria-label="{{ $t['progress'] }}" class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
            <ol class="flex min-w-[720px] items-start">
                @foreach($journey['steps'] as $i => $step)
                    @php $state = $step['state']; @endphp
                    <li class="relative flex flex-1 flex-col items-center text-center" @if($state === 'current' || $state === 'failed') aria-current="step" @endif>
                        @if(! $loop->last)
                            <span class="absolute left-1/2 top-4 h-0.5 w-full {{ $state === 'done' ? 'bg-primary' : 'bg-surface-container-highest' }}" aria-hidden="true"></span>
                        @endif
                        <span class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold
                            {{ match ($state) { 'done' => 'bg-primary text-white', 'current' => 'bg-[#168348] text-white ring-4 ring-[#E6F4EA]', 'failed' => 'bg-error text-white ring-4 ring-error-container', default => 'bg-surface-container text-on-surface-variant' } }}">
                            @if($state === 'done')<span class="material-symbols-outlined text-[18px]">check</span>
                            @elseif($state === 'failed')<span class="material-symbols-outlined text-[18px]">priority_high</span>
                            @else{{ $i + 1 }}@endif
                        </span>
                        <span class="mt-2 px-1 text-xs font-semibold {{ in_array($state, ['current', 'failed'], true) ? 'rounded-full bg-[#E6F4EA] px-2 py-0.5 text-primary' : ($state === 'done' ? 'text-primary' : 'text-on-surface-variant') }}">{{ $i + 1 }}. {{ $t['steps'][$step['key']] }}</span>
                        <span class="mt-0.5 text-[11px] text-on-surface-variant">{{ $t['states'][$state] }}</span>
                    </li>
                @endforeach
            </ol>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                {{-- Langkah berikutnya --}}
                @php $action = $t['actions'][$actionKey] ?? $t['actions']['loa_invoice']; @endphp
                <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-[#168348] p-6 text-white shadow-sm">
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#F5C518] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-[#182820]">
                        <span class="material-symbols-outlined text-[14px]">{{ $stepIcons[$current] ?? 'flag' }}</span>{{ $t['phase'] }}: {{ $current ? $t['steps'][$current] : $t['states']['done'] }}
                    </span>
                    <h2 class="mt-3 text-xl font-bold sm:text-2xl">{{ $t['next_action'] }}: {{ __('asic-participant.portal.dashboard.actions.'.$actionKey.'.title', $actionParams) }}</h2>
                    <p class="mt-2 max-w-xl text-sm text-white/90">{{ __('asic-participant.portal.dashboard.actions.'.$actionKey.'.body', $actionParams) }}</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route($actionRoute, $q) }}" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] bg-[#F5C518] px-5 py-2 text-sm font-bold text-[#182820] hover:bg-[#fed649]">{{ $action['cta'] }} <span aria-hidden="true">→</span></a>
                        <a href="{{ route('portal.participant.documents', $q) }}" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] bg-white/15 px-5 py-2 text-sm font-semibold text-white hover:bg-white/25"><span class="material-symbols-outlined text-[18px]">folder</span>{{ $t['documents'] }} ({{ $documents->where('id_registration', $reg->id_registration)->count() }})</a>
                    </div>
                    <span class="pointer-events-none absolute -bottom-16 -right-16 h-56 w-56 rounded-full border border-white/15" aria-hidden="true"></span>
                    <span class="pointer-events-none absolute -bottom-8 -right-8 h-40 w-40 rounded-full border border-white/15" aria-hidden="true"></span>
                </section>

                {{-- Ringkasan --}}
                <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined">assignment</span></span>
                            <div>
                                <h2 class="text-lg font-bold">{{ $reg->isPresenter() ? $t['summary'] : $t['registration_summary'] }}</h2>
                                <p class="text-xs text-on-surface-variant">{{ $event?->nama_event ?? 'ASIC 2027' }}</p>
                            </div>
                        </div>
                        @php $badge = $reg->isPresenter() ? ($abstract?->status_abstrak ?? $t['not_submitted']) : ($invoice?->status_pembayaran ?? $reg->registration_status); @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusTone($badge) }}">{{ $badge }}</span>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-4 rounded-xl bg-surface-container-low p-4 text-sm sm:grid-cols-4">
                        <div><dt class="text-[11px] font-semibold uppercase text-on-surface-variant">{{ $t['paper_id'] }}</dt><dd class="mt-1 font-mono font-semibold text-primary">#{{ $reg->id_registration }}</dd></div>
                        @if($reg->isPresenter())
                            <div><dt class="text-[11px] font-semibold uppercase text-on-surface-variant">{{ $t['scope'] }}</dt><dd class="mt-1">{{ $abstract?->scope ?? '-' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase text-on-surface-variant">{{ $t['submitted_at'] }}</dt><dd class="mt-1">{{ $abstract?->tanggal_submit ? \Illuminate\Support\Carbon::parse($abstract->tanggal_submit)->translatedFormat('j M Y') : '-' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase text-on-surface-variant">{{ $t['mode'] }}</dt><dd class="mt-1 font-semibold text-primary">{{ $abstract?->metode_presentasi ?? '-' }}</dd></div>
                        @else
                            <div><dt class="text-[11px] font-semibold uppercase text-on-surface-variant">{{ $t['attendance_mode'] }}</dt><dd class="mt-1">{{ $reg->attendance_mode }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase text-on-surface-variant">Invoice</dt><dd class="mt-1">{{ $invoice?->nomor_invoice ?? '-' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase text-on-surface-variant">{{ $t['payment_status'] }}</dt><dd class="mt-1 font-semibold text-primary">{{ $invoice?->status_pembayaran ?? '-' }}</dd></div>
                        @endif
                    </dl>
                    @if($reg->isPresenter() && $abstract)
                        <p class="mt-4 text-[11px] font-semibold uppercase text-on-surface-variant">{{ $t['abstract_title'] }}</p>
                        <p class="mt-1 font-bold">{{ $abstract->judul_abstrak }}</p>
                    @endif
                    @if($reg->isPresenter())
                        <div class="mt-4 flex justify-end"><a href="{{ route('portal.participant.abstract', $q) }}" class="text-sm font-semibold text-primary">{{ $t['manage_abstract'] }} →</a></div>
                    @endif
                </section>

                @if($channel)
                    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined">forum</span></span>
                            <div><h2 class="font-bold">{{ $t['community'] }}</h2><p class="text-xs text-on-surface-variant">{{ $t['community_body'] }}</p></div>
                        </div>
                        <a href="{{ $channel->url }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] bg-primary px-5 py-2 text-sm font-bold text-white hover:bg-primary-container"><span class="material-symbols-outlined text-[18px]">group</span>{{ $t['join'] }} ({{ $channel->nama_kanal }})</a>
                    </section>
                @endif

                @if($registrations->count() > 1)
                    <section class="space-y-3">
                        <h2 class="text-lg font-bold">{{ $t['my_registrations'] }}</h2>
                        @foreach($registrations as $item)
                            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-4">
                                <div><h3 class="font-bold">{{ $item->event?->nama_event ?? 'ASIC 2027' }}</h3><p class="text-xs text-on-surface-variant">#{{ $item->id_registration }} · {{ $item->participation_type }} · {{ $item->registration_status }}</p></div>
                                <div class="flex flex-wrap gap-3 text-sm">
                                    @if($item->invoice)<a class="font-semibold text-primary" href="{{ route('portal.participant.payment.checkout', ['id_registration' => $item->id_registration]) }}">Invoice {{ $item->invoice->nomor_invoice }}</a>@endif
                                    <a class="font-semibold text-primary" href="{{ route('portal.participant.documents', ['id_registration' => $item->id_registration]) }}">{{ $t['documents'] }}</a>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                    <h2 class="flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-primary">calendar_month</span>{{ $t['milestones'] }}</h2>
                    <ul class="mt-3 space-y-2">
                        @forelse($milestones as $date)
                            @php $past = \Illuminate\Support\Carbon::parse($date->tanggal_selesai ?? $date->tanggal_mulai)->endOfDay()->isPast(); @endphp
                            <li class="rounded-xl p-3 text-sm {{ $past ? 'bg-surface-container-low' : 'bg-[#E6F4EA]' }}">
                                <p class="font-semibold">{{ app()->getLocale() === 'en' ? ($date->label_en ?: $date->label_id) : ($date->label_id ?: $date->label_en) }}</p>
                                <p class="font-mono text-xs text-primary">{{ $date->tanggal_mulai->translatedFormat('j M Y') }}@if($date->tanggal_selesai) – {{ $date->tanggal_selesai->translatedFormat('j M Y') }}@endif</p>
                            </li>
                        @empty
                            <li class="text-sm text-on-surface-variant">{{ $t['no_milestones'] }}</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                    <h2 class="flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-primary">notifications</span>{{ $t['latest_notifications'] }}</h2>
                    <ul class="mt-3 space-y-3">
                        @forelse($latestNotifications as $note)
                            <li class="border-b border-surface-container-highest pb-3 last:border-0 last:pb-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold {{ $note['read_at'] ? '' : 'text-primary' }}">{{ $note['title'] }}</p>
                                    <span class="shrink-0 text-[11px] text-on-surface-variant">{{ $note['created_at'] ? \Illuminate\Support\Carbon::parse($note['created_at'])->diffForHumans() : '' }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-on-surface-variant">{{ $note['body'] }}</p>
                            </li>
                        @empty
                            <li class="text-sm text-on-surface-variant">{{ $t['no_notifications'] }}</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('portal.participant.notifications') }}" class="mt-3 block text-center text-sm font-semibold text-primary">{{ $t['all_notifications'] }}</a>
                </section>

                <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
                    <h2 class="flex items-center gap-2 font-bold"><span class="material-symbols-outlined text-primary">support_agent</span>{{ $t['helpdesk'] }}</h2>
                    <ul class="mt-3 space-y-2">
                        @forelse($contacts as $contact)
                            <li class="rounded-xl bg-surface-container-low p-3 text-sm">
                                <p class="font-semibold">{{ $contact->nama }}</p>
                                <p class="text-xs text-on-surface-variant">{{ app()->getLocale() === 'en' ? ($contact->peran_en ?: $contact->peran_id) : ($contact->peran_id ?: $contact->peran_en) }}</p>
                                @if($contact->email)<a href="mailto:{{ $contact->email }}" class="mt-1 block font-mono text-xs text-primary">{{ $contact->email }}</a>@endif
                                @if($contact->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D+/', '', $contact->whatsapp) }}" target="_blank" rel="noopener" class="block font-mono text-xs text-primary">WhatsApp {{ $contact->whatsapp }}</a>@endif
                            </li>
                        @empty
                            <li class="text-sm text-on-surface-variant">{{ $t['helpdesk_empty'] }}</li>
                        @endforelse
                    </ul>
                </section>
            </aside>
        </div>
    @endif
</main>
@endsection
