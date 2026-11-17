{{-- Form login bersama. Variabel: $badge, $heading, $desc, $emailLabel, $emailPlaceholder, $icon --}}
<div>
    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E6F4EA] px-3 py-1 text-xs font-bold uppercase tracking-wider text-primary"><span class="material-symbols-outlined text-[16px]">{{ $icon }}</span>{{ $badge }}</span>
    <h1 class="mt-3 font-serif text-[32px] font-semibold leading-tight text-[#182820]">{{ $heading }}</h1>
    <p class="mt-2 text-[15px] leading-relaxed text-on-surface-variant">{{ $desc }}</p>

    <form action="{{ route('api.login') }}" method="POST" data-asic-login class="mt-6 space-y-4">
        @csrf
        <p data-form-error role="alert" class="hidden rounded-lg bg-error-container px-3 py-2 text-sm font-semibold text-on-error-container"></p>
        <x-ui.input :label="$emailLabel" name="email" type="email" :placeholder="$emailPlaceholder" iconLeft="mail" autocomplete="email" :value="session('registered_email')" required />
        <x-ui.input :label="__('asic-auth.password_label')" name="password" type="password" placeholder="••••••••" iconLeft="lock" autocomplete="current-password" required />
        <div class="flex items-center justify-between gap-3 text-sm">
            <label class="flex items-center gap-2 text-on-surface-variant"><input type="checkbox" name="remember" value="1" checked> {{ __('asic-auth.remember_me') }}</label>
            <a href="mailto:{{ config('asic.contact_email') ?: 'asic@agr.unand.ac.id' }}?subject=Reset%20password%20ASIC" class="font-semibold text-primary hover:underline">{{ __('asic-auth.forgot_password') }}</a>
        </div>
        <button type="submit" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary text-[15px] font-bold text-white transition hover:bg-[#168348]">
            {{ __('asic-auth.sign_in_btn') }}<span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </button>
    </form>
</div>
