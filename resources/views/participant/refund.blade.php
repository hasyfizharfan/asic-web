@extends('layouts.portal', [
    'role' => 'participant'
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    refundSubmitted: false,
    bankName: 'bni',
    accountNo: '',
    accountHolder: '',
    reason: ''
}">
    <x-layout.page-header title="{{ __('asic-participant.refund_title') }}" subtitle="Financial settlement unit • Faculty of Agriculture, Universitas Andalas" :breadcrumbs="[
        ['label' => 'Portal', 'url' => route('fe.participant.dashboard')],
        ['label' => 'Payment', 'url' => route('fe.participant.payment.checkout')],
        ['label' => 'Refund Request']
    ]">
        <x-ui.badge variant="warning" dot>Evaluation Pending</x-ui.badge>
    </x-layout.page-header>

    <!-- Strict Policy Notice Banner -->
    <div class="p-5 rounded-2xl bg-error-container/30 border border-error/30 text-xs flex items-start gap-4 shadow-xs">
        <div class="w-10 h-10 rounded-xl bg-error text-white flex items-center justify-center shrink-0 shadow-sm">
            <span class="material-symbols-outlined text-[24px]">gavel</span>
        </div>
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-error font-display">Academic Treasury Policy Notice</span>
                <span class="font-mono text-[10px] text-error bg-error-container px-2 py-0.5 rounded-full">ASIC/FIN/2027-IV</span>
            </div>
            <p class="text-on-surface leading-relaxed">
                <strong>Seminar attendance and registration fees are non-refundable</strong> under any personal circumstance. Only <strong>Publication Fees</strong> (for withdrawn manuscripts prior to editorial typesetting or unselected proceeding submissions) qualify for refund evaluation, subject to a 20% administrative handling fee.
            </p>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Form Submission -->
        <div class="lg:col-span-2 space-y-6">
            {{-- TODO(BE): action route('fe.participant.refund.store') --}}
            <form action="{{ route('fe.participant.dashboard') }}" method="GET" class="space-y-6" @submit.prevent="$store.toast.add('Refund request submitted to Treasury for audit.', 'success'); refundSubmitted = true">
                @csrf
                
                <!-- Financial Ledger Card -->
                <x-ui.card title="Eligible Financial Ledger" icon="account_balance_wallet">
                    <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container-highest text-xs space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Eligible Publication Fee:</span>
                            <span class="font-mono font-bold text-on-surface">IDR 1.250.000</span>
                        </div>
                        <div class="flex justify-between text-error">
                            <span>Administrative & Banking Handling (20%):</span>
                            <span class="font-mono font-bold">- IDR 250.000</span>
                        </div>
                        <div class="border-t border-surface-container-highest pt-2 flex justify-between text-sm font-bold text-primary">
                            <span>Estimated Net Disbursement:</span>
                            <span class="font-mono text-base">IDR 1.000.000</span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Bank Disbursement Form -->
                <x-ui.card title="Disbursement Destination Account" icon="payments">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-ui.select label="Beneficiary Bank / Gateway" name="bank_name" x-model="bankName" required iconLeft="account_balance">
                                <option value="bni">Bank BNI (Bank Negara Indonesia)</option>
                                <option value="bri">Bank BRI (Bank Rakyat Indonesia)</option>
                                <option value="mandiri">Bank Mandiri</option>
                                <option value="bca">Bank BCA</option>
                                <option value="nagari">Bank Nagari</option>
                                <option value="bsi">Bank Syariah Indonesia (BSI)</option>
                                <option value="paypal">PayPal Account (International)</option>
                            </x-ui.select>

                            <x-ui.input label="Bank Branch & City" name="bank_branch" placeholder="e.g. Limau Manih, Padang" required />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-ui.input label="{{ __('asic-participant.account_number') }}" name="account_number" placeholder="e.g. 0123456789" iconLeft="tag" x-model="accountNo" required />
                            <x-ui.input label="{{ __('asic-participant.account_holder') }}" name="account_holder" placeholder="Must match ID card" iconLeft="person" x-model="accountHolder" required />
                        </div>

                        <x-ui.textarea label="{{ __('asic-participant.refund_reason') }}" name="reason" placeholder="Explain reasons for manuscript withdrawal or refund claim..." rows="3" x-model="reason" required />

                        <x-ui.file-upload label="Upload ID Card / KTP & Payment Receipt" name="identity_file" accept=".pdf,.jpg,.png" maxSize="5 MB" hint="Required for anti-fraud reconciliation" required />
                    </div>

                    <x-slot:footer>
                        <div class="w-full flex items-center justify-between text-xs">
                            <x-ui.button variant="outline" href="{{ route('fe.participant.payment.status') }}">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="danger" iconLeft="send">
                                Submit Refund Claim
                            </x-ui.button>
                        </div>
                    </x-slot:footer>
                </x-ui.card>
            </form>
        </div>

        <!-- Right: Treasury SLA & Guidelines -->
        <div class="space-y-6">
            <x-ui.card title="Treasury SLA Guidelines" icon="schedule">
                <div class="space-y-3 text-xs text-on-surface-variant leading-relaxed">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-primary text-[18px] shrink-0 mt-0.5">verified_user</span>
                        <p><strong class="text-on-surface">Verification Period:</strong> Treasury reviews requests within 5 business days after submission.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-secondary text-[18px] shrink-0 mt-0.5">currency_exchange</span>
                        <p><strong class="text-on-surface">Disbursement Transfer:</strong> Approved funds are transferred within 14 business days to the specified account.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-error text-[18px] shrink-0 mt-0.5">warning</span>
                        <p><strong class="text-on-surface">Account Holder:</strong> Disbursement cannot be made to third-party accounts not matching the registered author.</p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection

