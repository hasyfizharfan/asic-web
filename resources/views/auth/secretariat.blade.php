@extends('layouts.auth', ['title' => __('asic-auth.secretariat_portal') . ' - ASIC 2027'])

@section('content')
<div class="w-full max-w-md mx-auto">
    <div class="relative w-full overflow-hidden rounded-2xl bg-surface-container-lowest p-8 shadow-xl border border-surface-container-highest">
        <!-- Ambient Glow -->
        <div class="absolute -top-24 -right-24 w-60 h-60 rounded-full bg-secondary-container/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-60 h-60 rounded-full bg-primary-container/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <!-- Header Branding -->
            <div class="text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-primary text-white flex items-center justify-center shadow-md mb-3">
                    <span class="material-symbols-outlined text-[28px]">badge</span>
                </div>
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-primary-fixed/40 text-primary text-[11px] font-bold tracking-wide uppercase mb-1">
                    {{ __('asic-auth.secretariat_portal') }}
                </span>
                <h2 class="text-2xl font-bold text-on-surface font-display">{{ __('asic-auth.secretariat_welcome') }}</h2>
                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">{{ __('asic-auth.secretariat_desc') }}</p>
            </div>

            <!-- Form -->
            <form action="{{ route('fe.secretariat.login') }}" method="POST" class="space-y-4">
                @csrf
                <x-ui.input label="{{ __('asic-auth.staff_email_label') }}" 
                            name="email" 
                            type="email" 
                            placeholder="{{ __('asic-auth.staff_email_placeholder') }}" 
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
                    <a href="mailto:{{ $asic['email'] ?? 'asic@agr.unand.ac.id' }}?subject=Secretariat%20Password%20Reset" class="text-primary font-semibold hover:underline">{{ __('asic-auth.forgot_password') }}</a>
                </div>

                <div class="pt-2">
                    <x-ui.button type="submit" variant="primary" class="w-full py-3" iconRight="login">
                        {{ __('asic-auth.sign_in_btn') }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
