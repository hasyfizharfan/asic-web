@extends('layouts.portal', ['role' => 'reviewer', 'title' => __('asic-reviewer.profile_title') ?? 'Profil Reviewer'])

@section('content')
<div class="max-w-4xl space-y-6" x-data="{
    account: null, loading: true, currentPass: '', newPass: '', confirmPass: '', saving: false, errors: {},
    async init() { const r = await window.asicApi.get('/api/me'); this.loading = false; if (r.ok) this.account = r.data?.data; else window.asicApi.showToast(r.error || 'Profil gagal dimuat.'); },
    async savePassword(form) {
        this.saving = true; this.errors = {};
        const r = await window.asicApi.put('/api/me/password', Object.fromEntries(new FormData(form)));
        this.saving = false;
        if (!r.ok) { this.errors = r.errors || {}; window.asicApi.showToast(r.error || 'Kata sandi gagal diperbarui.'); return; }
        form.reset(); this.currentPass = ''; this.newPass = ''; this.confirmPass = '';
        window.asicApi.showToast(r.data?.message || 'Kata sandi diperbarui.', 'success');
    }
}">
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs"><h1 class="text-2xl font-bold font-display text-on-surface">{{ __('asic-reviewer.profile_title') ?? 'Profil Reviewer' }}</h1><p class="text-xs text-on-surface-variant mt-1">Informasi akun dan keamanan.</p></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-surface-container-highest"><div class="w-14 h-14 rounded-2xl bg-primary-fixed text-primary flex items-center justify-center font-bold text-xl" x-text="account?.email?.slice(0, 2)?.toUpperCase() || '…'"></div><div><h2 class="font-bold text-base text-on-surface" x-text="account?.email || (loading ? 'Memuat…' : '—')"></h2><p class="text-xs text-on-surface-variant" x-text="account?.role || 'Tim Abstrak'"></p></div></div>
            <div class="space-y-3 text-xs"><div><span class="text-on-surface-variant">Email</span><p class="font-semibold text-on-surface font-mono mt-0.5" x-text="account?.email || '—'"></p></div><div><span class="text-on-surface-variant">Bidang keahlian</span><p class="font-semibold text-on-surface mt-0.5">Belum tersedia</p></div><div><span class="text-on-surface-variant">Status akun</span><p class="font-semibold text-primary mt-0.5" x-text="account?.status_akun || '—'"></p></div></div>
            <div class="pt-4 border-t border-surface-container-highest"><a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-error/40 text-error text-xs font-bold"><span class="material-symbols-outlined text-[16px]">logout</span><span>Keluar dari Akun</span></a></div>
        </div>
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-4">
            <h2 class="text-base font-bold text-on-surface">{{ __('asic-reviewer.change_password') ?? 'Ganti Kata Sandi' }}</h2>
            <form @submit.prevent="savePassword($el)" class="space-y-3 text-xs">
                <div><label class="block font-bold text-on-surface mb-1">{{ __('asic-reviewer.current_password') ?? 'Kata Sandi Saat Ini' }}</label><input name="current_password" type="password" x-model="currentPass" required autocomplete="current-password" class="w-full px-3 py-2 rounded-xl border border-outline-variant text-on-surface"><p x-text="errors.current_password?.[0]" class="text-error mt-1"></p></div>
                <div><label class="block font-bold text-on-surface mb-1">{{ __('asic-reviewer.new_password') ?? 'Kata Sandi Baru' }}</label><input name="password" type="password" x-model="newPass" required autocomplete="new-password" class="w-full px-3 py-2 rounded-xl border border-outline-variant text-on-surface"><p x-text="errors.password?.[0]" class="text-error mt-1"></p></div>
                <div><label class="block font-bold text-on-surface mb-1">{{ __('asic-reviewer.confirm_password') ?? 'Konfirmasi Kata Sandi Baru' }}</label><input name="password_confirmation" type="password" x-model="confirmPass" required autocomplete="new-password" class="w-full px-3 py-2 rounded-xl border border-outline-variant text-on-surface"></div>
                <button type="submit" :disabled="saving" class="w-full inline-flex justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold disabled:opacity-50"><span class="material-symbols-outlined text-[16px]">lock_reset</span><span x-text="saving ? 'Menyimpan…' : '{{ __('asic-reviewer.btn_update_password') ?? 'Perbarui Kata Sandi' }}'"></span></button>
            </form>
        </div>
    </div>
</div>
@endsection
