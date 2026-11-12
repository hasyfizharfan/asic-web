@extends('layouts.portal', [
    'role' => 'reviewer',
    'title' => __('asic-reviewer.profile_title') ?? 'Profile - Reviewer ASIC 2027'
])

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs">
        <h1 class="text-2xl font-bold font-display text-on-surface">
            {{ __('asic-reviewer.profile_title') ?? 'Profil Reviewer' }}
        </h1>
        <p class="text-xs text-on-surface-variant mt-1">
            Informasi akun evaluator dan preferensi keamanan Anda untuk seminar ASIC 2027.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <!-- Reviewer Information Card -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-surface-container-highest">
                <div class="w-14 h-14 rounded-2xl bg-primary-fixed text-primary flex items-center justify-center font-bold text-xl font-display">
                    SJ
                </div>
                <div>
                    <h3 class="font-bold text-base text-on-surface font-display">Dr. Sarah Jenkins, Ph.D.</h3>
                    <p class="text-xs text-on-surface-variant font-medium">Department of Agronomy, Universitas Andalas</p>
                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-[11px] font-bold text-primary mt-1">
                        Academic Reviewer
                    </span>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-on-surface-variant font-medium">Email:</span>
                    <p class="font-semibold text-on-surface font-mono mt-0.5">reviewer@example.org</p>
                </div>
                <div>
                    <span class="text-on-surface-variant font-medium">Bidang Keahlian:</span>
                    <p class="font-semibold text-on-surface mt-0.5">Crop Science, Climate Resilience, Soil Microbiome</p>
                </div>
                <div>
                    <span class="text-on-surface-variant font-medium">Status Akun:</span>
                    <p class="font-semibold text-primary mt-0.5">Aktif (Reviewer Terverifikasi)</p>
                </div>
            </div>

            <div class="pt-4 border-t border-surface-container-highest">
                <a href="{{ route('fe.reviewer.logout') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-error/40 text-error hover:bg-error-container text-xs font-bold transition">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    <span>Keluar dari Akun</span>
                </a>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-4"
             x-data="{ currentPass: '', newPass: '', confirmPass: '', saved: false }">
            <h2 class="text-base font-bold text-on-surface font-display">
                {{ __('asic-reviewer.change_password') ?? 'Ganti Kata Sandi' }}
            </h2>

            <form @submit.prevent="
                if(newPass.length < 8) { $store.toast.add('Kata sandi minimal 8 karakter', 'warning'); return; }
                if(newPass !== confirmPass) { $store.toast.add('Konfirmasi kata sandi tidak cocok', 'error'); return; }
                saved = true;
                $store.toast.add('{{ __('asic-reviewer.profile_updated') ?? 'Kata sandi berhasil diperbarui' }}', 'success');
                currentPass = ''; newPass = ''; confirmPass = '';
            " class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-on-surface mb-1">{{ __('asic-reviewer.current_password') ?? 'Kata Sandi Saat Ini' }}</label>
                    <input type="password" x-model="currentPass" required placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block font-bold text-on-surface mb-1">{{ __('asic-reviewer.new_password') ?? 'Kata Sandi Baru' }}</label>
                    <input type="password" x-model="newPass" required placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block font-bold text-on-surface mb-1">{{ __('asic-reviewer.confirm_password') ?? 'Konfirmasi Kata Sandi Baru' }}</label>
                    <input type="password" x-model="confirmPass" required placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow transition active:scale-95">
                        <span class="material-symbols-outlined text-[16px]">lock_reset</span>
                        <span>{{ __('asic-reviewer.btn_update_password') ?? 'Perbarui Kata Sandi' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

