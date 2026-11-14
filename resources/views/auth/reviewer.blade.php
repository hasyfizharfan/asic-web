@extends('layouts.auth', ['title' => (__('asic-reviewer.portal_title') ?? 'Abstract Reviewer Portal') . ' - ASIC 2027'])

@section('content')
<div class="w-full max-w-md mx-auto">
    <div class="relative w-full overflow-hidden rounded-2xl bg-surface-container-lowest p-8 shadow-xl border border-surface-container-highest">
        <!-- Ambient Glow -->
        <div class="absolute -top-24 -right-24 w-60 h-60 rounded-full bg-primary-container/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-60 h-60 rounded-full bg-secondary-container/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <!-- Header Branding (Same style as Secretariat, Treasurer, Admin) -->
            <div class="text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-primary text-white flex items-center justify-center shadow-md mb-3">
                    <span class="material-symbols-outlined text-[28px]">rate_review</span>
                </div>
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-primary-fixed/40 text-primary text-[11px] font-bold tracking-wide uppercase mb-1">
                    {{ app()->getLocale() === 'id' ? 'Portal Reviewer Abstrak' : 'Abstract Reviewer Portal' }}
                </span>
                <h2 class="text-2xl font-bold text-on-surface font-display">
                    {{ __('asic-reviewer.login_card_title') ?? (app()->getLocale() === 'id' ? 'Reviewer Masuk' : 'Reviewer Sign In') }}
                </h2>
                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
                    {{ app()->getLocale() === 'id' ? 'Workspace komite akademik untuk meninjau dan mengevaluasi abstrak peserta ASIC 2027.' : 'Dedicated workspace to view assigned abstracts and submit academic review decisions.' }}
                </p>
            </div>

            <!-- Form -->
            <form action="{{ route('fe.reviewer.login') }}" method="POST" class="space-y-4">
                @csrf

                <x-ui.input label="{{ __('asic-reviewer.email_label') ?? 'Reviewer Email' }}" 
                            name="email" 
                            type="email" 
                            placeholder="{{ __('asic-reviewer.email_placeholder') ?? 'reviewer@example.org' }}" 
                            iconLeft="mail" 
                            required />

                <x-ui.input label="{{ __('asic-reviewer.password_label') ?? (app()->getLocale() === 'id' ? 'Kata Sandi' : 'Password') }}" 
                            name="password" 
                            type="password" 
                            placeholder="••••••••" 
                            iconLeft="lock" 
                            required />

                <div class="flex items-center justify-between text-xs pt-1">
                    <x-ui.checkbox label="{{ app()->getLocale() === 'id' ? 'Ingat Saya' : 'Remember Me' }}" name="remember" checked />
                    <a href="mailto:{{ $asic['email'] ?? 'asic@agr.unand.ac.id' }}?subject=Reviewer%20Password%20Reset" 
                       class="text-primary font-semibold hover:underline">
                        {{ __('asic-reviewer.forgot_password') ?? (app()->getLocale() === 'id' ? 'Lupa kata sandi?' : 'Forgot password?') }}
                    </a>
                </div>

                <div class="pt-2">
                    <x-ui.button type="submit" variant="primary" class="w-full py-3" iconRight="login">
                        {{ __('asic-reviewer.btn_signin') ?? (app()->getLocale() === 'id' ? 'Masuk ke Portal Reviewer' : 'Sign In to Reviewer Portal') }}
                    </x-ui.button>
                </div>
            </form>

            <!-- Bottom Evaluation Notice -->
            <div class="pt-3 border-t border-surface-container-highest/60 flex items-center justify-center gap-2 text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-primary text-[16px]">visibility_off</span>
                <span class="font-medium text-[11px]">{{ app()->getLocale() === 'id' ? 'Evaluasi Peer-Review: Double-Blind Active' : 'Peer-Review Evaluation: Double-Blind Active' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
