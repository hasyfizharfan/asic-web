@extends('layouts.auth', ['title' => __('asic-auth.split.register_title') . ' - ASIC 2027', 'photo' => 'images/asic/photos/farmers-planting.jpg', 'asideTitle' => __('asic-auth.split.register_aside'), 'formWidth' => 'max-w-xl'])

@php $R = trans('asic-auth.split'); @endphp

@section('content')
<h1 class="font-serif text-[30px] font-semibold leading-tight text-[#182820]">{{ $R['register_title'] }}</h1>
<p class="mt-1 text-[15px] text-on-surface-variant">{{ $R['register_desc'] }}</p>

<form action="{{ route('api.register') }}" method="POST" data-asic-api-form data-success-redirect="{{ route('auth.participant.login') }}" class="mt-5 grid gap-x-4 gap-y-3 sm:grid-cols-2">
    @csrf
    <p data-form-error role="alert" class="hidden rounded-lg bg-error-container px-3 py-2 text-sm font-semibold text-on-error-container sm:col-span-2"></p>
    <label class="block text-sm font-semibold">{{ $R['full_name'] }}
        <input name="full_name" required maxlength="150" autocomplete="name" placeholder="{{ $R['full_name_ph'] }}" class="mt-1 w-full">
    </label>
    <label class="block text-sm font-semibold">{{ __('asic-auth.email_label') }}
        <input name="email" type="email" required maxlength="100" autocomplete="email" placeholder="nama@institusi.ac.id" class="mt-1 w-full">
    </label>
    <label class="block text-sm font-semibold">{{ $R['affiliation'] }}
        <input name="affiliation" maxlength="150" autocomplete="organization" placeholder="{{ $R['affiliation_ph'] }}" class="mt-1 w-full">
    </label>
    <label class="block text-sm font-semibold">{{ $R['phone'] }}
        <input name="phone" maxlength="20" autocomplete="tel" placeholder="+62 812…" class="mt-1 w-full">
    </label>
    <label class="block text-sm font-semibold">{{ __('asic-auth.country_label') }}
        <input name="country" required maxlength="100" value="Indonesia" autocomplete="country-name" aria-describedby="country-help" class="mt-1 w-full">
    </label>
    <p id="country-help" class="flex items-end gap-2 pb-2.5 text-xs leading-snug text-on-surface-variant"><span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">payments</span>{{ __('asic-auth.country_help') }}</p>
    <label class="block text-sm font-semibold">{{ __('asic-auth.password_label') }}
        <input name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="{{ $R['password_ph'] }}" class="mt-1 w-full">
    </label>
    <label class="block text-sm font-semibold">{{ $R['password_confirm'] }}
        <input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full">
    </label>
    <label class="flex items-start gap-2.5 text-sm text-on-surface-variant sm:col-span-2">
        <input name="agree_terms" type="checkbox" value="1" required class="mt-0.5">
        <span>{!! __('asic-public.register_terms', [
            'terms' => '<a href="'.e(route('public.terms')).'" target="_blank" rel="noopener" class="font-semibold text-primary underline">'.e(__('asic-public.nav.terms')).'</a>',
            'privacy' => '<a href="'.e(route('public.privacy')).'" target="_blank" rel="noopener" class="font-semibold text-primary underline">'.e(__('asic-public.nav.privacy')).'</a>',
            'ethics' => '<a href="'.e(route('public.ethics')).'" target="_blank" rel="noopener" class="font-semibold text-primary underline">'.e(__('asic-public.nav.ethics')).'</a>',
        ]) !!}</span>
    </label>
    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-primary text-[15px] font-bold text-white transition hover:bg-[#168348] sm:col-span-2">
        {{ $R['create_account'] }}<span class="material-symbols-outlined text-[20px]">arrow_forward</span>
    </button>
</form>
<div class="mt-4 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
    <x-ui.spam-note compact class="inline-flex items-center gap-1 text-[13px]" />
    <p class="text-[15px] text-on-surface-variant">{{ $R['have_account'] }} <a href="{{ route('auth.participant.login') }}" class="font-bold text-primary hover:underline">{{ __('asic-auth.sign_in_btn') }}</a></p>
</div>
@endsection
