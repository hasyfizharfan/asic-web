@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => __('asic-treasurer.refund_policy.title')])

@php $t = trans('asic-treasurer.refund_policy'); @endphp

@section('content')
<main class="mx-auto max-w-3xl space-y-5">
    <header>
        <h1 class="text-[27px] font-bold leading-tight">{{ $t['title'] }}</h1>
        <p class="mt-1 text-[15px] text-on-surface-variant">{{ $t['lead'] }}</p>
    </header>
    <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <h2 class="font-bold">{{ $t['rules_title'] }}</h2>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach($t['rules'] as $rule)<li class="flex gap-2"><span class="material-symbols-outlined text-[18px] text-primary">arrow_right</span>{{ $rule }}</li>@endforeach
        </ul>
        <a href="{{ route('portal.treasurer.payments.index') }}" class="mt-4 inline-flex min-h-10 items-center gap-2 rounded-[9px] border border-surface-container-highest px-5 text-sm font-bold text-primary hover:border-primary">{{ $t['to_payments'] }}</a>
    </section>
</main>
@endsection
