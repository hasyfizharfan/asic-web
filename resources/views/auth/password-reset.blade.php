@extends('layouts.auth', ['title' => __('asic-auth.split.reset_title') . ' - ASIC 2027'])

@php $R = trans('asic-auth.split'); @endphp

@section('content')
<span class="inline-flex items-center gap-1.5 rounded-full bg-[#E6F4EA] px-3 py-1 text-xs font-bold uppercase tracking-wider text-primary"><span class="material-symbols-outlined text-[16px]">lock_reset</span>{{ $R['reset_title'] }}</span>
<h1 class="mt-4 font-serif text-[32px] font-semibold leading-tight text-[#182820]">{{ $R['reset_title'] }}</h1>
<p class="mt-2 text-[15px] text-on-surface-variant">{{ $R['reset_desc'] }}</p>
<form action="{{ route('api.password.update') }}" method="POST" data-asic-api-form data-success-redirect="{{ route('login') }}" class="mt-8 space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <p data-form-error role="alert" class="hidden rounded-lg bg-error-container px-3 py-2 text-sm font-semibold text-on-error-container"></p>
    <x-ui.input :label="__('asic-auth.email_label')" name="email" type="email" :value="$email" required />
    <x-ui.input :label="$R['new_password']" name="password" type="password" autocomplete="new-password" required />
    <x-ui.input :label="$R['password_confirm']" name="password_confirmation" type="password" autocomplete="new-password" required />
    <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-primary text-[15px] font-bold text-white hover:bg-[#168348]">{{ $R['save_password'] }}</button>
</form>
@endsection
