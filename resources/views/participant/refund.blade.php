@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.portal.refund.title')])

@php $t = trans('asic-participant.portal.refund'); @endphp

@section('content')
<main class="mx-auto max-w-3xl space-y-5">
    <header>
        <h1 class="text-[27px] font-bold leading-tight">{{ $t['title'] }}</h1>
        <p class="mt-1 text-[15px] text-on-surface-variant">{{ $t['policy_lead'] }}</p>
    </header>
    <div class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-2xl border border-[#cfe6d6] bg-[#E6F4EA] p-5">
            <span class="material-symbols-outlined text-primary">check_circle</span>
            <h2 class="mt-2 font-bold text-primary">{{ $t['refundable_title'] }}</h2>
            <p class="mt-1 text-sm">{{ $t['refundable_body'] }}</p>
        </article>
        <article class="rounded-2xl border border-error/20 bg-error-container/50 p-5">
            <span class="material-symbols-outlined text-error">block</span>
            <h2 class="mt-2 font-bold text-on-error-container">{{ $t['not_refundable_title'] }}</h2>
            <p class="mt-1 text-sm">{{ $t['not_refundable_body'] }}</p>
        </article>
    </div>
    <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <h2 class="font-bold">{{ $t['how_title'] }}</h2>
        <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-on-surface-variant">
            @foreach($t['how_steps'] as $step)<li>{{ $step }}</li>@endforeach
        </ol>
        <a href="{{ route('public.contact') }}" class="mt-4 inline-flex min-h-10 items-center gap-2 rounded-[9px] bg-primary px-5 text-sm font-bold text-white hover:bg-primary-container"><span class="material-symbols-outlined text-[18px]">support_agent</span>{{ $t['contact'] }}</a>
    </section>
</main>
@endsection
