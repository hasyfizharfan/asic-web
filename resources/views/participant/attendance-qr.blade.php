@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-layout.page-header title="Attendance Check-In QR" subtitle="Official registration pass for venue entry and conference kit collection" :breadcrumbs="[
        ['label' => 'Portal', 'url' => route('fe.participant.dashboard')],
        ['label' => 'Attendance QR']
    ]">
        <x-ui.badge variant="success" dot>Pass Valid</x-ui.badge>
    </x-layout.page-header>

    <!-- Center Card with QR -->
    <x-ui.card>
        <div class="text-center space-y-4 py-4">
            <!-- QR Box Component -->
            <x-ui.qr-box code="ASIC-2027-PRS-0014" title="Prof. Dr. Ir. Ahmad Fauzi" subtitle="Universitas Andalas • Oral Presenter">
                <div class="flex items-center gap-2">
                    <x-ui.button size="sm" variant="outline" iconLeft="download" @click="$store.toast.add('QR Pass downloaded to device.', 'success')">
                        Save to Phone
                    </x-ui.button>
                    <x-ui.button size="sm" variant="primary" iconLeft="print" @click="$store.toast.add('Preparing printable pass...', 'info')">
                        Print Pass
                    </x-ui.button>
                </div>
            </x-ui.qr-box>

            <!-- Details Table -->
            <div class="max-w-md mx-auto p-4 rounded-xl bg-surface-container-low border border-surface-container-highest text-xs text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Registration Type:</span>
                    <span class="font-bold text-primary">Oral Presenter (On-site)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Allocated Room:</span>
                    <span class="font-bold text-on-surface">Technical Room B (Convention Hall Lt. 2)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Presentation Slot:</span>
                    <span class="font-bold text-on-surface">Day 1 • 14:00 – 14:15 WIB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Verification Status:</span>
                    <span class="font-mono text-tertiary font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Fee Reconciled (Eligible for Check-In)</span>
                    </span>
                </div>
            </div>

            <!-- Instructions -->
            <div class="max-w-md mx-auto text-left text-xs text-on-surface-variant space-y-2 p-3 bg-surface-container-lowest rounded-xl border border-surface-container-highest">
                <p class="font-bold text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-primary">info</span>
                    <span>Check-in Instructions for Reception Desk:</span>
                </p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Arrive at Convention Hall Universitas Andalas on 24 August 2027 starting at 07:30 WIB.</li>
                    <li>Present this barcode to the secretariat officer at Scanner Desk 1 or Desk 2.</li>
                    <li>Collect your official seminar lanyard, certificate folder, and souvenir conference kit.</li>
                </ol>
            </div>
        </div>
    </x-ui.card>
</div>
@endsection

