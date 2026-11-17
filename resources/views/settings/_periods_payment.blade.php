{{-- KF-11 periode pendaftaran & KF-23 informasi pembayaran (Bendahara/Admin, izin manage_tariffs). --}}
@php($t = trans('asic-treasurer.settings_panel'))
<section class="grid gap-5 lg:grid-cols-2" x-data="{
    i18n: @js($t), events: @js(\App\Models\Event::orderBy('nama_event')->get(['id_event', 'nama_event'])),
    periods: [], periodForm: null, editingPeriod: null, periodErrors: {}, periodError: '',
    pay: {}, payErrors: {}, payMessage: '', payError: '', busy: false,
    blankPeriod() { return { id_event: String(this.events[0]?.id_event || ''), nama_periode: '', tanggal_mulai: '', tanggal_selesai: '', status_periode: 'Aktif' }; },
    async init() { this.periodForm = this.blankPeriod(); await Promise.all([this.loadPeriods(), this.loadPay()]); },
    async loadPeriods() {
        const response = await window.asicApi.get('/api/registration-periods');
        if (!response.ok) { this.periodError = response.error || this.i18n.load_failed; return; }
        this.periods = response.data?.data || [];
    },
    async loadPay() {
        const response = await window.asicApi.get('/api/payment-settings');
        if (!response.ok) { this.payError = response.error || this.i18n.load_failed; return; }
        this.pay = response.data?.data || {};
    },
    editPeriod(row) { this.editingPeriod = row.id_period; this.periodErrors = {}; this.periodForm = { id_event: String(row.id_event), nama_periode: row.nama_periode, tanggal_mulai: row.tanggal_mulai, tanggal_selesai: row.tanggal_selesai, status_periode: row.status_periode }; },
    resetPeriod() { this.editingPeriod = null; this.periodErrors = {}; this.periodForm = this.blankPeriod(); },
    async savePeriod() {
        this.busy = true; this.periodErrors = {}; this.periodError = '';
        const payload = { ...this.periodForm, id_event: Number(this.periodForm.id_event) };
        const response = this.editingPeriod ? await window.asicApi.patch('/api/registration-periods/' + this.editingPeriod, payload) : await window.asicApi.post('/api/registration-periods', payload);
        this.busy = false;
        if (!response.ok) { this.periodErrors = response.errors || {}; this.periodError = response.error || this.i18n.save_failed; return; }
        this.resetPeriod(); await this.loadPeriods();
    },
    async removePeriod(row) {
        if (!window.confirm(this.i18n.confirm_delete)) return;
        const response = await window.asicApi.delete('/api/registration-periods/' + row.id_period);
        if (!response.ok) { this.periodError = response.error || this.i18n.save_failed; return; }
        await this.loadPeriods();
    },
    async savePay() {
        this.busy = true; this.payErrors = {}; this.payMessage = ''; this.payError = '';
        const response = await window.asicApi.put('/api/payment-settings', this.pay);
        this.busy = false;
        if (!response.ok) { this.payErrors = response.errors || {}; this.payError = response.error || this.i18n.save_failed; return; }
        this.pay = response.data?.data || this.pay; this.payMessage = response.data?.message || '';
    }
}">
    <article class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <div><h2 class="font-bold">{{ $t['periods_title'] }}</h2><p class="mt-1 text-xs text-on-surface-variant">{{ $t['periods_help'] }}</p></div>
        <p x-show="periodError" x-text="periodError" role="alert" class="rounded-xl bg-error-container p-3 text-sm text-on-error-container"></p>
        <form x-show="periodForm" @submit.prevent="savePeriod()" class="grid gap-3 sm:grid-cols-2">
            <label class="text-sm">{{ $t['event'] }}<select x-model="periodForm.id_event" class="mt-1 block w-full rounded-lg border-surface-container-highest"><template x-for="item in events" :key="item.id_event"><option :value="String(item.id_event)" x-text="item.nama_event"></option></template></select></label>
            <label class="text-sm">{{ $t['name'] }}<input x-model="periodForm.nama_periode" required maxlength="100" placeholder="Early Bird / Regular" class="mt-1 block w-full rounded-lg border-surface-container-highest"><span x-show="periodErrors.nama_periode" x-text="periodErrors.nama_periode?.[0]" class="text-xs text-error"></span></label>
            <label class="text-sm">{{ $t['start'] }}<input type="date" x-model="periodForm.tanggal_mulai" required class="mt-1 block w-full rounded-lg border-surface-container-highest"></label>
            <label class="text-sm">{{ $t['end'] }}<input type="date" x-model="periodForm.tanggal_selesai" required class="mt-1 block w-full rounded-lg border-surface-container-highest"><span x-show="periodErrors.tanggal_selesai" x-text="periodErrors.tanggal_selesai?.[0]" class="text-xs text-error"></span></label>
            <label class="text-sm">{{ $t['status'] }}<select x-model="periodForm.status_periode" class="mt-1 block w-full rounded-lg border-surface-container-highest"><option value="Aktif">{{ $t['active'] }}</option><option value="Tidak Aktif">{{ $t['inactive'] }}</option></select></label>
            <div class="flex items-end gap-2"><button :disabled="busy" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-40" x-text="editingPeriod ? i18n.save : i18n.add"></button><button type="button" x-show="editingPeriod" @click="resetPeriod()" class="rounded-lg border px-3 py-2 text-sm">{{ $t['cancel'] }}</button></div>
        </form>
        <ul class="divide-y divide-surface-container-high text-sm">
            <template x-for="row in periods" :key="row.id_period">
                <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <span><strong x-text="row.nama_periode"></strong> <span class="text-xs text-on-surface-variant" x-text="(row.nama_event || '') + ' · ' + asicDate(row.tanggal_mulai) + ' – ' + asicDate(row.tanggal_selesai) + ' · ' + (row.status_periode === 'Aktif' ? i18n.active : i18n.inactive)"></span></span>
                    <span class="space-x-2"><button type="button" @click="editPeriod(row)" class="icon-btn" aria-label="{{ $t['edit'] }}"><span class="material-symbols-outlined" aria-hidden="true">edit</span></button><button type="button" @click="removePeriod(row)" class="icon-btn icon-btn-danger" aria-label="{{ $t['delete'] }}"><span class="material-symbols-outlined" aria-hidden="true">delete</span></button></span>
                </li>
            </template>
        </ul>
    </article>

    <article class="space-y-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <div><h2 class="font-bold">{{ $t['payment_title'] }}</h2><p class="mt-1 text-xs text-on-surface-variant">{{ $t['payment_help'] }}</p></div>
        <p x-show="payError" x-text="payError" role="alert" class="rounded-xl bg-error-container p-3 text-sm text-on-error-container"></p>
        <p x-show="payMessage" x-text="payMessage" role="status" class="rounded-xl bg-secondary-container p-3 text-sm text-on-secondary-container"></p>
        <form @submit.prevent="savePay()" class="grid gap-3 sm:grid-cols-2">
            @foreach(['bank_name', 'account_name', 'account_no', 'swift_code', 'branch'] as $field)
                <label class="text-sm">{{ $t['fields'][$field] }}<input x-model="pay.{{ $field }}" class="mt-1 block w-full rounded-lg border-surface-container-highest"><span x-show="payErrors.{{ $field }}" x-text="payErrors.{{ $field }}?.[0]" class="text-xs text-error"></span></label>
            @endforeach
            @foreach(['qr_info', 'international_note'] as $field)
                <label class="text-sm sm:col-span-2">{{ $t['fields'][$field] }}<textarea x-model="pay.{{ $field }}" rows="2" class="mt-1 block w-full rounded-lg border-surface-container-highest"></textarea></label>
            @endforeach
            <div class="sm:col-span-2"><button :disabled="busy" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-40">{{ $t['save'] }}</button></div>
        </form>
    </article>
</section>
