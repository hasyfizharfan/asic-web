@extends('layouts.auth', ['title' => __('asic-auth.password_label').' - ASIC 2027'])

@section('content')
<div class="w-full max-w-md mx-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-8 shadow-xl">
    <div class="mb-6 text-center">
        <span class="material-symbols-outlined mb-2 text-4xl text-primary">lock_reset</span>
        <h1 class="text-2xl font-bold text-on-surface">Reset kata sandi</h1>
        <p class="mt-2 text-sm text-on-surface-variant">Buat kata sandi baru untuk akun ASIC Anda.</p>
    </div>

    <form action="{{ route('api.password.update') }}" method="POST" data-asic-api-form data-success-redirect="{{ route('login') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <p data-form-error role="alert" class="hidden text-xs font-semibold text-error"></p>
        <x-ui.input label="Email" name="email" type="email" value="{{ $email }}" required />
        <x-ui.input label="Kata sandi baru" name="password" type="password" autocomplete="new-password" required />
        <x-ui.input label="Konfirmasi kata sandi" name="password_confirmation" type="password" autocomplete="new-password" required />
        <x-ui.button type="submit" variant="primary" class="w-full">Simpan kata sandi baru</x-ui.button>
    </form>
</div>
@endsection
