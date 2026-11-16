@php($t = trans('asic-participant.portal.profile'))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.portal.profile.title')])

@section('content')
<main class="mx-auto max-w-3xl space-y-5" x-data="{ i18n: @js($t), saving: false, fieldErrors: {}, async saveProfile(e) { this.saving=true; this.fieldErrors={}; const r=await window.asicApi.put('/api/me/profile', Object.fromEntries(new FormData(e.target))); this.saving=false; if(!r.ok){this.fieldErrors=r.errors||{};window.asicApi.showToast(r.error||this.i18n.profile_failed);return;} window.asicApi.showToast(r.data?.message||this.i18n.profile_saved,'success'); }, async savePassword(e) { this.saving=true; this.fieldErrors={}; const r=await window.asicApi.put('/api/me/password', Object.fromEntries(new FormData(e.target))); this.saving=false; if(!r.ok){this.fieldErrors=r.errors||{};window.asicApi.showToast(r.error||this.i18n.password_failed);return;} e.target.reset();window.asicApi.showToast(r.data?.message||this.i18n.password_saved,'success'); } }">
    <header><h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1></header>
    <form class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5" @submit.prevent="saveProfile($event)">
        @csrf
        @foreach(['prefix', 'first_name', 'last_name', 'passport_no', 'job_title', 'phone', 'institution', 'address', 'city', 'country', 'postcode'] as $field)
            <label class="block text-sm font-semibold">{{ $t['fields'][$field] }}<input name="{{ $field }}" value="{{ old($field, $participant?->{$field}) }}" class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
            <p x-show="fieldErrors['{{ $field }}']" x-text="fieldErrors['{{ $field }}']?.[0]" class="text-sm text-error"></p>
        @endforeach
        <p class="text-xs text-on-surface-variant">{{ $t['country_note'] }}</p>
        <button :disabled="saving" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50">{{ $t['save_profile'] }}</button>
    </form>
    <form class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5" @submit.prevent="savePassword($event)">
        @csrf
        <h2 class="font-bold">{{ $t['change_password'] }}</h2>
        <label class="block text-sm font-semibold">{{ $t['current_password'] }}<input name="current_password" type="password" required class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
        <label class="block text-sm font-semibold">{{ $t['new_password'] }}<input name="password" type="password" required class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
        <label class="block text-sm font-semibold">{{ $t['confirm_password'] }}<input name="password_confirmation" type="password" required class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
        <p x-show="fieldErrors.current_password || fieldErrors.password" x-text="fieldErrors.current_password?.[0] || fieldErrors.password?.[0]" class="text-sm text-error"></p>
        <button :disabled="saving" class="rounded-xl border border-primary px-5 py-2.5 text-sm font-bold text-primary disabled:opacity-50">{{ $t['update_password'] }}</button>
    </form>
</main>
@endsection
