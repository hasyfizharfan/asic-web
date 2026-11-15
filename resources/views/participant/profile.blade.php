@extends('layouts.portal', ['role' => 'participant', 'roleName' => 'Profil'])

@section('content')
<main class="mx-auto max-w-3xl space-y-5" x-data="{ saving: false, fieldErrors: {}, async saveProfile(e) { this.saving=true; this.fieldErrors={}; const r=await window.asicApi.put('/api/me/profile', Object.fromEntries(new FormData(e.target))); this.saving=false; if(!r.ok){this.fieldErrors=r.errors||{};window.asicApi.showToast(r.error||'Profil gagal diperbarui.');return;} window.asicApi.showToast(r.data?.message||'Profil diperbarui.','success'); }, async savePassword(e) { this.saving=true; this.fieldErrors={}; const r=await window.asicApi.put('/api/me/password', Object.fromEntries(new FormData(e.target))); this.saving=false; if(!r.ok){this.fieldErrors=r.errors||{};window.asicApi.showToast(r.error||'Kata sandi gagal diperbarui.');return;} e.target.reset();window.asicApi.showToast(r.data?.message||'Kata sandi diperbarui.','success'); } }">
    <header><h1 class="text-2xl font-bold text-on-surface">Profil peserta</h1></header>
    <form class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5" @submit.prevent="saveProfile($event)">
        @csrf
        @foreach(['prefix'=>'Gelar', 'first_name'=>'Nama depan', 'last_name'=>'Nama belakang', 'passport_no'=>'Nomor paspor', 'job_title'=>'Jabatan', 'phone'=>'Telepon', 'institution'=>'Institusi', 'address'=>'Alamat', 'city'=>'Kota', 'country'=>'Negara', 'postcode'=>'Kode pos'] as $field=>$label)
            <label class="block text-sm font-semibold">{{ $label }}<input name="{{ $field }}" value="{{ old($field, $participant?->{$field}) }}" class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
            <p x-show="fieldErrors['{{ $field }}']" x-text="fieldErrors['{{ $field }}']?.[0]" class="text-sm text-error"></p>
        @endforeach
        <button :disabled="saving" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50">Simpan profil</button>
    </form>
    <form class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5" @submit.prevent="savePassword($event)">
        @csrf
        <h2 class="font-bold">Ganti kata sandi</h2>
        <label class="block text-sm font-semibold">Kata sandi saat ini<input name="current_password" type="password" required class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
        <label class="block text-sm font-semibold">Kata sandi baru<input name="password" type="password" required class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
        <label class="block text-sm font-semibold">Konfirmasi kata sandi baru<input name="password_confirmation" type="password" required class="mt-1 block w-full rounded-xl border-surface-container-highest"></label>
        <p x-show="fieldErrors.current_password || fieldErrors.password" x-text="fieldErrors.current_password?.[0] || fieldErrors.password?.[0]" class="text-sm text-error"></p>
        <button :disabled="saving" class="rounded-xl border border-primary px-5 py-2.5 text-sm font-bold text-primary disabled:opacity-50">Perbarui kata sandi</button>
    </form>
</main>
@endsection
