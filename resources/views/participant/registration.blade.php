@php
    $type = request()->query('type', 'presenter');
    $isListener = ($type === 'listener');
@endphp

@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Registrasi'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    participationType: '{{ $isListener ? 'Listener' : 'Oral Presenter' }}',
    currency: @js(\App\Support\Currency::forParticipant($participant ?? null)),
    nationality: 'domestic',
    category: 'Regular',
    attendanceMode: 'Luring',
    publicationOption: 'Tidak',
    events: [], fees: [], loading: true, loadError: '', fieldErrors: {},
    idEvent: '', idPeriod: '',
    submitting: false,
    async init() {
        try {
            const [events, fees] = await Promise.all([
                window.asicApi.get('/api/public/events'),
                window.asicApi.get('/api/public/registration-fees')
            ]);
            if (!events.ok || !fees.ok) throw new Error(events.error || fees.error || 'Data pendaftaran tidak dapat dimuat.');
            const eventData = events.data?.data;
            const feeData = fees.data?.data;
            this.events = Array.isArray(eventData) ? eventData : (eventData?.data || []);
            const allFees = Array.isArray(feeData) ? feeData : (feeData?.data || []);
            this.fees = allFees.filter(f => (f.mata_uang || 'IDR') === this.currency);
            this.idEvent = String(this.fees[0]?.id_event || this.events[0]?.id_event || '');
            this.syncPeriod();
            if (!this.events.length || !this.fees.length) this.loadError = @js(__('asic-participant.flow.no_fee_currency')).replace(':currency', this.currency);
        } catch (error) { this.loadError = error.message || 'Data pendaftaran gagal dimuat.'; }
        finally { this.loading = false; }
    },
    get periods() { return [...new Map(this.fees.filter(f => String(f.id_event) === this.idEvent).map(f => [String(f.id_period), f.period])).values()]; },
    syncPeriod() {
        const firstFee = this.fees.find(f => String(f.id_event) === this.idEvent);
        this.idPeriod = String(firstFee?.id_period || this.periods[0]?.id_period || '');
        if (firstFee) {
            this.category = firstFee.registration_type;
            this.attendanceMode = firstFee.attendance_mode;
            this.publicationOption = firstFee.tujuan_publikasi;
        }
    },
    syncFeeOptions() {
        const fee = this.fees.find(f => String(f.id_event) === this.idEvent && String(f.id_period) === this.idPeriod);
        if (fee) {
            this.category = fee.registration_type;
            this.attendanceMode = fee.attendance_mode;
            this.publicationOption = fee.tujuan_publikasi;
        }
    },
    get selectedFee() { return this.fees.find(f => String(f.id_event) === this.idEvent && String(f.id_period) === this.idPeriod && f.registration_type === this.category && f.attendance_mode === this.attendanceMode && f.tujuan_publikasi === this.publicationOption); },
    get calculatedFee() {
        return this.selectedFee ? window.asicMoney(this.selectedFee.final_amount, this.selectedFee.mata_uang) : @js(__('asic-participant.flow.fee_unavailable'));
    },
    async handleSubmit(e) {
        this.fieldErrors = {};
        this.submitting = true;
        const payload = {
            id_event: Number(this.idEvent),
            id_period: Number(this.idPeriod),
            registration_type: this.category,
            attendance_mode: this.attendanceMode,
            participation_type: this.participationType,
            publication_option: this.publicationOption
        };
        const res = await window.asicApi.post('/api/registrations', payload);
        this.submitting = false;
        if (!res.ok || res.status !== 201) {
            this.fieldErrors = res.errors || {};
            window.asicApi.showToast(res.error || 'Pendaftaran gagal. Periksa data lalu coba lagi.');
            return;
        }
        const registrationId = res.data?.data?.id_registration;
        const target = this.participationType === 'Listener' ? '{{ route('portal.participant.payment.checkout') }}' : '{{ route('portal.participant.abstract') }}';
        if (registrationId) window.location.assign(target + '?id_registration=' + encodeURIComponent(registrationId));
        else window.asicApi.showToast('Pendaftaran tersimpan, tetapi data invoice belum tersedia. Buka halaman pembayaran dari dashboard.');
    }
}">
    
    <x-layout.page-header title="{{ __('asic-participant.reg_form_title') }}" subtitle="{{ __('asic-participant.reg_form_desc') }}">
        <div class="flex items-center gap-2 p-1 bg-surface-container-low rounded-xl border border-surface-container-highest text-xs">
            <span class="text-[11px] font-bold text-on-surface-variant px-2 uppercase">Mode:</span>
                <button type="button" @click="participationType = 'Oral Presenter'" :class="participationType !== 'Listener' ? 'bg-primary text-white font-bold shadow-xs' : 'text-on-surface'" class="px-2.5 py-1 rounded-lg transition">
                Presenter
            </button>
                <button type="button" @click="participationType = 'Listener'" :class="participationType === 'Listener' ? 'bg-primary text-white font-bold shadow-xs' : 'text-on-surface'" class="px-2.5 py-1 rounded-lg transition">
                Listener
            </button>
        </div>
    </x-layout.page-header>

    <form action="#" method="POST" class="space-y-6" @submit.prevent="handleSubmit($event)">
        @csrf

        <x-ui.card title="Kegiatan dan periode" icon="event">
            <div x-show="loading" role="status" class="text-sm text-on-surface-variant">Memuat event dan tarif…</div>
            <div x-show="loadError" x-text="loadError" role="alert" class="text-sm text-error"></div>
            <div x-show="!loading && !loadError" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="text-xs font-semibold">Event
                    <select x-model="idEvent" @change="syncPeriod()" class="mt-1 w-full rounded-xl border-surface-container-highest text-sm">
                        <template x-for="event in events.filter(event => fees.some(fee => String(fee.id_event) === String(event.id_event)))" :key="event.id_event">
                            <option :value="String(event.id_event)" x-text="event.nama_event"></option>
                        </template>
                    </select>
                    <p x-show="fieldErrors.id_event" x-text="fieldErrors.id_event?.[0]" class="mt-1 text-error"></p>
                </label>
                <label class="text-xs font-semibold">Periode
                    <select x-model="idPeriod" @change="syncFeeOptions()" class="mt-1 w-full rounded-xl border-surface-container-highest text-sm">
                        <template x-for="period in periods" :key="period.id_period">
                            <option :value="String(period.id_period)" x-text="period.nama_periode"></option>
                        </template>
                    </select>
                    <p x-show="fieldErrors.id_period" x-text="fieldErrors.id_period?.[0]" class="mt-1 text-error"></p>
                </label>
            </div>
            <p x-show="!loading && !loadError && !selectedFee" class="mt-3 text-sm text-error">Tarif untuk kategori dan mode yang dipilih belum tersedia.</p>
            <div class="mt-4 flex gap-4">
                <label class="text-xs font-semibold">Mode kehadiran
                    <select x-model="attendanceMode" class="mt-1 block rounded-xl border-surface-container-highest text-sm">
                        <option value="Luring">Luring</option><option value="Daring">Daring</option>
                    </select>
                    <span x-show="fieldErrors.attendance_mode" x-text="fieldErrors.attendance_mode?.[0]" class="mt-1 block text-error"></span>
                </label>
                <label class="text-xs font-semibold">Tujuan publikasi
                    <select x-model="publicationOption" class="mt-1 block rounded-xl border-surface-container-highest text-sm">
                        <option value="Tidak">Tidak</option><option value="Prosiding">Prosiding</option><option value="Jurnal">Jurnal</option>
                    </select>
                    <span x-show="fieldErrors.publication_option" x-text="fieldErrors.publication_option?.[0]" class="mt-1 block text-error"></span>
                </label>
            </div>
        </x-ui.card>

        <!-- Section 1: Participation Type -->
        <x-ui.card title="{{ __('asic-participant.ticket_type_legend') }}" icon="how_to_reg">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-ui.radio-card name="participation_type" value="Oral Presenter" title="Oral Presenter" description="Present in parallel technical session" price="See active fee" icon="record_voice_over" model="participationType" />
                <x-ui.radio-card name="participation_type" value="Poster Presenter" title="Poster Presenter" description="Display scientific poster board" price="See active fee" icon="aspect_ratio" model="participationType" />
                <x-ui.radio-card name="participation_type" value="Listener" title="Attendee / Listener" description="Access all sessions, kit & lunch" price="See active fee" icon="hearing" model="participationType" />
            </div>
            <p x-show="fieldErrors.participation_type" x-text="fieldErrors.participation_type?.[0]" class="mt-2 text-sm text-error"></p>
        </x-ui.card>

        <!-- Section 2: Category & Nationality -->
        <x-ui.card title="{{ __('asic-participant.category_legend') }}" icon="category">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-2">Origin / Nationality</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="nationality === 'domestic' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="nationality" value="domestic" x-model="nationality" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">Indonesian (Domestic)</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Asal peserta untuk informasi</span>
                        </label>
                        <label :class="nationality === 'international' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="nationality" value="international" x-model="nationality" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">International</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Asal peserta untuk informasi</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-2">Participant Category</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label :class="category === 'Student' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="category" value="Student" x-model="category" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">Student (Undergrad/Postgrad)</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Requires Student ID verification</span>
                        </label>
                        <label :class="category === 'Regular' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="category" value="Regular" x-model="category" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">Regular / Academician</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Lecturers, Researchers & General</span>
                        </label>
                        <label :class="category === 'Committee' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="category" value="Committee" x-model="category" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">Committee</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Organizing committee</span>
                        </label>
                    </div>
                    <p x-show="fieldErrors.registration_type" x-text="fieldErrors.registration_type?.[0]" class="mt-2 text-sm text-error"></p>
                </div>
            </div>
        </x-ui.card>

        <!-- Section 3: Personal & Affiliation Information -->
        <x-ui.card title="{{ __('asic-participant.personal_info') }}" icon="person">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-ui.input label="{{ __('asic-participant.full_name') }}" name="name" placeholder="e.g. Dr. Budi Santoso, M.Sc." iconLeft="person" />
                </div>
                <x-ui.input label="{{ __('asic-participant.email') }}" name="email" type="email" placeholder="budi@institution.ac.id" iconLeft="mail" />
                <x-ui.input label="{{ __('asic-participant.phone') }}" name="phone" placeholder="+62 812-3456-7890" iconLeft="call" />
                <x-ui.input label="{{ __('asic-participant.institution') }}" name="institution" placeholder="Universitas Andalas" iconLeft="school" />
                <x-ui.input label="{{ __('asic-participant.department') }}" name="department" placeholder="Department of Agronomy" iconLeft="business" />
                <x-ui.input label="{{ __('asic-participant.city') }}" name="city" placeholder="Padang" iconLeft="location_city" />
                <x-ui.input label="{{ __('asic-participant.country') }}" name="country" placeholder="Indonesia" iconLeft="public" />
            </div>
        </x-ui.card>

        <!-- Section 4: Abstract Submission (Visible only for Presenter) -->
        <div x-show="false" class="rounded-xl border border-surface-container-highest bg-surface-container-low p-4 text-sm text-on-surface-variant">
            {{ __('asic-participant.flow.abstract_payment_note') }}
        </div>
        <div x-show="false" class="space-y-6">
            <x-ui.card title="{{ __('asic-participant.abstract_info') }}" icon="description">
                <div class="space-y-4">
                    <x-ui.input label="{{ __('asic-participant.abstract_title') }}" name="abstract_title" placeholder="e.g. Assessment of Organic Fertilizer on Crop Yield..." />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.select label="{{ __('asic-participant.topic') }}" name="topic" iconLeft="category">
                            <option value="sustainable_food">Sustainable Food Production</option>
                            <option value="agro_biodiversity">Agro-biodiversity & Climate Resilience</option>
                            <option value="food_security">Food Security & Nutrition</option>
                            <option value="agritech">Agri-tech & Digital Innovation</option>
                            <option value="policy_market">Markets, Policy & Rural Livelihoods</option>
                        </x-ui.select>

                        <x-ui.select label="{{ __('asic-participant.presentation_mode') }}" name="presentation_mode" iconLeft="co_present">
                            <option value="onsite">{{ __('asic-participant.onsite') }}</option>
                            <option value="online">{{ __('asic-participant.online') }}</option>
                        </x-ui.select>
                    </div>

                    <x-ui.input label="{{ __('asic-participant.authors') }}" name="authors" placeholder="e.g. Budi Santoso (1), Ahmad Fauzi (2)" hint="List all co-authors with institution numbers" />

                    <x-ui.textarea label="{{ __('asic-participant.abstract_body') }}" name="abstract_body" placeholder="Provide background, methods, results, and conclusion (150–250 words)..." rows="5" maxlength="500" />

                    <x-ui.input label="{{ __('asic-participant.keywords') }}" name="keywords" placeholder="biochar, cassava, food security, soil biology" />

                    <x-ui.file-upload label="{{ __('asic-participant.upload_abstract_doc') }}" name="abstract_file" accept=".docx,.pdf" maxSize="5 MB" hint="Optional manuscript document with figures/tables" />
                </div>
            </x-ui.card>
        </div>

        <!-- Section 5: Listener Field Trip Option (Visible only for Listener) -->
        <div x-show="participationType === 'Listener'" x-transition>
            <x-ui.card title="{{ __('asic-participant.field_trip_title') }}" icon="nature_people">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-surface-container-low border border-surface-container-highest">
                    <input type="checkbox" id="field_trip" name="include_field_trip" disabled class="h-5 w-5 rounded border-outline/40 text-primary mt-0.5 cursor-not-allowed">
                    <label for="field_trip" class="cursor-pointer">
                        <span class="block text-sm font-bold text-on-surface">{{ __('asic-participant.field_trip_title') }} — belum tersedia</span>
                        <span class="block text-xs text-on-surface-variant mt-1 leading-relaxed">Pilihan add-on belum disediakan oleh API saat ini.</span>
                        <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-fixed text-primary font-mono">
                            {{ __('asic-participant.field_trip_fee') }}
                        </span>
                    </label>
                </div>
            </x-ui.card>
        </div>

        <!-- Section 6: Order Summary & Submission -->
        <x-ui.card>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <span class="text-xs uppercase tracking-wider font-semibold text-on-surface-variant">Estimated Total Fee</span>
                    <p class="text-2xl font-bold text-primary font-mono" x-text="calculatedFee"></p>
                    <p class="text-[11px] text-on-surface-variant">Formal invoice will be issued upon form submission.</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <x-ui.button variant="outline" href="{{ route('login') }}">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="w-full sm:w-auto px-8" iconRight="arrow_forward" ::disabled="submitting || !selectedFee || loading || !!loadError">
                        <span x-text="submitting ? 'Processing...' : 'Proceed to Checkout'">Proceed to Checkout</span>
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    </form>
</div>
@endsection

