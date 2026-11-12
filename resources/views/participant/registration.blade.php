@php
    $type = request()->query('type', 'presenter');
    $isListener = ($type === 'listener');
@endphp

@extends('layouts.portal', [
    'role' => 'participant',
    'roleName' => 'Registration',
    'nav' => [
        ['label' => __('asic-nav.dashboard'), 'route' => 'participant.dashboard', 'icon' => 'space_dashboard'],
        ['label' => __('asic-nav.payment'), 'route' => 'participant.payment.checkout', 'icon' => 'payments'],
        ['label' => __('asic-nav.documents'), 'route' => 'participant.documents', 'icon' => 'folder'],
    ]
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ 
    participationType: '{{ $isListener ? 'listener' : 'oral' }}',
    nationality: 'domestic',
    category: 'regular',
    includeFieldTrip: false,
    get calculatedFee() {
        if (this.nationality === 'domestic') {
            let base = this.participationType === 'listener' ? 750000 : (this.category === 'student' ? 1200000 : 1500000);
            if (this.includeFieldTrip) base += 450000;
            return 'IDR ' + base.toLocaleString('id-ID');
        } else {
            let base = this.participationType === 'listener' ? 80 : (this.category === 'student' ? 150 : 250);
            if (this.includeFieldTrip) base += 40;
            return 'USD ' + base;
        }
    }
}">
    
    <x-layout.page-header title="{{ __('asic-participant.reg_form_title') }}" subtitle="{{ __('asic-participant.reg_form_desc') }}">
        <div class="flex items-center gap-2 p-1 bg-surface-container-low rounded-xl border border-surface-container-highest text-xs">
            <span class="text-[11px] font-bold text-on-surface-variant px-2 uppercase">Mode:</span>
            <button type="button" @click="participationType = 'oral'" :class="participationType !== 'listener' ? 'bg-primary text-white font-bold shadow-xs' : 'text-on-surface'" class="px-2.5 py-1 rounded-lg transition">
                Presenter
            </button>
            <button type="button" @click="participationType = 'listener'" :class="participationType === 'listener' ? 'bg-primary text-white font-bold shadow-xs' : 'text-on-surface'" class="px-2.5 py-1 rounded-lg transition">
                Listener
            </button>
        </div>
    </x-layout.page-header>

    {{-- TODO(BE): ganti action route('fe.participant.register.post') --}}
    <form action="{{ route('fe.participant.payment.checkout') }}" method="GET" class="space-y-6">
        @csrf

        <!-- Section 1: Participation Type -->
        <x-ui.card title="{{ __('asic-participant.ticket_type_legend') }}" icon="how_to_reg">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-ui.radio-card name="participation_type" value="oral" title="Oral Presenter" description="Present in parallel technical session" price="From IDR 1.2M / USD 150" icon="record_voice_over" model="participationType" />
                <x-ui.radio-card name="participation_type" value="poster" title="Poster Presenter" description="Display scientific poster board" price="From IDR 1.0M / USD 120" icon="aspect_ratio" model="participationType" />
                <x-ui.radio-card name="participation_type" value="listener" title="Attendee / Listener" description="Access all sessions, kit & lunch" price="IDR 750K / USD 80" icon="hearing" model="participationType" />
            </div>
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
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Payment via IDR / QRIS</span>
                        </label>
                        <label :class="nationality === 'international' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="nationality" value="international" x-model="nationality" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">International</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Payment via USD / PayPal</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-2">Participant Category</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="category === 'student' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="category" value="student" x-model="category" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">Student (Undergrad/Postgrad)</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Requires Student ID verification</span>
                        </label>
                        <label :class="category === 'regular' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest'" class="p-3.5 rounded-xl border text-center cursor-pointer transition">
                            <input type="radio" name="category" value="regular" x-model="category" class="sr-only">
                            <span class="block text-xs font-bold text-on-surface">Regular / Academician</span>
                            <span class="block text-[11px] text-on-surface-variant mt-0.5">Lecturers, Researchers & General</span>
                        </label>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Section 3: Personal & Affiliation Information -->
        <x-ui.card title="{{ __('asic-participant.personal_info') }}" icon="person">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-ui.input label="{{ __('asic-participant.full_name') }}" name="name" placeholder="e.g. Dr. Budi Santoso, M.Sc." iconLeft="person" required />
                </div>
                <x-ui.input label="{{ __('asic-participant.email') }}" name="email" type="email" placeholder="budi@institution.ac.id" iconLeft="mail" required />
                <x-ui.input label="{{ __('asic-participant.phone') }}" name="phone" placeholder="+62 812-3456-7890" iconLeft="call" required />
                <x-ui.input label="{{ __('asic-participant.institution') }}" name="institution" placeholder="Universitas Andalas" iconLeft="school" required />
                <x-ui.input label="{{ __('asic-participant.department') }}" name="department" placeholder="Department of Agronomy" iconLeft="business" />
                <x-ui.input label="{{ __('asic-participant.city') }}" name="city" placeholder="Padang" iconLeft="location_city" required />
                <x-ui.input label="{{ __('asic-participant.country') }}" name="country" placeholder="Indonesia" iconLeft="public" required />
            </div>
        </x-ui.card>

        <!-- Section 4: Abstract Submission (Visible only for Presenter) -->
        <div x-show="participationType !== 'listener'" x-transition class="space-y-6">
            <x-ui.card title="{{ __('asic-participant.abstract_info') }}" icon="description">
                <div class="space-y-4">
                    <x-ui.input label="{{ __('asic-participant.abstract_title') }}" name="abstract_title" placeholder="e.g. Assessment of Organic Fertilizer on Crop Yield..." required />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.select label="{{ __('asic-participant.topic') }}" name="topic" required iconLeft="category">
                            <option value="sustainable_food">Sustainable Food Production</option>
                            <option value="agro_biodiversity">Agro-biodiversity & Climate Resilience</option>
                            <option value="food_security">Food Security & Nutrition</option>
                            <option value="agritech">Agri-tech & Digital Innovation</option>
                            <option value="policy_market">Markets, Policy & Rural Livelihoods</option>
                        </x-ui.select>

                        <x-ui.select label="{{ __('asic-participant.presentation_mode') }}" name="presentation_mode" required iconLeft="co_present">
                            <option value="onsite">{{ __('asic-participant.onsite') }}</option>
                            <option value="online">{{ __('asic-participant.online') }}</option>
                        </x-ui.select>
                    </div>

                    <x-ui.input label="{{ __('asic-participant.authors') }}" name="authors" placeholder="e.g. Budi Santoso (1), Ahmad Fauzi (2)" hint="List all co-authors with institution numbers" required />

                    <x-ui.textarea label="{{ __('asic-participant.abstract_body') }}" name="abstract_body" placeholder="Provide background, methods, results, and conclusion (150–250 words)..." rows="5" maxlength="500" required />

                    <x-ui.input label="{{ __('asic-participant.keywords') }}" name="keywords" placeholder="biochar, cassava, food security, soil biology" required />

                    <x-ui.file-upload label="{{ __('asic-participant.upload_abstract_doc') }}" name="abstract_file" accept=".docx,.pdf" maxSize="5 MB" hint="Optional manuscript document with figures/tables" />
                </div>
            </x-ui.card>
        </div>

        <!-- Section 5: Listener Field Trip Option (Visible only for Listener) -->
        <div x-show="participationType === 'listener'" x-transition>
            <x-ui.card title="{{ __('asic-participant.field_trip_title') }}" icon="nature_people">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-surface-container-low border border-surface-container-highest">
                    <input type="checkbox" id="field_trip" name="include_field_trip" x-model="includeFieldTrip" class="h-5 w-5 rounded border-outline/40 text-primary focus:ring-primary mt-0.5 cursor-pointer">
                    <label for="field_trip" class="cursor-pointer">
                        <span class="block text-sm font-bold text-on-surface">{{ __('asic-participant.field_trip_title') }}</span>
                        <span class="block text-xs text-on-surface-variant mt-1 leading-relaxed">{{ __('asic-participant.field_trip_desc') }}</span>
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
                    <x-ui.button variant="outline" href="{{ route('fe.participant.login') }}">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="w-full sm:w-auto px-8" iconRight="arrow_forward">
                        Proceed to Checkout
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    </form>
</div>
@endsection

