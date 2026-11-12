@extends('layouts.auth', [
    'title' => __('asic-auth.participant_portal') . ' - ASIC 2027'
])

@section('content')
<div class="w-full max-w-4xl lg:max-w-5xl bg-surface-container-lowest rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row border border-surface-container-highest/80 relative">
    
    <!-- Left Brand & Editorial Section -->
    <div class="lg:w-5/12 bg-primary p-8 sm:p-10 lg:p-12 flex flex-col justify-between relative overflow-hidden text-white min-h-[380px] lg:min-h-[520px]">
        <!-- Ambient Graphic Accents -->
        <div class="absolute -right-16 -bottom-16 w-64 h-64 rounded-full bg-secondary opacity-20 pointer-events-none blur-2xl"></div>
        <div class="absolute -left-12 -top-12 w-48 h-48 rounded-full bg-primary-fixed opacity-15 pointer-events-none blur-xl"></div>
        
        <!-- Top Branding -->
        <div class="relative z-10 space-y-5">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white p-2 shadow-md flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/asic/logo.svg') }}" alt="ASIC 2027 Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="block text-2xl font-bold tracking-tight text-white font-display leading-none">ASIC 2027</span>
                    <span class="block text-[11px] text-[#a7f4b6] font-semibold uppercase tracking-wider mt-1">{{ $asic['short_title'] ?? 'ASIC 2027' }}</span>
                </div>
            </div>

            <div class="pt-2">
                <span class="inline-block px-3 py-1 rounded-full bg-primary-container text-[#a7f4b6] text-[11px] font-bold tracking-wide uppercase mb-3">
                    {{ __('asic-auth.conference_edition') }}
                </span>
                <h2 class="text-lg sm:text-xl font-bold text-white font-display leading-relaxed">
                    {{ __('asic-auth.tagline') }}
                </h2>
            </div>
        </div>

        <!-- Middle Info Strip -->
        <div class="relative z-10 py-5 my-4 border-y border-white/15 space-y-3 text-xs text-white/90">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[18px] text-[#F5C518] shrink-0">calendar_month</span>
                <span>{{ $asic['dates'] ?? 'August 24–26, 2027' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[18px] text-[#F5C518] shrink-0">location_on</span>
                <span>{{ $asic['venue'] ?? 'Convention Hall, Universitas Andalas, Padang' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[18px] text-[#F5C518] shrink-0">school</span>
                <span>{{ $asic['organizer'] ?? 'Faculty of Agriculture, Universitas Andalas' }}</span>
            </div>
        </div>

        <!-- Bottom Notice -->
        <div class="relative z-10 text-[11px] text-white/70">
            <p>&copy; {{ date('Y') }} ASIC 2027 · Universitas Andalas. All official submissions are peer-reviewed.</p>
        </div>
    </div>

    <!-- Right Login Form Section -->
    <div class="lg:w-7/12 p-8 sm:p-10 lg:p-12 flex flex-col justify-between bg-surface-container-lowest">
        <div>
            <!-- Header -->
            <div class="mb-6 space-y-1.5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-[11px] font-bold tracking-wide uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    <span>{{ __('asic-auth.participant_portal') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-on-surface font-display tracking-tight">
                    {{ __('asic-auth.participant_welcome') }}
                </h1>
                <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                    {{ __('asic-auth.participant_desc') }}
                </p>
            </div>

            <!-- Form -->
            <form action="{{ route('fe.participant.login') }}" method="POST" class="space-y-4">
                @csrf
                <x-ui.input label="{{ __('asic-auth.email_label') }}" 
                            name="email" 
                            type="email" 
                            placeholder="{{ __('asic-auth.email_placeholder') }}" 
                            iconLeft="mail" 
                            required />

                <x-ui.input label="{{ __('asic-auth.password_label') }}" 
                            name="password" 
                            type="password" 
                            placeholder="••••••••" 
                            iconLeft="lock" 
                            required />

                <div class="flex items-center justify-between text-xs pt-1">
                    <x-ui.checkbox label="{{ __('asic-auth.remember_me') }}" name="remember" checked />
                    <a href="mailto:{{ $asic['email'] ?? 'asic@agr.unand.ac.id' }}?subject=Password%20Reset" class="text-primary font-semibold hover:underline">
                        {{ __('asic-auth.forgot_password') }}
                    </a>
                </div>

                <div class="pt-3">
                    <x-ui.button type="submit" variant="primary" class="w-full py-3.5 text-sm" iconRight="login">
                        {{ __('asic-auth.sign_in_btn') }}
                    </x-ui.button>
                </div>
            </form>
        </div>

        <!-- Footer link -->
        <div class="mt-8 pt-5 border-t border-surface-container-highest text-center text-xs text-on-surface-variant">
            <span>{{ __('asic-auth.no_account') }}</span>
            <a href="{{ route('fe.participant.registration') }}" class="font-bold text-primary hover:underline ml-1">
                {{ __('asic-auth.register_now') }}
            </a>
        </div>
    </div>
</div>
@endsection
