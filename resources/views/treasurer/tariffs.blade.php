@extends('layouts.portal', ['role' => 'treasurer', 'roleName' => 'Tarif Pendaftaran', 'title' => __('asic-treasurer.tariffs.title').' — ASIC 2027'])

@section('content')
<main class="space-y-6" x-data="{
    events: @js($events->map(fn($event) => ['id_event' => $event->id_event, 'nama_event' => $event->nama_event])->values()),
    periods: @js($periods->map(fn($period) => ['id_period' => $period->id_period, 'id_event' => $period->id_event, 'nama_periode' => $period->nama_periode])->values()),
    fees: [], loading: true, saving: false, loadError: '', fieldErrors: {}, editingId: null,
    form: { id_event: '', id_period: '', registration_type: 'Regular', attendance_mode: 'Luring', tujuan_publikasi: 'Tidak', amount: '', discount: '0' },
    get availablePeriods() { return this.periods.filter(period => String(period.id_event) === String(this.form.id_event)); },
    async init() { await this.load(); },
    async load() {
        this.loading = true; this.loadError = '';
        const response = await window.asicApi.get('/api/registration-fees', { per_page: 100 });
        this.loading = false;
        if (!response.ok) { this.loadError = response.error || 'Tarif gagal dimuat.'; return; }
        const rows = response.data?.data;
        this.fees = Array.isArray(rows) ? rows : (rows?.data || []);
    },
    resetForm() { this.editingId = null; this.fieldErrors = {}; this.form = { id_event: '', id_period: '', registration_type: 'Regular', attendance_mode: 'Luring', tujuan_publikasi: 'Tidak', amount: '', discount: '0' }; },
    edit(fee) { this.editingId = fee.id_fee; this.fieldErrors = {}; this.form = { id_event: String(fee.id_event), id_period: String(fee.id_period), registration_type: fee.registration_type, attendance_mode: fee.attendance_mode, tujuan_publikasi: fee.tujuan_publikasi, amount: String(fee.amount), discount: String(fee.discount) }; window.scrollTo({ top: 0, behavior: 'smooth' }); },
    async save() {
        this.saving = true; this.fieldErrors = {};
        const payload = { ...this.form, id_event: Number(this.form.id_event), id_period: Number(this.form.id_period), amount: Number(this.form.amount), discount: Number(this.form.discount || 0) };
        const response = this.editingId ? await window.asicApi.patch('/api/registration-fees/' + this.editingId, payload) : await window.asicApi.post('/api/registration-fees', payload);
        this.saving = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; window.asicApi.showToast(response.error || 'Tarif gagal disimpan.'); return; }
        window.asicApi.showToast(response.data?.message || 'Tarif tersimpan.', 'success');
        this.resetForm(); await this.load();
    },
    async remove(fee) {
        if (!window.confirm('Hapus tarif ini?')) return;
        const response = await window.asicApi.delete('/api/registration-fees/' + fee.id_fee);
        if (!response.ok) { window.asicApi.showToast(response.error || 'Tarif gagal dihapus.'); return; }
        window.asicApi.showToast(response.data?.message || 'Tarif dihapus.', 'success'); await this.load();
    }
}">
    <header><h1 class="text-2xl font-bold text-on-surface">Tarif pendaftaran</h1><p class="mt-1 text-sm text-on-surface-variant">Kelola tarif melalui API registration-fees. Nilai mata uang mengikuti kolom amount yang tersedia di basis data (IDR).</p></header>
    <form @submit.prevent="save()" class="grid gap-4 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5 sm:grid-cols-2 lg:grid-cols-3">
        <h2 class="font-bold sm:col-span-2 lg:col-span-3" x-text="editingId ? 'Ubah tarif #' + editingId : 'Tambah tarif'"></h2>
        <label class="text-sm font-semibold">Event<select x-model="form.id_event" @change="form.id_period = ''" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option value="">Pilih event</option><template x-for="event in events" :key="event.id_event"><option :value="String(event.id_event)" x-text="event.nama_event"></option></template></select><span x-show="fieldErrors.id_event" x-text="fieldErrors.id_event?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Periode<select x-model="form.id_period" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option value="">Pilih periode</option><template x-for="period in availablePeriods" :key="period.id_period"><option :value="String(period.id_period)" x-text="period.nama_periode"></option></template></select><span x-show="fieldErrors.id_period" x-text="fieldErrors.id_period?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Kategori<select x-model="form.registration_type" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option>Student</option><option>Regular</option><option>Committee</option></select><span x-show="fieldErrors.registration_type" x-text="fieldErrors.registration_type?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Moda<select x-model="form.attendance_mode" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option>Daring</option><option>Luring</option></select><span x-show="fieldErrors.attendance_mode" x-text="fieldErrors.attendance_mode?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Opsi publikasi<select x-model="form.tujuan_publikasi" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><option>Tidak</option><option>Prosiding</option><option>Jurnal</option></select><span x-show="fieldErrors.tujuan_publikasi" x-text="fieldErrors.tujuan_publikasi?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Tarif (IDR)<input x-model="form.amount" type="number" min="0" step="0.01" required class="mt-1 block w-full rounded-xl border-surface-container-highest"><span x-show="fieldErrors.amount" x-text="fieldErrors.amount?.[0]" class="text-xs text-error"></span></label>
        <label class="text-sm font-semibold">Diskon (IDR)<input x-model="form.discount" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-xl border-surface-container-highest"><span x-show="fieldErrors.discount" x-text="fieldErrors.discount?.[0]" class="text-xs text-error"></span></label>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3"><button type="submit" :disabled="saving" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50" x-text="saving ? 'Menyimpan…' : (editingId ? 'Simpan perubahan' : 'Tambah tarif')"></button><button x-show="editingId" type="button" @click="resetForm()" class="rounded-xl border border-surface-container-highest px-5 py-2.5 text-sm font-semibold">Batal</button></div>
    </form>
    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">Memuat tarif…</p><p x-show="loadError" x-text="loadError" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-error"></p>
    <div x-show="!loading && !loadError" class="overflow-x-auto rounded-2xl border border-surface-container-highest bg-surface-container-lowest">
        <table class="w-full min-w-[900px] text-left text-sm"><thead><tr class="border-b border-surface-container-high text-xs uppercase text-on-surface-variant"><th class="p-3">Event / Periode</th><th class="p-3">Kategori</th><th class="p-3">Moda / Publikasi</th><th class="p-3">Tarif IDR</th><th class="p-3">Diskon</th><th class="p-3">Tindakan</th></tr></thead>
            <tbody><template x-for="fee in fees" :key="fee.id_fee"><tr class="border-b border-surface-container-highest"><td class="p-3"><span x-text="fee.event?.nama_event || fee.id_event"></span><span class="block text-xs text-on-surface-variant" x-text="fee.period?.nama_periode || fee.id_period"></span></td><td class="p-3" x-text="fee.registration_type"></td><td class="p-3"><span x-text="fee.attendance_mode"></span><span class="block text-xs text-on-surface-variant" x-text="fee.tujuan_publikasi"></span></td><td class="p-3" x-text="'IDR ' + new Intl.NumberFormat('id-ID').format(Number(fee.amount))"></td><td class="p-3" x-text="'IDR ' + new Intl.NumberFormat('id-ID').format(Number(fee.discount))"></td><td class="space-x-2 p-3"><button type="button" @click="edit(fee)" class="font-semibold text-primary">Ubah</button><button type="button" @click="remove(fee)" class="font-semibold text-error">Hapus</button></td></tr></template></tbody>
        </table>
        <p x-show="!fees.length" class="p-5 text-sm text-on-surface-variant">Belum ada tarif.</p>
    </div>
</main>
@endsection
