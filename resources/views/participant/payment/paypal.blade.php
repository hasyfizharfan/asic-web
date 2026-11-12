@extends('layouts.portal', [
    'role' => 'participant',
    'roleName' => 'Oral Presenter',
    'nav' => [
        ['label' => __('asic-nav.dashboard'), 'route' => 'participant.dashboard', 'icon' => 'space_dashboard'],
        ['label' => __('asic-nav.my_abstract'), 'route' => 'participant.abstract', 'icon' => 'description'],
        ['label' => __('asic-nav.payment'), 'route' => 'participant.payment.checkout', 'icon' => 'payments'],
        ['label' => __('asic-nav.documents'), 'route' => 'participant.documents', 'icon' => 'folder'],
    ]
])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-layout.page-header title="PayPal Express Checkout" subtitle="Secure International Payment for ASIC 2027 Registration">
        <x-ui.button variant="outline" size="sm" href="{{ route('fe.participant.payment.checkout') }}" iconLeft="arrow_back">
            Back to Gateways
        </x-ui.button>
    </x-layout.page-header>

    <x-ui.card>
        <!-- PayPal Brand Banner -->
        <div class="flex items-center justify-between border-b border-surface-container-highest pb-4 mb-6">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-extrabold text-[#003087] font-display italic">Pay</span><span class="text-2xl font-extrabold text-[#0070BA] font-display italic -ml-1.5">Pal</span>
                <span class="text-xs text-on-surface-variant font-medium ml-2">Checkout</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-primary">lock</span>
                <span>256-bit SSL Encrypted</span>
            </div>
        </div>

        <!-- Order Items -->
        <div class="space-y-4">
            <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest space-y-3">
                <div class="flex justify-between items-start text-xs">
                    <div>
                        <p class="font-bold text-on-surface text-sm font-display">ASIC 2027 Oral Presentation Fee</p>
                        <p class="text-on-surface-variant mt-0.5">Author: Prof. Dr. Ahmad Fauzi (Universitas Andalas)</p>
                        <p class="text-on-surface-variant">Paper ID: ASIC-2027-ABS-0089</p>
                    </div>
                    <span class="font-mono font-bold text-base text-on-surface">$150.00 USD</span>
                </div>
                <div class="border-t border-surface-container-highest pt-2 flex justify-between text-xs text-on-surface-variant">
                    <span>Tax & Processing Fee</span>
                    <span class="font-mono">$0.00 USD</span>
                </div>
                <div class="border-t border-surface-container-highest pt-2 flex justify-between text-sm font-bold text-primary">
                    <span>Total Amount Charged</span>
                    <span class="font-mono text-lg">$150.00 USD</span>
                </div>
            </div>

            <div class="text-xs text-on-surface-variant leading-relaxed">
                By clicking "Pay with PayPal", you will be redirected to PayPal's secure gateway to complete your transaction using credit card, debit card, or your PayPal wallet balance.
            </div>

            <!-- PayPal Mock CTA -->
            <div class="space-y-3 pt-4">
                {{-- TODO(BE): integrasi PayPal SDK capture URL --}}
                <a href="{{ route('fe.participant.payment.status', ['state' => 'verified']) }}" 
                   class="flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-xl bg-[#FFC439] hover:bg-[#F4BB38] active:scale-[0.99] text-[#003087] font-bold text-sm shadow-md transition">
                    <span class="font-extrabold italic font-display text-base">Pay<span class="text-[#0070BA]">Pal</span></span>
                    <span>Pay with PayPal Account</span>
                </a>

                <a href="{{ route('fe.participant.payment.status', ['state' => 'verified']) }}" 
                   class="flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-xl bg-[#2C2E2F] hover:bg-black active:scale-[0.99] text-white font-bold text-sm shadow transition">
                    <span class="material-symbols-outlined text-[18px]">credit_card</span>
                    <span>Debit or Credit Card</span>
                </a>
            </div>
        </div>

        <x-slot:footer>
            <div class="w-full flex items-center justify-between text-xs text-on-surface-variant">
                <span>Merchant: ASIC 2027 Committee</span>
                <span>Currency: USD ($)</span>
            </div>
        </x-slot:footer>
    </x-ui.card>
</div>
@endsection

