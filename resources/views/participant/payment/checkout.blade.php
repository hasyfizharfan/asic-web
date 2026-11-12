@php
    $participantType = session('participant_type', 'oral');
    $isListener = ($participantType === 'listener');

    $invoiceNo = $isListener ? '#INV-2027-0841-LIS' : '#INV-2027-0089-PRS';
    $totalAmountIdr = $isListener ? 'IDR 500.000' : 'IDR 1.500.000';
    $totalAmountUsd = $isListener ? 'USD 50.00' : 'USD 150.00';
    $itemTitle = $isListener ? 'Registration Fee (Conference Attendee / Listener)' : 'Registration Fee (Oral Presenter - Regular Academician)';
    $itemPrice = $isListener ? 'IDR 500.000' : 'IDR 1.750.000';
@endphp

@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ 
    paymentMethod: 'qris',
    proofUploaded: false,
    timer: '23:59:45'
}">
    
    <x-layout.page-header title="{{ __('asic-participant.checkout_title') }}" subtitle="Select your preferred payment gateway to settle conference registration invoice {{ $invoiceNo }}">
        <x-ui.badge variant="warning" dot>Pending Payment</x-ui.badge>
    </x-layout.page-header>

    <!-- Invoice Summary Banner -->
    <x-ui.card>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-surface-container-highest pb-4">
            <div>
                <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Invoice Reference</span>
                <p class="text-base font-bold font-mono text-primary">{{ $invoiceNo }}</p>
                <p class="text-xs text-on-surface-variant">{{ $isListener ? 'Due Date: 7 Days from Registration (23:59 WIB)' : 'Due Date: 30 June 2027 (23:59 WIB)' }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Total Amount Due</span>
                <p class="text-2xl font-bold font-mono text-primary">{{ $totalAmountIdr }}</p>
                <p class="text-[11px] text-on-surface-variant">Or {{ $totalAmountUsd }} for International Card</p>
            </div>
        </div>

        <div class="pt-4 text-xs space-y-2">
            <div class="flex justify-between text-on-surface-variant">
                <span>{{ $itemTitle }}</span>
                <span class="font-mono font-medium text-on-surface">{{ $itemPrice }}</span>
            </div>
            @if(!$isListener)
                <div class="flex justify-between text-tertiary">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">sell</span>
                        <span>Early Bird Discount (Valid until June 30)</span>
                    </span>
                    <span class="font-mono font-bold">- IDR 250.000</span>
                </div>
            @else
                <div class="flex justify-between text-on-surface-variant">
                    <span>Seminar Kit, Plenary & Parallel Access, Lunch</span>
                    <span class="font-mono font-medium text-secondary">Included</span>
                </div>
            @endif
            <div class="flex justify-between text-on-surface-variant">
                <span>Gateway & Administrative Fee</span>
                <span class="font-mono font-medium text-on-surface">IDR 0 (Waived)</span>
            </div>
        </div>
    </x-ui.card>

    <!-- Payment Gateway Tabs -->
    <x-ui.card title="Select Payment Method" icon="account_balance_wallet">
        <x-ui.tabs :tabs="[
            ['id' => 'qris', 'label' => 'QRIS Instant Transfer (IDR)', 'icon' => 'qr_code_2'],
            ['id' => 'paypal', 'label' => 'PayPal / Credit Card (USD)', 'icon' => 'credit_card'],
            ['id' => 'bank', 'label' => 'Direct Bank Transfer', 'icon' => 'account_balance']
        ]">
            
            <!-- Tab 1: QRIS -->
            <div x-show="activeTab === 'qris'" class="space-y-6">
                <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest text-xs text-on-surface-variant">
                    <p class="font-bold text-on-surface mb-1">How to pay with QRIS:</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Open any Indonesian mobile banking app (BCA, Mandiri, BNI, BRI, Bank Nagari) or e-Wallet (GoPay, OVO, Dana).</li>
                        <li>Scan the QR code below and check that the merchant name appears as <b class="text-on-surface">ASIC 2027 UNAND</b>.</li>
                        <li>Enter the exact amount of <b class="text-primary font-mono">{{ $totalAmountIdr }}</b>.</li>
                        <li>Payment is verified automatically within 10–15 minutes.</li>
                    </ol>
                </div>

                <div class="text-center space-y-3">
                    <div class="p-4 bg-white rounded-2xl border border-surface-container-highest inline-block shadow-md">
                        <img src="{{ asset('images/asic/qr-sample.svg') }}" alt="QRIS ASIC 2027" class="w-48 h-48 mx-auto">
                        <div class="mt-2 flex items-center justify-center gap-2">
                            <span class="text-[11px] font-bold font-mono text-primary bg-primary-fixed/40 px-2 py-0.5 rounded">NMID: ID10293847561</span>
                        </div>
                    </div>

                    <p class="text-xs font-mono font-bold text-on-surface-variant">
                        Invoice expires in: <span class="text-error font-bold" x-text="timer"></span>
                    </p>

                    <div class="flex items-center justify-center gap-3 pt-2">
                        <x-ui.button size="sm" variant="outline" iconLeft="download">
                            Download QR Image
                        </x-ui.button>
                        <x-ui.button size="sm" variant="primary" href="{{ route('fe.participant.payment.status', ['state' => 'waiting_verification']) }}" iconLeft="check_circle">
                            I Have Completed Payment
                        </x-ui.button>
                    </div>
                </div>
            </div>

            <!-- Tab 2: PayPal -->
            <div x-show="activeTab === 'paypal'" class="space-y-6" style="display: none;">
                <div class="p-5 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-3">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span class="material-symbols-outlined text-[20px]">credit_card</span>
                        <span>PayPal International Express Checkout</span>
                    </div>
                    <p class="leading-relaxed">
                        For authors and participants outside Indonesia. Pay securely with international Visa, MasterCard, American Express, or your PayPal balance.
                    </p>
                    <div class="p-3 bg-white rounded-lg border border-blue-200 flex items-center justify-between font-mono text-xs">
                        <span>Invoice Amount in USD:</span>
                        <b class="text-primary text-base">{{ $totalAmountUsd }}</b>
                    </div>
                </div>

                <div class="text-center pt-2">
                    <a href="{{ route('fe.participant.payment.paypal') }}" class="inline-flex items-center gap-3 px-8 py-3.5 rounded-xl bg-[#0070BA] text-white font-bold text-sm shadow hover:bg-[#005ea6] transition">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart_checkout</span>
                        <span>Proceed to PayPal Invoice Details</span>
                    </a>
                </div>
            </div>

            <!-- Tab 3: Bank Transfer -->
            <div x-show="activeTab === 'bank'" class="space-y-6" style="display: none;">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest space-y-2">
                        <p class="font-bold text-primary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">account_balance</span>
                            <span>Bank BNI (Bank Negara Indonesia)</span>
                        </p>
                        <p class="text-on-surface-variant font-mono">Account No: <b class="text-on-surface text-sm">0123-456-789</b></p>
                        <p class="text-on-surface-variant">Holder: <b class="text-on-surface">Rektor Universitas Andalas QQ ASIC</b></p>
                        <p class="text-on-surface-variant">Branch: Limau Manih, Padang</p>
                        <p class="text-primary font-mono font-bold pt-1">Amount: {{ $totalAmountIdr }}</p>
                    </div>

                    <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest space-y-2">
                        <p class="font-bold text-primary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">account_balance</span>
                            <span>Bank Nagari (Bank Pembangunan Daerah)</span>
                        </p>
                        <p class="text-on-surface-variant font-mono">Account No: <b class="text-on-surface text-sm">2100-0103-9999</b></p>
                        <p class="text-on-surface-variant">Holder: <b class="text-on-surface">Panitia Seminar Internasional Pertanian</b></p>
                        <p class="text-on-surface-variant">Branch: Kampus Unand Padang</p>
                        <p class="text-primary font-mono font-bold pt-1">Amount: {{ $totalAmountIdr }}</p>
                    </div>
                </div>

                <!-- Form Upload Transfer Proof -->
                {{-- TODO(BE): action route('fe.participant.payment.upload-proof') --}}
                <form action="{{ route('fe.participant.payment.status', ['state' => 'waiting_verification']) }}" method="GET" class="space-y-4 pt-4 border-t border-surface-container-highest">
                    @csrf
                    <x-ui.input label="Sender Bank & Account Holder" placeholder="e.g. Bank Mandiri a.n. Ahmad Fauzi" required />
                    <x-ui.input label="Transaction Transfer Date" type="date" value="{{ date('Y-m-d') }}" required />
                    <x-ui.file-upload label="{{ __('asic-participant.upload_proof') }}" name="proof_file" accept=".jpg,.png,.pdf" maxSize="5 MB" hint="{{ __('asic-participant.proof_hint') }}" required />

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <x-ui.button type="submit" variant="primary" iconLeft="upload">
                            Submit Payment Proof
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.tabs>
    </x-ui.card>
</div>
@endsection
