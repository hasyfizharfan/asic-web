@extends('layouts.auth', ['title' => 'Daftar Peserta - ASIC 2027'])

@section('content')
<main class="mx-auto w-full max-w-2xl rounded-3xl border border-surface-container-highest bg-surface-container-lowest p-6 shadow-2xl sm:p-10">
    <a href="{{ route('portal.landing') }}" class="text-sm font-semibold text-primary">← ASIC 2027</a>
    <header class="mt-5 space-y-2">
        <p class="text-xs font-bold uppercase tracking-wider text-primary">Portal Peserta</p>
        <h1 class="text-2xl font-bold text-on-surface sm:text-3xl">Buat akun peserta</h1>
        <p class="text-sm text-on-surface-variant">Buat akun terlebih dahulu. Setelah masuk, Anda dapat memilih event dan mendaftar dengan fee aktif.</p>
    </header>

    <form action="{{ route('api.register') }}" method="POST" data-asic-api-form data-success-redirect="{{ route('auth.participant.login') }}" class="mt-7 space-y-4">
        @csrf
        <p data-form-error role="alert" class="hidden text-sm font-semibold text-error"></p>
        <label class="block text-sm font-semibold">Nama lengkap
            <input name="full_name" required maxlength="150" autocomplete="name" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
        </label>
        <label class="block text-sm font-semibold">Email
            <input name="email" type="email" required maxlength="100" autocomplete="email" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
        </label>
        <label class="block text-sm font-semibold">Institusi / afiliasi
            <input name="affiliation" maxlength="150" autocomplete="organization" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
        </label>
        <label class="block text-sm font-semibold">Nomor telepon
            <input name="phone" maxlength="20" autocomplete="tel" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
        </label>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-semibold">Kata sandi
                <input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
            </label>
            <label class="block text-sm font-semibold">Konfirmasi kata sandi
                <input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 block w-full rounded-xl border-surface-container-highest text-sm">
            </label>
        </div>
        <label class="flex items-start gap-2 text-sm text-on-surface-variant">
            <input name="agree_terms" type="checkbox" value="1" required class="mt-1 rounded border-surface-container-highest">
            <span>Saya menyetujui syarat dan ketentuan serta pedoman etika publikasi ASIC.</span>
        </label>
        <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white">Buat akun</button>
    </form>
    <p class="mt-6 border-t border-surface-container-highest pt-5 text-center text-sm text-on-surface-variant">
        Sudah punya akun? <a href="{{ route('auth.participant.login') }}" class="font-bold text-primary">Masuk</a>
    </p>
</main>
@endsection
