@php($t = trans('asic-participant.portal.abstract'))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.portal.abstract.title'), 'nav' => $participantNav])

@section('content')
<main class="mx-auto max-w-3xl space-y-6" x-data="{
    i18n: @js($t), registrations: [], registration: null, abstractSubmission: null,
    loading: true, submitting: false, loadError: '', fieldErrors: {},
    async init() { await this.load(); },
    async load() {
        this.loading = true;
        const response = await window.asicApi.get('/api/registrations');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || this.i18n.load_failed; return; }
        this.registrations = response.data?.data?.data || [];
        const requested = new URLSearchParams(window.location.search).get('id_registration');
        this.registration = this.registrations.find(row => String(row.id_registration) === String(requested)) || this.registrations[0] || null;
        this.abstractSubmission = this.registration?.abstract_submission || null;
        if (!this.registration) this.loadError = this.i18n.no_registration;
    },
    async submit(event) {
        this.fieldErrors = {};
        this.submitting = true;
        const response = await window.asicApi.upload('/api/registrations/' + this.registration.id_registration + '/abstract', new FormData(event.target));
        this.submitting = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; window.asicApi.showToast(response.error || this.i18n.submit_failed); return; }
        await this.load();
        window.asicApi.showToast(response.data?.message || this.i18n.submitted, 'success');
    }
}">
    <header>
        <h1 class="text-2xl font-bold text-on-surface">{{ $t['heading'] }}</h1>
        <p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p>
    </header>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <section x-show="registration" class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <p class="text-sm">{{ $t['registration'] }} <strong x-text="registration?.id_registration"></strong> · {{ $t['payment'] }} <strong x-text="registration?.invoice?.status_pembayaran || @js(__('asic-participant.flow.invoice_after_acceptance'))"></strong></p>
        <p x-show="!registration?.invoice" class="text-xs text-on-surface-variant">{{ __('asic-participant.flow.abstract_payment_note') }}</p>
        <p class="text-xs"><a href="{{ route('public.downloads') }}" target="_blank" rel="noopener" class="font-semibold text-primary underline">{{ __('asic-documents.public.title') }}</a></p>
        <template x-if="abstractSubmission">
            <div class="space-y-3 rounded-xl bg-surface-container-low p-4">
                <p class="text-sm">{{ $t['title_label'] }}: <strong x-text="abstractSubmission.judul_abstrak"></strong></p>
                <p class="text-sm">{{ $t['status_label'] }}: <strong x-text="abstractSubmission.status_abstrak"></strong></p>
                <a :href="'/api/registrations/' + registration.id_registration + '/abstract/download'" class="inline-flex rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">{{ $t['download'] }}</a>
            </div>
        </template>
        <form x-show="!abstractSubmission" class="space-y-4" @submit.prevent="submit($event)">
            <label class="block text-sm font-semibold">{{ $t['field_title'] }}
                <input name="judul_abstrak" required maxlength="255" class="mt-1 block w-full rounded-xl border-surface-container-highest">
            </label>
            <p x-show="fieldErrors.judul_abstrak" x-text="fieldErrors.judul_abstrak?.[0]" class="text-sm text-error"></p>
            <label class="block text-sm font-semibold">{{ $t['field_scope'] }}
                <select name="scope" required class="mt-1 block w-full rounded-xl border-surface-container-highest">
                    <option value="">{{ $t['choose_scope'] }}</option>@foreach(config('abstract-scopes', []) as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach
                </select>
            </label>
            <p x-show="fieldErrors.scope" x-text="fieldErrors.scope?.[0]" class="text-sm text-error"></p>
            <label class="block text-sm font-semibold">{{ $t['field_method'] }}
                <select name="metode_presentasi" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option value="Oral">Oral</option><option value="Poster">Poster</option></select>
            </label>
            <p x-show="fieldErrors.metode_presentasi" x-text="fieldErrors.metode_presentasi?.[0]" class="text-sm text-error"></p>
            <label class="block text-sm font-semibold">{{ $t['field_file'] }}
                <input type="file" name="file_abstrak" required accept=".pdf,.doc,.docx" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
            </label>
            <p x-show="fieldErrors.file_abstrak" x-text="fieldErrors.file_abstrak?.[0]" class="text-sm text-error"></p>
            <button type="submit" :disabled="submitting" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="submitting ? i18n.submitting : i18n.submit"></button>
        </form>
    </section>
</main>
@endsection
