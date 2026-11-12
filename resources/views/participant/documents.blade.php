@php
    $participantType = session('participant_type', 'oral');
    $isListener = ($participantType === 'listener');
    $isId = (app()->getLocale() === 'id');
@endphp

@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="mx-auto w-full max-w-4xl py-2">
    <!-- Header matching prototype and screenshot -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold font-display text-on-surface">
            {{ $isId ? 'Dokumen' : 'Documents' }}
        </h1>
        <p class="text-sm text-on-surface-variant mt-1">
            {{ $isId ? 'Semua dokumen konferensi Anda di satu tempat.' : 'All your conference documents in one place.' }}
        </p>
    </div>

    <!-- Stacked Document Rows (Image 5 & Prototype p__c_documents.html) -->
    <div class="flex flex-col gap-3.5">
        @if(!$isListener)
            <!-- Row 1: Surat Penerimaan (LOA) -->
            <div class="flex items-center gap-4 rounded-2xl bg-surface-container-lowest p-4.5 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
                <div class="w-12 h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[28px]">description</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                        {{ $isId ? 'Surat Penerimaan (LOA)' : 'Letter of Acceptance (LOA)' }}
                    </div>
                    <div class="text-xs text-on-surface-variant mt-0.5">
                        {{ $isId ? 'Surat penerimaan abstrak, PDF' : 'Abstract acceptance letter, PDF' }}
                    </div>
                </div>
                <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                    {{ $isId ? 'Tersedia' : 'Available' }}
                </span>
                <button type="button" 
                        @click="$store.toast.add('{{ $isId ? 'Mengunduh Surat Penerimaan (LOA)...' : 'Downloading Letter of Acceptance (LOA)...' }}', 'info')" 
                        class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                    {{ $isId ? 'Unduh' : 'Download' }}
                </button>
            </div>
        @else
            <!-- Row 1 for Listener: Panduan Peserta -->
            <div class="flex items-center gap-4 rounded-2xl bg-surface-container-lowest p-4.5 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
                <div class="w-12 h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[28px]">menu_book</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                        {{ $isId ? 'Panduan Peserta' : 'Attendee Guide' }}
                    </div>
                    <div class="text-xs text-on-surface-variant mt-0.5">
                        {{ $isId ? 'Buku panduan konferensi dan rundown, PDF' : 'Conference attendee guide and rundown, PDF' }}
                    </div>
                </div>
                <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                    {{ $isId ? 'Tersedia' : 'Available' }}
                </span>
                <button type="button" 
                        @click="$store.toast.add('{{ $isId ? 'Mengunduh Panduan Peserta...' : 'Downloading Attendee Guide...' }}', 'info')" 
                        class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                    {{ $isId ? 'Unduh' : 'Download' }}
                </button>
            </div>
        @endif

        <!-- Row 2: Tagihan / Invoice -->
        <div class="flex items-center gap-4 rounded-2xl bg-surface-container-lowest p-4.5 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
            <div class="w-12 h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[28px]">receipt_long</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                    {{ $isId ? 'Tagihan' : 'Invoice' }}
                </div>
                <div class="text-xs text-on-surface-variant mt-0.5">
                    {{ $isId ? 'Tagihan registrasi, PDF' : 'Registration billing invoice, PDF' }}
                </div>
            </div>
            <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                {{ $isId ? 'Tersedia' : 'Available' }}
            </span>
            <button type="button" 
                    @click="$store.toast.add('{{ $isId ? 'Mengunduh Tagihan Registrasi...' : 'Downloading Billing Invoice...' }}', 'info')" 
                    class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                {{ $isId ? 'Unduh' : 'Download' }}
            </button>
        </div>

        <!-- Row 3: Bukti Pembayaran / Receipt -->
        <div class="flex items-center gap-4 rounded-2xl bg-surface-container-lowest p-4.5 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
            <div class="w-12 h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[28px]">verified</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                    {{ $isId ? 'Bukti Pembayaran' : 'Payment Receipt' }}
                </div>
                <div class="text-xs text-on-surface-variant mt-0.5">
                    {{ $isId ? 'Bukti pembayaran resmi, PDF' : 'Official payment receipt, PDF' }}
                </div>
            </div>
            <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                {{ $isId ? 'Tersedia' : 'Available' }}
            </span>
            <button type="button" 
                    @click="$store.toast.add('{{ $isId ? 'Mengunduh Bukti Pembayaran...' : 'Downloading Payment Receipt...' }}', 'info')" 
                    class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                {{ $isId ? 'Unduh' : 'Download' }}
            </button>
        </div>

        @if(!$isListener)
            <!-- Row 4: Berkas Abstrak yang Dikirim -->
            <div class="flex items-center gap-4 rounded-2xl bg-surface-container-lowest p-4.5 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
                <div class="w-12 h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[28px]">upload_file</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                        {{ $isId ? 'Berkas Abstrak yang Dikirim' : 'Submitted Abstract File' }}
                    </div>
                    <div class="text-xs text-on-surface-variant mt-0.5">
                        {{ $isId ? 'Berkas abstrak yang Anda kirim' : 'Your submitted abstract manuscript' }}
                    </div>
                </div>
                <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                    {{ $isId ? 'Tersedia' : 'Available' }}
                </span>
                <button type="button" 
                        @click="$store.toast.add('{{ $isId ? 'Mengunduh Berkas Abstrak...' : 'Downloading Submitted Abstract...' }}', 'info')" 
                        class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                    {{ $isId ? 'Unduh' : 'Download' }}
                </button>
            </div>
        @endif

        <!-- Row 5: Sertifikat -->
        <div class="flex items-center gap-4 rounded-2xl bg-surface-container-lowest p-4.5 sm:p-5 shadow-xs border border-surface-container-highest hover:border-primary/40 transition">
            <div class="w-12 h-12 rounded-xl bg-primary-fixed/20 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[28px]">workspace_premium</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-sm sm:text-base text-on-surface font-display truncate">
                    {{ $isId ? 'Sertifikat' : 'Certificate' }}
                </div>
                <div class="text-xs text-on-surface-variant mt-0.5">
                    {{ $isId ? 'Terbuka setelah presentasi tercatat' : ($isListener ? 'Unlocked after attendance check-in' : 'Unlocked after presentation recorded') }}
                </div>
            </div>
            <span class="inline-flex items-center rounded-full bg-[#c8e6c9] px-3.5 py-1 text-xs font-semibold text-emerald-900 shrink-0">
                {{ $isId ? 'Terkunci' : 'Locked' }}
            </span>
            <button type="button" 
                    @click="$store.toast.add('{{ $isId ? 'Sertifikat masih terkunci sampai acara selesai.' : 'Certificate is locked until session verification.' }}', 'warning')" 
                    class="inline-flex items-center justify-center rounded-xl bg-primary hover:bg-primary-container px-5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs transition active:scale-95 shrink-0">
                {{ $isId ? 'Unduh' : 'Download' }}
            </button>
        </div>
    </div>
</div>
@endsection
