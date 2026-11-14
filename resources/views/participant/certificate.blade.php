@php
    $eligible = request()->query('eligible', '1') == '1';
@endphp

@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-layout.page-header title="{{ __('asic-participant.cert_title') }}" subtitle="{{ __('asic-participant.cert_desc') }}" :breadcrumbs="[
        ['label' => 'Portal', 'url' => route('fe.participant.dashboard')],
        ['label' => 'Certificate']
    ]">
        <!-- State Simulator Switcher -->
        <div class="flex items-center gap-1.5 p-1 bg-surface-container-low rounded-xl border border-surface-container-highest text-xs">
            <span class="text-[11px] font-bold text-on-surface-variant px-2 uppercase">Status:</span>
            <a href="{{ route('fe.participant.certificate', ['eligible' => '1']) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $eligible ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Available
            </a>
            <a href="{{ route('fe.participant.certificate', ['eligible' => '0']) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ !$eligible ? 'bg-primary text-white shadow-xs' : 'text-on-surface hover:bg-surface-container' }}">
                Locked
            </a>
        </div>
    </x-layout.page-header>

    @if($eligible)
        <!-- Certificate Available State -->
        <div class="space-y-6">
            <x-ui.alert variant="success" title="Certificate Officially Issued">
                Your certificate has been validated by the scientific committee and signed digitally with blockchain hash integrity.
            </x-ui.alert>

            <!-- Certificate Canvas Preview -->
            <x-ui.card padding="p-8 sm:p-12" class="border-4 border-double border-primary/30 bg-[#FFFDF9] text-center relative shadow-lg">
                <!-- Watermark Logo -->
                <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
                    <img src="{{ asset('images/asic/logo.svg') }}" alt="Watermark" class="w-80 h-80">
                </div>

                <div class="relative z-10 space-y-6 max-w-2xl mx-auto">
                    <!-- Crest & Institution -->
                    <div class="space-y-1">
                        <img src="{{ asset('images/asic/logo.svg') }}" alt="ASIC" class="w-16 h-16 mx-auto mb-2">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary font-display">Universitas Andalas • Faculty of Agriculture</p>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-[#17261D] font-display uppercase tracking-wide">Certificate of Presentation</h2>
                        <p class="text-xs font-mono text-on-surface-variant">No. 0892/UN16.03/ASIC/2027</p>
                    </div>

                    <div class="py-4">
                        <p class="text-xs text-on-surface-variant uppercase tracking-wider">This is proudly awarded to:</p>
                        <p class="text-2xl sm:text-3xl font-bold text-primary font-display mt-1 border-b-2 border-primary/30 pb-2 inline-block">
                            Prof. Dr. Ir. Ahmad Fauzi, M.Sc.
                        </p>
                        <p class="text-xs text-on-surface-variant mt-2 font-medium">Universitas Andalas, Indonesia</p>
                    </div>

                    <p class="text-xs text-on-surface leading-relaxed max-w-xl mx-auto text-justify sm:text-center">
                        in recognition of outstanding contribution as <b class="text-primary">Oral Presenter</b> for the paper titled:
                        <br>
                        <i class="font-serif text-sm block my-2 text-on-surface">"Application of Biochar and Arbuscular Mycorrhizal Fungi in Restoring Acid Tropical Agricultural Soils"</i>
                        at the 3rd Agrifood System International Conference (ASIC 2027) held on August 24–26, 2027 in Padang, West Sumatra, Indonesia.
                    </p>

                    <!-- Signatures & QR Verification -->
                    <div class="pt-8 border-t border-surface-container-highest grid grid-cols-1 sm:grid-cols-3 gap-6 items-end text-xs">
                        <div>
                            <p class="font-bold text-on-surface">Prof. Dr. Ir. Helmi, M.Sc.</p>
                            <p class="text-[11px] text-on-surface-variant">Dean, Faculty of Agriculture</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <img src="{{ asset('images/asic/qr-sample.svg') }}" alt="Verification Barcode" class="w-20 h-20 p-1 bg-white border border-surface-container-highest rounded-lg">
                            <span class="text-[10px] font-mono text-on-surface-variant mt-1">Verify: asic.unand.ac.id/v/0892</span>
                        </div>
                        <div>
                            <p class="font-bold text-on-surface">Dr. Ir. Rahmat Hidayat</p>
                            <p class="text-[11px] text-on-surface-variant">Chair, ASIC 2027 Committee</p>
                        </div>
                    </div>
                </div>

                <x-slot:footer>
                    <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <span class="text-on-surface-variant font-mono">Format: High-Res PDF (300 DPI)</span>
                        <div class="flex items-center gap-2">
                            <x-ui.button size="sm" variant="outline" iconLeft="share" @click="$store.toast.add('Verification link copied to clipboard!', 'info')">
                                Share Credential
                            </x-ui.button>
                            <x-ui.button size="sm" variant="primary" iconLeft="download" @click="$store.toast.add('Downloading official PDF certificate...', 'success')">
                                {{ __('asic-participant.download_pdf') }}
                            </x-ui.button>
                        </div>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </div>
    @else
        <!-- Certificate Locked State -->
        <x-ui.card>
            <div class="text-center py-10 px-4 space-y-4 max-w-lg mx-auto">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-surface-container-high text-on-surface-variant flex items-center justify-center">
                    <span class="material-symbols-outlined text-[36px]">lock</span>
                </div>
                
                <h3 class="text-xl font-bold text-on-surface font-display">{{ __('asic-participant.cert_locked_title') }}</h3>
                <p class="text-xs text-on-surface-variant leading-relaxed">{{ __('asic-participant.cert_locked_desc') }}</p>

                <!-- Eligibility Checklist -->
                <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest text-xs text-left space-y-2.5">
                    <p class="font-bold text-on-surface uppercase tracking-wider text-[11px]">Certificate Requirements Checklist:</p>
                    <div class="flex items-center gap-2 text-primary font-medium">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>Registration invoice payment reconciled</span>
                    </div>
                    <div class="flex items-center gap-2 text-primary font-medium">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>Abstract accepted by scientific committee</span>
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">radio_button_unchecked</span>
                        <span>On-site venue check-in (recorded on Day 1)</span>
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">radio_button_unchecked</span>
                        <span>Technical presentation session verified by Session Chair</span>
                    </div>
                </div>

                <div class="pt-2">
                    <x-ui.button variant="outline" href="{{ route('fe.participant.attendance-qr') }}" iconLeft="qr_code_2">
                        View Check-in Pass
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
@endsection

