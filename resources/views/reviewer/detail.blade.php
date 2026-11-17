@extends('layouts.portal', [
    'role' => 'reviewer',
    'title' => __('asic-reviewer.form_title') ?? 'Review Abstract - ASIC 2027'
])

@php
    $abstractId = (int) ($id ?? 0);
@endphp

@section('content')
<div class="space-y-6" x-data="{
    recommendation: '', comments: '', isSubmitted: false,
    abstractData: null, loading: true, loadError: '',
    showConfirmModal: false,
    errorMessage: '', fieldErrors: {}, warnings: [],
    submitting: false,

    async init() { await this.loadDetail(); },

    async loadDetail() {
        this.loading = true;
        const response = await window.asicApi.get('/api/tim-abstrak/submissions/{{ $abstractId }}');
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Detail abstrak gagal dimuat.'; return; }
        this.abstractData = response.data?.data;
        this.isSubmitted = this.abstractData?.status_abstrak !== 'Diajukan';
        const latest = this.abstractData?.reviews?.at(-1);
        this.recommendation = latest?.hasil_review === 'Ditolak' ? 'reject' : (latest ? 'accept' : '');
        this.comments = latest?.catatan_review || '';
    },

    attemptSubmit() {
        this.errorMessage = '';
        this.fieldErrors = {};
        if (!this.recommendation) {
            this.errorMessage = '{{ app()->getLocale() === 'id' ? 'Silakan pilih rekomendasi evaluasi.' : 'Please select an evaluation recommendation.' }}';
            return;
        }
        if (this.recommendation === 'reject' && (!this.comments || this.comments.trim().length < 15)) {
            this.errorMessage = '{{ app()->getLocale() === 'id' ? 'Catatan reviewer wajib diisi minimal 15 karakter jika menolak.' : 'Comments of at least 15 characters are required when rejecting.' }}';
            return;
        }
        this.showConfirmModal = true;
    },

    async confirmSubmit() {
        this.submitting = true;
        this.showConfirmModal = false;
        
        const payload = {
            hasil_review: this.recommendation === 'reject' ? 'Ditolak' : 'Diterima',
            catatan_review: this.comments
        };

        const res = await window.asicApi.post('/api/tim-abstrak/submissions/{{ $abstractId }}/review', payload);
        this.submitting = false;

        if (res.ok) {
            this.warnings = res.data?.data?.warnings || [];
            $store.toast.add(res.data?.message || 'Keputusan review tersimpan.', 'success');
            await this.loadDetail();
        } else {
            this.fieldErrors = res.errors || {};
            this.errorMessage = res.status === 409 ? 'Abstrak sudah diputuskan. Status terbaru dimuat ulang.' : (res.status === 422 ? (res.error || 'Data keputusan tidak valid; periksa catatan review.') : (res.error || 'Review gagal dikirim.'));
            if (res.status === 409 || res.status === 422) await this.loadDetail();
            $store.toast.add(this.errorMessage, 'error');
        }
    }
}">

    <!-- Top Navigation & Breadcrumb (Matching Image 1 / r2_detail) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('portal.reviewer.queue') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold transition shadow-xs">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            <span>{{ __('asic-reviewer.btn_back') ?? 'Kembali ke antrean abstrak' }}</span>
        </a>

        @if($blindReview ?? false)
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-fixed text-primary text-xs font-bold">
                <span class="material-symbols-outlined text-[18px]">visibility_off</span>
                <span>{{ __('asic-reviewer.blind_review_notice') ?? 'Double-Blind Review: Author identity and institution are concealed.' }}</span>
            </div>
        @endif
    </div>

    <!-- Main 2-Column Grid (Card Kiri & Card Kanan, Matching Image 1 / r2_detail) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Card (Abstract Content & Details - 7 cols) -->
        <div class="lg:col-span-7 bg-surface-container-lowest p-6 sm:p-8 rounded-2xl border border-surface-container-highest shadow-xs space-y-6">
            <!-- Header & Metadata -->
            <div class="space-y-2 border-b border-surface-container-highest pb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary">
                    <span class="font-mono">#{{ $abstractId }}</span>
                </div>

                <p x-show="loading" class="text-sm text-on-surface-variant">Memuat detail abstrak…</p>
                <p x-show="loadError" x-text="loadError" role="alert" class="text-sm text-error"></p>
                <h1 x-show="abstractData" x-text="abstractData?.judul_abstrak" class="text-2xl sm:text-3xl font-bold text-on-surface font-display leading-tight"></h1>

                <div class="flex flex-wrap items-center gap-3 text-xs text-on-surface-variant font-medium">
                    <span>{{ __('asic-reviewer.submission_id') ?? 'Pengiriman ID' }}: <strong class="text-on-surface font-mono">#{{ $abstractId }}</strong></span>
                    <span>•</span>
                    <span>{{ __('asic-reviewer.research_area') ?? 'Area' }}: <strong class="text-on-surface" x-text="abstractData?.scope"></strong></span>
                    <span>•</span>
                    <span>Format: <strong class="text-on-surface" x-text="abstractData?.metode_presentasi"></strong></span>
                    <span>•</span><span>Tanggal submit: <strong class="text-on-surface" x-text="asicDateTime(abstractData?.tanggal_submit)"></strong></span>
                    <span>•</span><span>Status: <strong class="text-on-surface" x-text="abstractData?.status_abstrak"></strong></span>
                </div>
            </div>

            <!-- Abstract Body Section -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-display">
                    {{ __('asic-reviewer.abstract_heading') ?? 'Abstrak' }}
                </h3>
                <p class="text-sm text-on-surface-variant leading-relaxed">Abstrak lengkap tersedia pada berkas yang diunggah peserta.</p>
            </div>

            <!-- Keywords Section -->
            <div class="space-y-3 pt-2">
                <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-display">
                    {{ __('asic-reviewer.keywords_heading') ?? 'Kata Kunci' }}
                </h3>
                <p class="text-sm text-on-surface-variant">Kata kunci belum tersedia pada kontrak abstrak.</p>
            </div>

            <!-- Download Manuscript Button -->
            <div class="pt-4 border-t border-surface-container-highest flex flex-wrap gap-2">
                @if (! empty($abstractIsPdf))
                <button type="button" :disabled="!abstractData" @click="window.asicPreview('/api/tim-abstrak/submissions/{{ $abstractId }}/file', @js(__('asic-common.preview')))" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold transition active:scale-95 disabled:opacity-50">
                    <span class="material-symbols-outlined text-[18px]">visibility</span><span>{{ __('asic-common.preview') }}</span>
                </button>
                @endif
                <a :href="abstractData ? '/api/tim-abstrak/submissions/{{ $abstractId }}/file' : '#'"
                   :aria-disabled="!abstractData"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-primary text-primary hover:bg-primary hover:text-white text-xs font-bold transition shadow-xs active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>{{ __('asic-reviewer.btn_download_file') ?? 'Unduh Berkas Abstrak (PDF)' }}</span>
                </a>
            </div>
        </div>

        <div class="lg:col-span-7 bg-surface-container-lowest p-6 rounded-2xl border border-surface-container-highest shadow-xs space-y-3">
            <h2 class="text-sm font-bold text-on-surface uppercase tracking-wider">Riwayat Review</h2>
            <template x-for="review in (abstractData?.reviews || [])" :key="review.id_review">
                <div class="rounded-xl bg-surface-container-low p-4 space-y-1"><div class="flex justify-between gap-3 text-xs"><strong x-text="review.hasil_review"></strong><time class="text-on-surface-variant" x-text="asicDateTime(review.tanggal_review)"></time></div><p class="text-sm text-on-surface-variant whitespace-pre-line" x-text="review.catatan_review || 'Tanpa catatan'"></p></div>
            </template>
            <p x-show="abstractData && !abstractData.reviews?.length" class="text-sm text-on-surface-variant">Belum ada riwayat review.</p>
            <div x-show="warnings.length" class="rounded-xl bg-[#fff3c4] text-[#745b00] p-4"><p class="font-bold text-xs">Peringatan tindak lanjut</p><template x-for="(warning, index) in warnings" :key="index"><p class="text-xs mt-1" x-text="warning"></p></template></div>
        </div>

        <!-- Right Card (Review Form - 5 cols) -->
        <div class="lg:col-span-5 bg-surface-container-lowest p-6 sm:p-8 rounded-2xl border border-surface-container-highest shadow-xs space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-on-surface font-display">
                        {{ __('asic-reviewer.form_title') ?? 'Review Form' }}
                    </h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">
                        {{ __('asic-reviewer.form_lead') ?? 'Berikan keputusan final atas abstrak ini.' }}
                    </p>
                </div>

                <template x-if="isSubmitted">
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-xs font-bold flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-[14px]">lock</span>
                        <span>{{ __('asic-reviewer.readonly_badge') ?? 'Submitted' }}</span>
                    </span>
                </template>
            </div>

            <!-- Error Banner -->
            <div x-show="errorMessage" class="p-3.5 rounded-xl bg-error-container text-on-error-container text-xs flex items-center gap-2" style="display: none;">
                <span class="material-symbols-outlined text-[18px] text-error">error</span>
                <span x-text="errorMessage"></span>
            </div>

            <form @submit.prevent="attemptSubmit" class="space-y-4">
                <!-- Recommendation Dropdown -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider">
                        {{ __('asic-reviewer.recommendation_label') ?? 'Recommendation' }} <span class="text-error">*</span>
                    </label>
                    <select x-model="recommendation" 
                            :disabled="isSubmitted"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none disabled:bg-surface-container-high disabled:cursor-not-allowed">
                        <option value="">{{ __('asic-reviewer.rec_placeholder') ?? 'Select recommendation' }}</option>
                        <option value="accept">{{ __('asic-reviewer.rec_accept') ?? 'Accept' }}</option>
                        <option value="reject">{{ __('asic-reviewer.rec_reject') ?? 'Reject' }}</option>
                    </select>
                    <p x-show="fieldErrors.hasil_review" x-text="fieldErrors.hasil_review?.[0]" class="text-[11px] text-error"></p>
                </div>

                <!-- Review Comments Textarea -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider">
                        {{ __('asic-reviewer.comments_label') ?? 'Review Comments' }}
                    </label>
                    <textarea x-model="comments" 
                              :disabled="isSubmitted"
                              rows="6" 
                              placeholder="{{ __('asic-reviewer.comments_placeholder') ?? 'Provide scholarly feedback' }}" 
                              class="w-full p-3.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-xs text-on-surface leading-relaxed focus:ring-2 focus:ring-primary outline-none disabled:bg-surface-container-high disabled:cursor-not-allowed"></textarea>
                    <p x-show="fieldErrors.catatan_review" x-text="fieldErrors.catatan_review?.[0]" class="text-[11px] text-error"></p>
                    <p class="text-[11px] text-on-surface-variant">
                        {{ __('asic-reviewer.comments_required_help') ?? 'Catatan minimal 15 karakter wajib untuk keputusan Ditolak.' }}
                    </p>
                </div>

                <!-- Form Action Buttons -->
                <div class="pt-2 flex flex-col sm:flex-row items-center gap-3" x-show="!isSubmitted">

                    <button type="submit" 
                            class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#F5C518] hover:bg-[#fed649] text-[#17261D] text-xs font-bold shadow-md transition active:scale-95">
                        <span>{{ __('asic-reviewer.btn_submit_review') ?? 'Simpan Review' }}</span>
                        <span class="material-symbols-outlined text-[16px]">send</span>
                    </button>
                </div>

                <!-- Read-Only Notice -->
                <div x-show="isSubmitted" class="p-3.5 rounded-xl bg-surface-container-high text-xs text-on-surface-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">verified</span>
                    <span>Review ini telah diserahkan dan terkunci. Keputusan diteruskan ke tim sekretariat ASIC 2027.</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal Dialog -->
    <div x-show="showConfirmModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
         style="display: none;">
        <div @click.outside="showConfirmModal = false" class="bg-surface-container-lowest max-w-md w-full rounded-2xl p-6 border border-surface-container-highest shadow-2xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">rate_review</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-on-surface font-display">
                        {{ __('asic-reviewer.confirm_title') ?? 'Konfirmasi Pengiriman Review' }}
                    </h3>
                    <p class="text-xs text-on-surface-variant">Konferensi Internasional ASIC 2027</p>
                </div>
            </div>

            <p class="text-xs text-on-surface-variant leading-relaxed">
                {{ __('asic-reviewer.confirm_desc') ?? 'Keputusan ini final dan tidak dapat diubah. Setelah dikirim, sistem memproses dokumen keputusan dan invoice (jika diterima), serta mengirim email terkait.' }}
            </p>

            <div class="p-3 rounded-xl bg-surface-container-low border border-surface-container-highest text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Rekomendasi:</span>
                    <strong class="text-primary font-bold uppercase" x-text="recommendation"></strong>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        @click="showConfirmModal = false" 
                        class="px-4 py-2 rounded-xl border border-surface-container-highest bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-semibold transition">
                    {{ __('asic-reviewer.btn_cancel') ?? 'Batal' }}
                </button>
                <button type="button" 
                        @click="confirmSubmit" 
                        :disabled="submitting"
                        class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow transition active:scale-95 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] animate-spin" x-show="submitting" style="display: none;">progress_activity</span>
                    <span x-text="submitting ? '{{ app()->getLocale() === 'id' ? 'Mengirim...' : 'Submitting...' }}' : '{{ __('asic-reviewer.btn_confirm_submit') ?? 'Konfirmasi & Kirim' }}'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

