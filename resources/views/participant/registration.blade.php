@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-participant.portal.reg.title')])

@php
    $t = trans('asic-participant.portal.reg');
    $currency = \App\Support\Currency::forParticipant($participant ?? null);
    $startType = request()->query('type') === 'listener' ? 'Listener' : 'Oral Presenter';
@endphp

@section('content')
<div class="mx-auto max-w-4xl space-y-6" x-data="{
    i18n: @js($t),
    participationType: @js($startType),
    currency: @js($currency),
    category: '', attendanceMode: '', publicationOption: '',
    events: [], fees: [], loading: true, loadError: '', fieldErrors: {},
    idEvent: '', idPeriod: '', submitting: false,
    order: { type: ['Student', 'Regular', 'Committee'], mode: ['Luring', 'Daring'], pub: ['Tidak', 'Prosiding', 'Jurnal'] },
    async init() {
        try {
            const [events, fees] = await Promise.all([window.asicApi.get('/api/public/events'), window.asicApi.get('/api/public/registration-fees')]);
            if (!events.ok || !fees.ok) throw new Error(events.error || fees.error || this.i18n.load_failed);
            const eventData = events.data?.data, feeData = fees.data?.data;
            this.events = Array.isArray(eventData) ? eventData : (eventData?.data || []);
            const allFees = Array.isArray(feeData) ? feeData : (feeData?.data || []);
            this.fees = allFees.filter(f => (f.mata_uang || 'IDR') === this.currency);
            this.idEvent = String(this.fees[0]?.id_event || '');
            this.syncPeriod();
            if (!this.fees.length) this.loadError = @js(__('asic-participant.flow.no_fee_currency')).replace(':currency', this.currency);
        } catch (error) { this.loadError = error.message || this.i18n.load_failed; }
        finally { this.loading = false; }
    },
    get eventOptions() { return this.events.filter(e => this.fees.some(f => String(f.id_event) === String(e.id_event))); },
    get periodFees() { return this.fees.filter(f => String(f.id_event) === this.idEvent && String(f.id_period) === this.idPeriod); },
    get periods() { return [...new Map(this.fees.filter(f => String(f.id_event) === this.idEvent).map(f => [String(f.id_period), f.period])).values()].filter(Boolean); },
    options(field, key) { const values = new Set(this.periodFees.map(f => f[field])); return this.order[key].filter(v => values.has(v)); },
    pick(field, key, current) { const list = this.options(field, key); return list.includes(current) ? current : (list[0] || ''); },
    syncPeriod() { this.idPeriod = String(this.periods[0]?.id_period || ''); this.syncOptions(); },
    syncOptions() {
        this.category = this.pick('registration_type', 'type', this.category || 'Regular');
        this.attendanceMode = this.pick('attendance_mode', 'mode', this.attendanceMode || 'Luring');
        this.publicationOption = this.pick('tujuan_publikasi', 'pub', this.publicationOption || 'Tidak');
    },
    get selectedFee() { return this.periodFees.find(f => f.registration_type === this.category && f.attendance_mode === this.attendanceMode && f.tujuan_publikasi === this.publicationOption); },
    money(v) { return window.asicMoney(v, this.currency); },
    async handleSubmit() {
        this.fieldErrors = {}; this.submitting = true;
        const res = await window.asicApi.post('/api/registrations', {
            id_event: Number(this.idEvent), id_period: Number(this.idPeriod), registration_type: this.category,
            attendance_mode: this.attendanceMode, participation_type: this.participationType, publication_option: this.publicationOption
        });
        this.submitting = false;
        if (!res.ok || res.status !== 201) { this.fieldErrors = res.errors || {}; window.asicApi.showToast(res.error || this.i18n.submit_failed); return; }
        const id = res.data?.data?.id_registration;
        const target = this.participationType === 'Listener' ? @js(route('portal.participant.payment.checkout')) : @js(route('portal.participant.abstract'));
        if (id) window.location.assign(target + '?id_registration=' + encodeURIComponent(id));
        else window.asicApi.showToast(this.i18n.saved_no_invoice);
    }
}">
    <header>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">{{ $t['eyebrow'] }}</p>
        <h1 class="mt-1 text-[27px] font-bold leading-tight sm:text-[34px]">{{ $t['title'] }}</h1>
        <p class="mt-1 text-[15px] text-on-surface-variant">{{ $t['subtitle'] }}</p>
    </header>

    {{-- Data pribadi diambil dari profil --}}
    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined">badge</span></span>
            <div>
                <p class="font-bold">{{ $participant?->full_name ?: auth()->user()->email }}</p>
                <p class="text-sm text-on-surface-variant">{{ collect([$participant?->institution, $participant?->country, auth()->user()->email])->filter()->implode(' · ') }}</p>
                <p class="mt-1 text-sm"><span class="rounded-full bg-[#FEF6D8] px-2 py-0.5 font-semibold text-[#7a5d00]">{{ str_replace(':currency', $currency, $t['currency_note']) }}</span></p>
            </div>
        </div>
        <a href="{{ route('portal.participant.profile') }}" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] border border-surface-container-highest px-4 py-2 text-sm font-semibold text-primary hover:border-primary"><span class="material-symbols-outlined text-[18px]">edit</span>{{ $t['edit_profile'] }}</a>
    </section>

    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container" style="display: none;"></p>

    <form x-show="!loading && !loadError" @submit.prevent="handleSubmit()" class="space-y-6" style="display: none;">
        {{-- 1. Event & periode --}}
        <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 sm:p-6">
            <h2 class="flex items-center gap-2 text-lg font-bold"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-sm text-white">1</span>{{ $t['step_event'] }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold">{{ $t['event'] }}
                    <select x-model="idEvent" @change="syncPeriod()" class="mt-1.5 w-full">
                        <template x-for="event in eventOptions" :key="event.id_event"><option :value="String(event.id_event)" x-text="event.nama_event"></option></template>
                    </select>
                    <span x-show="fieldErrors.id_event" x-text="fieldErrors.id_event?.[0]" class="mt-1 block text-sm font-normal text-error"></span>
                </label>
                <label class="block text-sm font-semibold">{{ $t['period'] }}
                    <select x-model="idPeriod" @change="syncOptions()" class="mt-1.5 w-full">
                        <template x-for="period in periods" :key="period.id_period"><option :value="String(period.id_period)" x-text="period.nama_periode"></option></template>
                    </select>
                    <span x-show="fieldErrors.id_period" x-text="fieldErrors.id_period?.[0]" class="mt-1 block text-sm font-normal text-error"></span>
                </label>
            </div>
        </section>

        {{-- 2. Jenis partisipasi --}}
        <fieldset class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 sm:p-6">
            <legend class="sr-only">{{ $t['step_type'] }}</legend>
            <h2 class="flex items-center gap-2 text-lg font-bold" aria-hidden="true"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-sm text-white">2</span>{{ $t['step_type'] }}</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach(['Oral Presenter' => 'record_voice_over', 'Poster Presenter' => 'image', 'Listener' => 'hearing'] as $value => $icon)
                    <label class="relative flex cursor-pointer gap-3 rounded-xl border-2 p-4 transition" :class="participationType === @js($value) ? 'border-primary bg-[#E6F4EA]' : 'border-surface-container-highest hover:border-[#9aaa9e]'">
                        <input type="radio" name="participation_type" value="{{ $value }}" x-model="participationType" class="mt-1">
                        <span>
                            <span class="flex items-center gap-1.5 font-bold"><span class="material-symbols-outlined text-[20px] text-primary">{{ $icon }}</span>{{ $t['types'][$value]['title'] }}</span>
                            <span class="mt-1 block text-sm text-on-surface-variant">{{ $t['types'][$value]['desc'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <span x-show="fieldErrors.participation_type" x-text="fieldErrors.participation_type?.[0]" class="mt-2 block text-sm text-error"></span>
        </fieldset>

        {{-- 3. Kategori, kehadiran, publikasi --}}
        <section class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 sm:p-6">
            <h2 class="flex items-center gap-2 text-lg font-bold"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-sm text-white">3</span>{{ $t['step_options'] }}</h2>
            @foreach([['category', 'registration_type', 'type', $t['category'], $t['categories']], ['attendanceMode', 'attendance_mode', 'mode', $t['mode'], $t['modes']], ['publicationOption', 'tujuan_publikasi', 'pub', $t['publication'], $t['publications']]] as [$model, $field, $key, $label, $labels])
                <fieldset class="mt-5">
                    <legend class="text-sm font-semibold">{{ $label }}</legend>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="value in options(@js($field), @js($key))" :key="value">
                            <label class="cursor-pointer rounded-xl border-2 px-4 py-2.5 text-sm font-semibold transition" :class="{{ $model }} === value ? 'border-primary bg-[#E6F4EA] text-primary' : 'border-surface-container-highest hover:border-[#9aaa9e]'">
                                <input type="radio" class="sr-only" :value="value" x-model="{{ $model }}">
                                <span x-text="(@js($labels))[value] || value"></span>
                            </label>
                        </template>
                    </div>
                    <span x-show="fieldErrors.{{ $field === 'tujuan_publikasi' ? 'publication_option' : $field }}" x-text="fieldErrors.{{ $field === 'tujuan_publikasi' ? 'publication_option' : $field }}?.[0]" class="mt-1 block text-sm text-error"></span>
                </fieldset>
            @endforeach
            <p class="mt-4 text-sm text-on-surface-variant">{{ $t['publication_hint'] }}</p>
        </section>

        {{-- Ringkasan --}}
        <section class="sticky bottom-4 z-10 flex flex-col gap-4 rounded-2xl border border-primary/30 bg-surface-container-lowest p-5 shadow-lg sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ $t['total'] }}</p>
                <p class="font-mono text-2xl font-bold text-primary" x-text="selectedFee ? money(selectedFee.final_amount) : i18n.fee_unavailable"></p>
                <p class="text-sm text-on-surface-variant" x-text="participationType === 'Listener' ? i18n.next_listener : i18n.next_presenter"></p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('portal.participant.dashboard') }}" class="inline-flex min-h-11 items-center rounded-[9px] border border-surface-container-highest px-5 text-sm font-semibold hover:border-primary">{{ $t['cancel'] }}</a>
                <button type="submit" :disabled="submitting || !selectedFee" class="inline-flex min-h-11 items-center gap-2 rounded-[9px] bg-primary px-6 text-sm font-bold text-white hover:bg-primary-container">
                    <span x-text="submitting ? i18n.submitting : i18n.submit"></span><span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </div>
        </section>
    </form>
</div>
@endsection
