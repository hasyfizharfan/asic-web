@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => __('asic-treasurer.payment_queue.title'), 'nav' => $treasurerNav])

@php $t = trans('asic-treasurer.payment_queue'); @endphp

@section('content')
<main class="space-y-6" x-data="{
    i18n: @js($t), invoices: [], loading: true, loadError: '', actionError: '', status: 'Menunggu Verifikasi', search: '',
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/payments', { status: this.status, search: this.search });
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || this.i18n.load_failed; return; }
        this.invoices = response.data?.data?.data || response.data?.data || [];
    },
    setStatus(value) { this.status = value; this.load(); },
    badge(status) {
        return { 'Terverifikasi': 'bg-[#E6F4EA] text-primary', 'Ditolak': 'bg-error-container text-on-error-container', 'Kedaluwarsa': 'bg-error-container text-on-error-container', 'Menunggu Verifikasi': 'bg-[#FEF6D8] text-[#7a5d00]' }[status] || 'bg-surface-container text-on-surface-variant';
    },
    isOverdue(invoice) {
        const due = invoice.tanggal_jatuh_tempo_baru || invoice.tanggal_jatuh_tempo;
        return invoice.status_pembayaran === 'Menunggu Pembayaran' && due && new Date(due) < new Date(new Date().toDateString());
    },
    async verify(invoice, proof, valid) {
        this.actionError = '';
        const reason = valid ? null : window.prompt(this.i18n.reject_prompt);
        if (!valid && !reason) return;
        const response = await window.asicApi.post('/api/payment-proofs/' + proof.id_payment_proof + '/verify', {
            hasil_verifikasi: valid ? 'Valid' : 'Tidak Valid',
            ...(reason ? { alasan_penolakan: reason } : {})
        });
        if (!response.ok) { this.actionError = response.error || this.i18n.verify_failed; window.asicApi.showToast(this.actionError); return; }
        invoice.status_pembayaran = response.data?.data?.status_pembayaran || (valid ? 'Terverifikasi' : 'Ditolak');
        await this.load();
        window.asicApi.showToast(response.data?.message || this.i18n.updated, 'success');
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">{{ $t['eyebrow'] }}</p>
            <h1 class="mt-1 text-[27px] font-bold leading-tight sm:text-[34px]">{{ $t['title'] }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">{{ $t['subtitle'] }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="load()" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] border border-surface-container-highest bg-surface-container-lowest px-4 py-2 text-sm font-bold text-primary hover:border-primary"><span class="material-symbols-outlined text-[18px]">refresh</span>{{ $t['refresh'] }}</button>
            <a href="/api/exports/payments" class="inline-flex min-h-10 items-center gap-2 rounded-[9px] bg-[#F5C518] px-4 py-2 text-sm font-bold text-[#182820] hover:bg-[#fed649]"><span class="material-symbols-outlined text-[18px]">download</span>{{ $t['export'] }}</a>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card :title="$t['stat_total']" :value="$counts['total']" icon="receipt_long" :description="$t['stat_total_hint']" />
        <x-ui.stat-card :title="$t['stat_waiting']" :value="$counts['waiting']" icon="hourglass_top" color="warning" :description="$t['stat_waiting_hint']" />
        <x-ui.stat-card :title="$t['stat_verified']" :value="$counts['verified']" icon="check_circle" :description="$t['stat_verified_hint']" />
        <x-ui.stat-card :title="$t['stat_overdue']" :value="$counts['overdue']" icon="warning" color="error" :description="$t['stat_overdue_hint']" />
    </section>

    <section class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-4 shadow-sm">
        <div class="flex flex-wrap gap-3">
            <label class="relative min-w-[240px] flex-1">
                <span class="sr-only">{{ $t['search'] }}</span>
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-on-surface-variant">search</span>
                <input x-model="search" @keydown.enter.prevent="load()" placeholder="{{ $t['search'] }}" class="w-full rounded-[9px] border-surface-container-highest bg-surface-container-low py-2.5 pl-10 text-sm">
            </label>
            <button type="button" @click="load()" class="min-h-10 rounded-[9px] bg-primary px-5 py-2 text-sm font-bold text-white hover:bg-primary-container">{{ $t['search_btn'] }}</button>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs" role="group" aria-label="{{ $t['status_filter'] }}">
            <span class="font-bold uppercase tracking-wider text-on-surface-variant">{{ $t['status_filter'] }}:</span>
            @foreach([['all', $t['all'], $counts['total']], ['Menunggu Verifikasi', $t['waiting'], $counts['waiting']], ['Terverifikasi', $t['verified'], $counts['verified']], ['Ditolak', $t['rejected'], $counts['rejected']]] as [$value, $label, $n])
                <button type="button" @click="setStatus(@js($value))" :aria-pressed="status === @js($value)"
                    :class="status === @js($value) ? 'bg-primary text-white' : 'bg-surface-container text-on-surface hover:bg-surface-container-high'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 font-semibold">{{ $label }} <span class="rounded-full bg-black/10 px-1.5">{{ $n }}</span></button>
            @endforeach
        </div>
    </section>

    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t['loading'] }}</p>
    <p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container" style="display: none;"></p>
    <div x-show="!loading && !loadError && invoices.length === 0" class="rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-8 text-center text-sm text-on-surface-variant" style="display: none;">{{ $t['empty'] }}</div>
    <div x-show="!loading && !loadError && invoices.length > 0" class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest shadow-sm" style="display: none;">
        <table class="w-full min-w-[860px] text-left text-sm">
            <thead class="bg-surface-container-low text-[11px] font-bold uppercase tracking-wider text-on-surface-variant"><tr><th class="px-5 py-3">{{ $t['col_invoice'] }}</th><th class="px-3 py-3">{{ $t['col_participant'] }}</th><th class="px-3 py-3">{{ $t['col_amount'] }}</th><th class="px-3 py-3">{{ $t['col_due'] }}</th><th class="px-3 py-3">{{ $t['col_status'] }}</th><th class="px-3 py-3">{{ $t['col_proof'] }}</th><th class="px-5 py-3">{{ $t['col_action'] }}</th></tr></thead>
            <tbody class="divide-y divide-surface-container-highest"><template x-for="invoice in invoices" :key="invoice.id_invoice"><tr class="align-top">
                <td class="px-5 py-3"><a :href="'/treasurer/payments/' + invoice.id_invoice" class="font-mono font-bold text-primary" x-text="'#' + invoice.nomor_invoice"></a></td>
                <td class="px-3 py-3"><p class="font-semibold" x-text="invoice.registration?.participant?.full_name || invoice.registration?.participant?.user_account?.email || '-'"></p><p class="text-xs text-on-surface-variant" x-text="[invoice.registration?.participant?.institution, invoice.registration?.participation_type].filter(Boolean).join(' · ')"></p></td>
                <td class="px-3 py-3"><p class="font-bold" x-text="window.asicMoney(invoice.total_tagihan, invoice.mata_uang)"></p><p class="text-xs text-on-surface-variant" x-text="invoice.mata_uang === 'USD' ? i18n.international : i18n.domestic"></p></td>
                <td class="px-3 py-3"><span x-text="invoice.tanggal_jatuh_tempo_baru || invoice.tanggal_jatuh_tempo || '-'"></span> <span x-show="isOverdue(invoice)" class="ml-1 rounded-full bg-error-container px-2 py-0.5 text-[11px] font-bold text-on-error-container">{{ $t['overdue'] }}</span></td>
                <td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="badge(invoice.status_pembayaran)" x-text="invoice.status_pembayaran"></span></td>
                <td class="px-3 py-3"><template x-if="invoice.latest_payment_proof"><a :href="'/api/payment-proofs/' + invoice.latest_payment_proof.id_payment_proof + '/file'" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-semibold text-primary"><span class="material-symbols-outlined text-[18px]">attach_file</span>{{ $t['view_proof'] }}</a></template><span x-show="!invoice.latest_payment_proof">—</span></td>
                <td class="px-5 py-3"><div class="flex gap-2" x-show="invoice.status_pembayaran === 'Menunggu Verifikasi' && invoice.latest_payment_proof"><button type="button" @click="verify(invoice, invoice.latest_payment_proof, true)" class="min-h-9 rounded-[9px] bg-primary px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-container">{{ $t['verify'] }}</button><button type="button" @click="verify(invoice, invoice.latest_payment_proof, false)" class="min-h-9 rounded-[9px] border border-error px-3 py-1.5 text-xs font-bold text-error">{{ $t['reject'] }}</button></div></td>
            </tr></template></tbody>
        </table>
    </div>
</main>
@endsection
