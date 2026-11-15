@extends('layouts.portal', [
    'role' => 'participant',
    'title' => (app()->getLocale() === 'id' ? 'Kebijakan Pengembalian Dana' : 'Refund Policy & Settlement') . ' - ASIC 2027'
])

@php
    $isId = (app()->getLocale() === 'id');
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-layout.page-header 
        title="{{ $isId ? 'Kebijakan Pengembalian Dana' : 'Refund Policy & Manual Settlement' }}" 
        subtitle="{{ $isId ? 'Ketentuan pemrosesan manual di luar sistem sesuai Analisis Kebutuhan Bagian D' : 'Offline manual settlement terms according to Requirements Analysis Section D' }}" 
        :breadcrumbs="[
            ['label' => 'Portal', 'url' => route('portal.participant.dashboard')],
            ['label' => $isId ? 'Informasi Pembayaran' : 'Payment Information', 'url' => route('portal.participant.documents')],
            ['label' => $isId ? 'Kebijakan Refund' : 'Refund Policy']
        ]">
        <x-ui.badge variant="secondary">
            <span class="material-symbols-outlined text-[14px] mr-1">policy</span>
            <span>{{ $isId ? 'Proses Manual di Luar Sistem' : 'Offline Manual Process' }}</span>
        </x-ui.badge>
    </x-layout.page-header>

    <!-- Directive Notice Banner (Analisis Kebutuhan Bagian D) -->
    <div class="p-5 sm:p-6 rounded-2xl bg-surface-container-lowest border border-amber-300 shadow-xs flex flex-col sm:flex-row items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-900 flex items-center justify-center shrink-0 shadow-sm">
            <span class="material-symbols-outlined text-[28px]">gavel</span>
        </div>
        <div class="space-y-2 flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-bold text-base text-on-surface font-display">
                    {{ $isId ? 'Ketentuan Resmi Pengembalian Dana (ASIC 2027)' : 'Official Refund Directive (ASIC 2027)' }}
                </span>
                <span class="font-mono text-[10px] text-amber-900 bg-amber-100 px-2.5 py-0.5 rounded-full font-bold">
                    {{ $isId ? 'Analisis Kebutuhan Bagian D' : 'Requirements Section D' }}
                </span>
            </div>
            <p class="text-xs sm:text-sm text-on-surface leading-relaxed">
                {!! $isId 
                    ? 'Sesuai dengan <strong>Analisis Kebutuhan Sistem Bagian D</strong>, seluruh proses pengajuan dan pengembalian dana (refund) <strong>dilakukan secara manual di luar sistem aplikasi web</strong> dan dikoordinasikan langsung bersama Bendahara Konferensi. Sistem web tidak memproses transaksi transfer dana keluar secara otomatis.' 
                    : 'In accordance with <strong>Requirements Analysis Section D</strong>, all refund claims and disbursements are <strong>handled manually outside this web portal</strong> through direct coordination with the Conference Treasury. The system does not execute automated outbound fund disbursements.' 
                !!}
            </p>
        </div>
    </div>

    <!-- Main Grid: Terms & Procedures -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Detailed Guidelines (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Eligibility Card -->
            <x-ui.card title="{{ $isId ? 'Kriteria Kelayakan Pengembalian Dana' : 'Eligibility & Deductions' }}" icon="account_balance_wallet">
                <div class="space-y-4 text-xs sm:text-sm text-on-surface leading-relaxed">
                    <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest space-y-2.5">
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">cancel</span>
                            <p>
                                <strong>{{ $isId ? 'Biaya Registrasi / Kehadiran Seminar:' : 'Registration & Attendance Fees:' }}</strong>
                                <span class="text-on-surface-variant block mt-0.5">
                                    {{ $isId ? 'Bersifat non-refundable (tidak dapat dikembalikan) atas alasan pembatalan kehadiran pribadi peserta.' : 'Strictly non-refundable for any personal cancellation of attendance.' }}
                                </span>
                            </p>
                        </div>
                        <div class="flex items-start gap-2.5 border-t border-surface-container-highest pt-2.5">
                            <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">check_circle</span>
                            <p>
                                <strong>{{ $isId ? 'Biaya Publikasi Prosiding (Publication Fee):' : 'Proceedings Publication Fee:' }}</strong>
                                <span class="text-on-surface-variant block mt-0.5">
                                    {{ $isId ? 'Hanya naskah yang ditarik (withdrawn) sebelum proses layout/typesetting percetakan atau tidak lolos seleksi prosiding terindeks yang dapat dievaluasi untuk pengembalian dana manual.' : 'Only manuscripts withdrawn prior to editorial typesetting or unselected proceeding submissions qualify for evaluation.' }}
                                </span>
                            </p>
                        </div>
                        <div class="flex items-start gap-2.5 border-t border-surface-container-highest pt-2.5">
                            <span class="material-symbols-outlined text-amber-700 text-[20px] shrink-0 mt-0.5">percent</span>
                            <p>
                                <strong>{{ $isId ? 'Potongan Biaya Administrasi Perbankan:' : 'Administrative & Banking Handling:' }}</strong>
                                <span class="text-on-surface-variant block mt-0.5">
                                    {{ $isId ? 'Pengembalian dana dikenakan potongan biaya penanganan administrasi dan kliring bank sebesar 20% dari nilai biaya publikasi.' : 'Disbursements are subject to a standard 20% administrative handling and clearing fee.' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Step by Step Offline Procedure -->
            <x-ui.card title="{{ $isId ? 'Tata Cara Pengajuan Manual ke Bendahara' : 'Manual Submission Procedure' }}" icon="checklist">
                <ol class="space-y-3.5 text-xs sm:text-sm text-on-surface-variant">
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-primary-fixed text-primary font-bold text-xs flex items-center justify-center shrink-0">1</span>
                        <div>
                            <strong class="text-on-surface">{{ $isId ? 'Kirimkan Surat Permohonan Tertulis' : 'Send Written Request Letter' }}</strong>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                {{ $isId ? 'Buat surat resmi bertanda tangan pemakalah utama yang memuat Nomor Registrasi ASIC, Judul Naskah, dan Alasan Penarikan.' : 'Draft an official signed letter containing your ASIC Registration ID, Abstract Title, and Withdrawal Rationale.' }}
                            </p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-primary-fixed text-primary font-bold text-xs flex items-center justify-center shrink-0">2</span>
                        <div>
                            <strong class="text-on-surface">{{ $isId ? 'Lampirkan Dokumen Pendukung' : 'Attach Supporting Documents' }}</strong>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                {{ $isId ? 'Lampirkan Bukti Transfer Pembayaran resmi, KTP/Paspor pemohon, dan rincian rekening bank atas nama penulis terdaftar.' : 'Attach official payment receipt, author ID card / passport, and bank account book matching the registered author.' }}
                            </p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-primary-fixed text-primary font-bold text-xs flex items-center justify-center shrink-0">3</span>
                        <div>
                            <strong class="text-on-surface">{{ $isId ? 'Audit dan Rekonsiliasi Offline' : 'Offline Audit & Disbursement' }}</strong>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                {{ $isId ? 'Bendahara dan tim keuangan Fakultas Pertanian UNAND akan memverifikasi permohonan dalam waktu 14 hari kerja.' : 'Treasury will verify your file and process the settlement transfer within 14 working days.' }}
                            </p>
                        </div>
                    </li>
                </ol>

                <x-slot:footer>
                    <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <x-ui.button variant="outline" href="{{ route('portal.participant.dashboard') }}" iconLeft="arrow_back">
                            {{ $isId ? 'Kembali ke Dashboard' : 'Back to Dashboard' }}
                        </x-ui.button>
                        <x-ui.button variant="primary" href="mailto:asic@agr.unand.ac.id?subject=Pengajuan%20Refund%20Manual%20ASIC%202027" iconLeft="mail">
                            {{ $isId ? 'Hubungi Email Bendahara' : 'Email Treasury Desk' }}
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </div>

        <!-- Right: Helpdesk & Contacts -->
        <div class="space-y-6">
            <x-ui.card title="{{ $isId ? 'Kontak Layanan Keuangan' : 'Treasury Contacts' }}" icon="support_agent">
                <div class="space-y-4 text-xs text-on-surface-variant">
                    <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container-highest space-y-2">
                        <div class="font-bold text-on-surface text-sm flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">account_balance</span>
                            <span>{{ $isId ? 'Unit Keuangan ASIC 2027' : 'ASIC Finance Unit' }}</span>
                        </div>
                        <p class="leading-relaxed">
                            {{ $isId ? 'Fakultas Pertanian, Universitas Andalas' : 'Faculty of Agriculture, Universitas Andalas' }}<br>
                            Kampus Limau Manih, Padang 25163
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">mail</span>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-on-surface-variant">Email Resmi</span>
                                <a href="mailto:{{ $asic['email'] ?? 'asic@agr.unand.ac.id' }}" class="text-primary font-bold hover:underline font-mono">
                                    {{ $asic['email'] ?? 'asic@agr.unand.ac.id' }}
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-primary text-[18px]">schedule</span>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-on-surface-variant">{{ $isId ? 'Jam Operasional' : 'Service Hours' }}</span>
                                <span class="text-on-surface font-semibold">Senin – Jumat, 08:30 – 16:00 WIB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
