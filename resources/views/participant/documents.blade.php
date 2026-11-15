@php
    $participantType = request()->query('type', 'oral');
    $isListener = ($participantType === 'listener');
    $isId = (app()->getLocale() === 'id');
@endphp

@extends('layouts.portal', [
    'role' => 'participant',
    'title' => ($isId ? 'Dokumen' : 'Documents') . ' - ASIC 2027'
])

@section('content')
<div class="mx-auto w-full max-w-4xl py-2 space-y-6">
    <!-- Header matching prototype and screenshot -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold font-display text-on-surface">
            {{ $isId ? 'Dokumen' : 'Documents' }}
        </h1>
        <p class="text-xs sm:text-sm text-on-surface-variant mt-1">
            {{ $isId ? 'Semua dokumen resmi konferensi Anda di satu tempat.' : 'All your official conference documents in one place.' }}
        </p>
    </div>

    <!-- Stacked Document Rows (Image 5 & Prototype p__c_documents.html) -->
    <div class="flex flex-col gap-3.5">
        @if(!$isListener)
            <!-- Row 1: Surat Penerimaan (LOA) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 rounded-2xl bg-surface-container-lowest p-4 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
                <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px] sm:text-[28px]">description</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                            {{ $isId ? 'Surat Penerimaan (LOA)' : 'Letter of Acceptance (LOA)' }}
                        </div>
                        <div class="text-xs text-on-surface-variant mt-0.5 truncate">
                            {{ $isId ? 'Surat penerimaan abstrak resmi, PDF' : 'Official abstract acceptance letter, PDF' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-surface-container-highest/60">
                    <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                        {{ $isId ? 'Tersedia' : 'Available' }}
                    </span>
                    <button type="button" 
                            @click="$store.toast.add('{{ $isId ? 'Mengunduh Surat Penerimaan (LOA)...' : 'Downloading Letter of Acceptance (LOA)...' }}', 'info')" 
                            class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-4 sm:px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                        {{ $isId ? 'Unduh' : 'Download' }}
                    </button>
                </div>
            </div>
        @else
            <!-- Row 1 for Listener: Panduan Peserta -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 rounded-2xl bg-surface-container-lowest p-4 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
                <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px] sm:text-[28px]">menu_book</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                            {{ $isId ? 'Panduan Peserta' : 'Attendee Guide' }}
                        </div>
                        <div class="text-xs text-on-surface-variant mt-0.5 truncate">
                            {{ $isId ? 'Buku panduan konferensi dan rundown, PDF' : 'Conference attendee guide and rundown, PDF' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-surface-container-highest/60">
                    <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                        {{ $isId ? 'Tersedia' : 'Available' }}
                    </span>
                    <button type="button" 
                            @click="$store.toast.add('{{ $isId ? 'Mengunduh Panduan Peserta...' : 'Downloading Attendee Guide...' }}', 'info')" 
                            class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-4 sm:px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                        {{ $isId ? 'Unduh' : 'Download' }}
                    </button>
                </div>
            </div>
        @endif

        <!-- Row 2: Tagihan / Invoice -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 rounded-2xl bg-surface-container-lowest p-4 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px] sm:text-[28px]">receipt_long</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                        {{ $isId ? 'Tagihan' : 'Invoice' }}
                    </div>
                    <div class="text-xs text-on-surface-variant mt-0.5 truncate">
                        {{ $isId ? 'Tagihan registrasi resmi, PDF' : 'Official registration billing invoice, PDF' }}
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-surface-container-highest/60">
                <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                    {{ $isId ? 'Tersedia' : 'Available' }}
                </span>
                <button type="button" 
                        @click="$store.toast.add('{{ $isId ? 'Mengunduh Tagihan Registrasi...' : 'Downloading Billing Invoice...' }}', 'info')" 
                        class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-4 sm:px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                    {{ $isId ? 'Unduh' : 'Download' }}
                </button>
            </div>
        </div>

        <!-- Row 3: Bukti Pembayaran / Receipt -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 rounded-2xl bg-surface-container-lowest p-4 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px] sm:text-[28px]">verified</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                        {{ $isId ? 'Bukti Pembayaran' : 'Payment Receipt' }}
                    </div>
                    <div class="text-xs text-on-surface-variant mt-0.5 truncate">
                        {{ $isId ? 'Bukti pembayaran lunas resmi, PDF' : 'Official payment receipt, PDF' }}
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-surface-container-highest/60">
                <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                    {{ $isId ? 'Tersedia' : 'Available' }}
                </span>
                <button type="button" 
                        @click="$store.toast.add('{{ $isId ? 'Mengunduh Bukti Pembayaran...' : 'Downloading Payment Receipt...' }}', 'info')" 
                        class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-4 sm:px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                    {{ $isId ? 'Unduh' : 'Download' }}
                </button>
            </div>
        </div>

        @if(!$isListener)
            <!-- Row 4: Berkas Abstrak yang Dikirim -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 rounded-2xl bg-surface-container-lowest p-4 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
                <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px] sm:text-[28px]">upload_file</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                            {{ $isId ? 'Berkas Abstrak yang Dikirim' : 'Submitted Abstract File' }}
                        </div>
                        <div class="text-xs text-on-surface-variant mt-0.5 truncate">
                            {{ $isId ? 'Naskah abstrak yang telah diunggah' : 'Your uploaded abstract manuscript' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-surface-container-highest/60">
                    <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                        {{ $isId ? 'Tersedia' : 'Available' }}
                    </span>
                    <button type="button" 
                            @click="$store.toast.add('{{ $isId ? 'Mengunduh Berkas Abstrak...' : 'Downloading Submitted Abstract...' }}', 'info')" 
                            class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-4 sm:px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                        {{ $isId ? 'Unduh' : 'Download' }}
                    </button>
                </div>
            </div>
        @endif

        <!-- Row 5: Sertifikat -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 rounded-2xl bg-surface-container-lowest p-4 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px] sm:text-[28px]">workspace_premium</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                        {{ $isId ? 'Sertifikat' : 'Certificate' }}
                    </div>
                    <div class="text-xs text-on-surface-variant mt-0.5 truncate">
                        {{ $isId ? 'Terbuka setelah kehadiran dan presentasi diverifikasi' : ($isListener ? 'Unlocked after attendance check-in' : 'Unlocked after presentation recorded') }}
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-surface-container-highest/60">
                <span class="inline-flex items-center rounded-full bg-surface-container-high px-3.5 py-1 text-xs font-semibold text-on-surface-variant shrink-0">
                    <span class="material-symbols-outlined text-[14px] mr-1">lock</span>
                    {{ $isId ? 'Terkunci' : 'Locked' }}
                </span>
                <button type="button" 
                        @click="$store.toast.add('{{ $isId ? 'Sertifikat masih terkunci sampai sesi konferensi diverifikasi.' : 'Certificate is locked until session verification.' }}', 'warning')" 
                        class="inline-flex items-center justify-center rounded-xl bg-surface-container-high text-on-surface-variant cursor-not-allowed px-4 sm:px-5 py-2 text-xs sm:text-sm font-bold shadow-xs transition shrink-0">
                    {{ $isId ? 'Unduh' : 'Download' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
