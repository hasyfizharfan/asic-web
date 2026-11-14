@extends('layouts.portal', [
    'role' => 'reviewer',
    'title' => __('asic-reviewer.form_title') ?? 'Review Abstract - ASIC 2027'
])

@php
    $abstractId = $id ?? 'ASIC27-001';
    $isSubmitted = request()->has('submitted') || request()->query('view') === 'readonly';
@endphp

@section('content')
<div class="space-y-6" x-data="{
    recommendation: '{{ $isSubmitted ? 'accept' : '' }}',
    comments: '{{ $isSubmitted ? 'The abstract presents a comprehensive methodology addressing climate stress on rice productivity. Highly recommended for oral presentation in the main conference track.' : '' }}',
    isSubmitted: {{ $isSubmitted ? 'true' : 'false' }},
    showConfirmModal: false,
    errorMessage: '',

    saveDraft() {
        $store.toast.add('Draft review berhasil disimpan.', 'info');
    },

    attemptSubmit() {
        this.errorMessage = '';
        if (!this.recommendation) {
            this.errorMessage = '{{ app()->getLocale() === 'id' ? 'Silakan pilih rekomendasi evaluasi.' : 'Please select an evaluation recommendation.' }}';
            return;
        }
        if ((this.recommendation === 'revision' || this.recommendation === 'reject') && (!this.comments || this.comments.trim().length < 15)) {
            this.errorMessage = '{{ app()->getLocale() === 'id' ? 'Catatan reviewer wajib diisi (minimal 15 karakter) jika meminta revisi atau menolak.' : 'Comments are required (min 15 characters) when requesting revision or rejecting.' }}';
            return;
        }
        this.showConfirmModal = true;
    },

    confirmSubmit() {
        this.showConfirmModal = false;
        this.isSubmitted = true;
        $store.toast.add('{{ app()->getLocale() === 'id' ? 'Review berhasil dikirim ke sekretariat!' : 'Review submitted successfully to secretariat!' }}', 'success');
    }
}">

    <!-- Top Navigation & Breadcrumb (Matching Image 1 / r2_detail) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('fe.reviewer.queue') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold transition shadow-xs">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            <span>{{ __('asic-reviewer.btn_back') ?? '← Kembali to Assigned Abstracts' }}</span>
        </a>

        @if($blindReview ?? true)
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

                <h1 class="text-2xl sm:text-3xl font-bold text-on-surface font-display leading-tight">
                    Sustainable Rice Systems Under Climate Stress
                </h1>

                <div class="flex flex-wrap items-center gap-3 text-xs text-on-surface-variant font-medium">
                    <span>{{ __('asic-reviewer.submission_id') ?? 'Pengiriman ID' }}: <strong class="text-on-surface font-mono">#{{ $abstractId }}</strong></span>
                    <span>•</span>
                    <span>{{ __('asic-reviewer.research_area') ?? 'Area' }}: <strong class="text-on-surface">Crop Production</strong></span>
                    <span>•</span>
                    <span>Format: <strong class="text-on-surface">Oral Presentation</strong></span>
                </div>
            </div>

            <!-- Abstract Body Section -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-display">
                    {{ __('asic-reviewer.abstract_heading') ?? 'Abstrak' }}
                </h3>
                <p class="text-sm text-on-surface-variant leading-relaxed">
                    Climate variability presents unprecedented challenges to tropical agricultural productivity, especially regarding lowland and upland rice ecosystems. This study investigates the physiological response, yield stability, and rhizosphere microbial resilience of selected indigenous and improved rice cultivars under induced temperature elevation and drought conditions. Preliminary field trials conducted across diverse agroecological zones in West Sumatra indicate significant adaptive variations across genotypes, with cultivar AR-27 displaying notable stomatal regulation and deeper root penetration under osmotic deficits. These empirical findings offer critical pathways for climate-resilient crop breeding strategies and sustainable agrifood transition across Southeast Asia.
                </p>
            </div>

            <!-- Keywords Section -->
            <div class="space-y-3 pt-2">
                <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider font-display">
                    {{ __('asic-reviewer.keywords_heading') ?? 'Kata Kunci' }}
                </h3>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-lg bg-surface-container-high text-xs font-semibold text-on-surface">Sustainable agriculture</span>
                    <span class="px-3 py-1 rounded-lg bg-surface-container-high text-xs font-semibold text-on-surface">Rice</span>
                    <span class="px-3 py-1 rounded-lg bg-surface-container-high text-xs font-semibold text-on-surface">Climate resilience</span>
                    <span class="px-3 py-1 rounded-lg bg-surface-container-high text-xs font-semibold text-on-surface">Agrifood Systems</span>
                </div>
            </div>

            <!-- Download Manuscript Button -->
            <div class="pt-4 border-t border-surface-container-highest">
                <a href="{{ route('fe.template.author') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-primary text-primary hover:bg-primary hover:text-white text-xs font-bold transition shadow-xs active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>{{ __('asic-reviewer.btn_download_file') ?? 'Unduh Berkas Abstrak (PDF)' }}</span>
                </a>
            </div>
        </div>

        <!-- Right Card (Review Form - 5 cols) -->
        <div class="lg:col-span-5 bg-surface-container-lowest p-6 sm:p-8 rounded-2xl border border-surface-container-highest shadow-xs space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-on-surface font-display">
                        {{ __('asic-reviewer.form_title') ?? 'Review Form' }}
                    </h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">
                        {{ __('asic-reviewer.form_lead') ?? 'Demonstration of reviewer-specific interaction. Decision criteria and authority are subject to analyst approval.' }}
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
                        <option value="revision">{{ __('asic-reviewer.rec_revision') ?? 'Revision Required' }}</option>
                        <option value="reject">{{ __('asic-reviewer.rec_reject') ?? 'Reject' }}</option>
                    </select>
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
                    <p class="text-[11px] text-on-surface-variant">
                        {{ __('asic-reviewer.comments_required_help') ?? 'Detailed comments are required when requesting revision or rejecting.' }}
                    </p>
                </div>

                <!-- Form Action Buttons -->
                <div class="pt-2 flex flex-col sm:flex-row items-center gap-3" x-show="!isSubmitted">
                    <button type="button" 
                            @click="saveDraft"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-surface-container-highest bg-surface-container-high hover:bg-surface-container-highest text-on-surface text-xs font-bold transition shadow-xs active:scale-95">
                        {{ __('asic-reviewer.btn_save_draft') ?? 'Simpan Draft' }}
                    </button>

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
                {{ __('asic-reviewer.confirm_desc') ?? 'Apakah Anda yakin ingin mengirimkan review ini? Setelah dikirim, keputusan review akan dicatat dan diteruskan ke sekretariat.' }}
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
                        class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold shadow transition active:scale-95">
                    {{ __('asic-reviewer.btn_confirm_submit') ?? 'Konfirmasi & Kirim' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

