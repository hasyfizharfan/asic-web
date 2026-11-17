{{-- Pengelola konten publik Fase 1 (dipakai /secretariat/content dan /admin/content). Semua teks dari lang/asic-content. --}}
@php
    $m = trans('asic-content.manager');
    $fieldDefs = [
        'event-content' => [['section', 'select', \App\Models\EventContent::SECTIONS], ['judul_id', 'text'], ['judul_en', 'text'], ['isi_id', 'textarea'], ['isi_en', 'textarea'], ['tautan', 'text']],
        'important-dates' => [['label_id', 'text'], ['label_en', 'text'], ['tanggal_mulai', 'date'], ['tanggal_selesai', 'date']],
        'faqs' => [['pertanyaan_id', 'text'], ['pertanyaan_en', 'text'], ['jawaban_id', 'textarea'], ['jawaban_en', 'textarea'], ['kategori', 'text']],
        'contacts' => [['nama', 'text'], ['peran_id', 'text'], ['peran_en', 'text'], ['email', 'text'], ['telepon', 'text'], ['whatsapp', 'text']],
        'photos' => [['file', 'file'], ['caption_id', 'text'], ['caption_en', 'text']],
        'sponsorship-packages' => [['nama', 'text'], ['deskripsi_id', 'textarea'], ['deskripsi_en', 'textarea'], ['manfaat_id', 'textarea'], ['manfaat_en', 'textarea'], ['harga_teks', 'text'], ['kuota', 'number']],
        'legal-documents' => [['jenis', 'select', \App\Models\LegalDocument::TYPES], ['versi', 'text'], ['berlaku_mulai', 'date'], ['judul_id', 'text'], ['judul_en', 'text'], ['isi_id', 'textarea'], ['isi_en', 'textarea']],
    ];
    $titleField = ['event-content' => 'judul', 'important-dates' => 'label', 'faqs' => 'pertanyaan', 'contacts' => 'nama', 'photos' => 'caption', 'sponsorship-packages' => 'nama', 'legal-documents' => 'judul', 'sponsorship-inquiries' => 'nama_organisasi'];
    $keys = ['event-content' => 'id_content', 'important-dates' => 'id_date', 'faqs' => 'id_faq', 'contacts' => 'id_contact', 'photos' => 'id_photo', 'sponsorship-packages' => 'id_package', 'sponsorship-inquiries' => 'id_inquiry', 'legal-documents' => 'id_legal'];
@endphp
<section class="space-y-5" x-data="{
    i18n: @js($m), sections: @js(trans('asic-content.sections')), statuses: @js(trans('asic-content.statuses')), legalTypes: @js(trans('asic-content.legal_types')),
    fieldDefs: @js($fieldDefs), titleField: @js($titleField), keys: @js($keys),
    inquiryStatuses: @js(\App\Models\SponsorshipInquiry::STATUSES),
    tab: 'event-content', events: [], idEvent: '', rows: [], loading: true, error: '',
    modal: false, editing: null, form: {}, fieldErrors: {}, formError: '', saving: false,
    missingLegal: [], preview: null,
    get eventScoped() { return !['legal-documents'].includes(this.tab); },
    get canAdd() { return !['sponsorship-inquiries'].includes(this.tab); },
    async init() {
        const response = await window.asicApi.get('/api/content/events', { per_page: 100 });
        if (response.ok) { this.events = response.data?.data || []; this.idEvent = String(this.events[0]?.id_event || ''); }
        await this.load();
        await this.loadLegalStatus();
    },
    label(field) { return this.i18n.fields[field] || field; },
    title(row) { const f = this.titleField[this.tab]; return row[f] ?? row[f + '_id'] ?? row[f + '_en'] ?? ('#' + row[this.keys[this.tab]]); },
    async load() {
        this.loading = true; this.error = '';
        const params = { per_page: 200 };
        if (this.eventScoped && this.idEvent) params.id_event = this.idEvent;
        const response = await window.asicApi.get('/api/content/' + this.tab, params);
        this.loading = false;
        if (!response.ok) { this.error = response.error || this.i18n.load_failed; this.rows = []; return; }
        this.rows = response.data?.data || [];
        if (this.tab === 'legal-documents') this.missingLegal = response.data?.missing_published || [];
    },
    async loadLegalStatus() {
        const response = await window.asicApi.get('/api/content/legal-documents');
        if (response.ok) this.missingLegal = response.data?.missing_published || [];
    },
    switchTab(key) { this.tab = key; this.preview = null; this.load(); },
    blank() { const form = {}; (this.fieldDefs[this.tab] || []).forEach(([name, type, options]) => { form[name] = type === 'select' ? options[0] : ''; }); return form; },
    openNew() { this.editing = null; this.form = this.blank(); this.fieldErrors = {}; this.formError = ''; this.modal = true; },
    openEdit(row) { this.editing = row[this.keys[this.tab]]; const form = {}; (this.fieldDefs[this.tab] || []).forEach(([name, type]) => { if (type !== 'file') form[name] = row[name] == null ? '' : String(row[name]).slice(0, type === 'date' ? 10 : undefined); }); this.form = form; this.fieldErrors = {}; this.formError = ''; this.modal = true; },
    payload() { const data = { ...this.form }; Object.keys(data).forEach(k => { if (data[k] === '') data[k] = null; }); if (this.eventScoped && this.tab !== 'legal-documents') data.id_event = this.idEvent ? Number(this.idEvent) : null; if (this.editing) { delete data.jenis; delete data.versi; } return data; },
    async save(event) {
        this.saving = true; this.fieldErrors = {}; this.formError = '';
        let response;
        const base = '/api/content/' + this.tab;
        if (this.tab === 'photos' && !this.editing) {
            const data = new FormData(event.target); data.append('id_event', this.idEvent);
            response = await window.asicApi.upload(base, data);
        } else {
            response = this.editing ? await window.asicApi.patch(base + '/' + this.editing, this.payload()) : await window.asicApi.post(base, this.payload());
        }
        this.saving = false;
        if (!response.ok) { this.fieldErrors = response.errors || {}; this.formError = response.error || this.i18n.save_failed; return; }
        this.modal = false; await this.load(); await this.loadLegalStatus();
    },
    async setStatus(row, status) {
        const response = await window.asicApi.patch('/api/content/' + this.tab + '/' + row[this.keys[this.tab]], { status });
        if (!response.ok) { this.error = response.error || this.i18n.save_failed; return; }
        await this.load();
    },
    async remove(row) {
        if (!window.confirm(this.i18n.confirm_delete)) return;
        const response = await window.asicApi.delete('/api/content/' + this.tab + '/' + row[this.keys[this.tab]]);
        if (!response.ok) { this.error = response.error || this.i18n.save_failed; return; }
        await this.load();
    },
    async move(index, delta) {
        const other = this.rows[index + delta]; const row = this.rows[index];
        if (!other) return;
        const items = this.rows.map((r, i) => ({ id: r[this.keys[this.tab]], urutan: i }));
        [items[index].urutan, items[index + delta].urutan] = [index + delta, index];
        let response;
        if (this.tab === 'photos') {
            await window.asicApi.patch('/api/content/photos/' + row.id_photo, { urutan: index + delta });
            response = await window.asicApi.patch('/api/content/photos/' + other.id_photo, { urutan: index });
        } else {
            response = await window.asicApi.patch('/api/content/' + this.tab + '/reorder', { items });
        }
        if (!response.ok) { this.error = response.error || this.i18n.save_failed; return; }
        await this.load();
    },
    async saveInquiry(row) {
        const response = await window.asicApi.patch('/api/content/sponsorship-inquiries/' + row.id_inquiry, { status: row.status, catatan_internal: row.catatan_internal || null });
        if (!response.ok) { this.error = response.error || this.i18n.save_failed; return; }
        await this.load();
    },
    async publishLegal(row) {
        if (!window.confirm(this.i18n.confirm_publish)) return;
        const response = await window.asicApi.post('/api/content/legal-documents/' + row.id_legal + '/publish', {});
        if (!response.ok) { this.error = response.error || this.i18n.save_failed; return; }
        await this.load(); await this.loadLegalStatus();
    }
}">
    <header class="flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-surface-container-low p-6">
        <div><p class="text-xs font-bold uppercase tracking-widest text-primary">{{ $m['eyebrow'] }}</p><h1 class="mt-1 text-2xl font-bold">{{ $m['heading'] }}</h1><p class="mt-1 max-w-3xl text-sm text-on-surface-variant">{{ $m['subtitle'] }}</p></div>
        <button type="button" x-show="canAdd" @click="openNew()" :disabled="eventScoped && !idEvent" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-40">{{ $m['add'] }}</button>
    </header>

    <p x-show="missingLegal.length" role="alert" class="rounded-xl bg-tertiary-container p-4 text-sm text-on-tertiary-container" x-text="i18n.missing_legal.replace(':types', missingLegal.map(t => legalTypes[t] || t).join(', '))"></p>

    <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-wrap gap-2" role="tablist">
            <template x-for="(tabLabel, key) in i18n.tabs" :key="key"><button type="button" role="tab" :aria-selected="tab === key" @click="switchTab(key)" :class="tab === key ? 'bg-primary text-white' : 'bg-surface-container'" class="rounded-lg px-3 py-2 text-sm" x-text="tabLabel"></button></template>
        </div>
        <label x-show="eventScoped" class="ml-auto text-sm font-semibold">{{ $m['event'] }}<select x-model="idEvent" @change="load()" class="mt-1 block rounded-xl border-surface-container-highest"><template x-for="item in events" :key="item.id_event"><option :value="String(item.id_event)" x-text="item.nama_event"></option></template></select></label>
    </div>

    <p x-show="loading" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $m['loading'] }}</p>
    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <p x-show="!loading && !error && rows.length === 0" class="rounded-xl border p-8 text-center text-sm text-on-surface-variant">{{ $m['empty'] }}</p>

    {{-- Permintaan sponsor: hanya status & catatan internal yang dapat diubah --}}
    <div x-show="tab === 'sponsorship-inquiries' && rows.length" class="space-y-3">
        <template x-for="row in rows" :key="row.id_inquiry">
            <article class="rounded-xl border border-surface-container-high bg-surface-container-lowest p-4 text-sm">
                <div class="flex flex-wrap justify-between gap-2"><p class="font-bold"><span x-text="row.jenis"></span> · <span x-text="row.nama_organisasi"></span></p><p class="text-xs text-on-surface-variant"><span>{{ $m['received_at'] }}</span> <span x-text="asicDateTime(row.created_at)"></span></p></div>
                <p class="mt-1 text-xs text-on-surface-variant" x-text="[row.nama_kontak, row.email, row.telepon].filter(Boolean).join(' · ')"></p>
                <p class="mt-2 whitespace-pre-line" x-text="row.pesan"></p>
                <div class="mt-3 grid gap-2 sm:grid-cols-[12rem_1fr_auto]">
                    <select x-model="row.status" class="rounded-lg border-surface-container-highest text-sm"><template x-for="s in inquiryStatuses" :key="s"><option :value="s" x-text="statuses[s] || s"></option></template></select>
                    <input x-model="row.catatan_internal" :placeholder="i18n.internal_note" class="rounded-lg border-surface-container-highest text-sm">
                    <button type="button" @click="saveInquiry(row)" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white">{{ $m['save'] }}</button>
                </div>
            </article>
        </template>
    </div>

    <div x-show="tab !== 'sponsorship-inquiries' && rows.length && !loading" class="overflow-x-auto rounded-xl border border-surface-container-high bg-surface-container-lowest shadow-sm">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b border-surface-container-high bg-surface-container-low text-xs uppercase"><th class="p-3">#</th><th class="p-3">{{ $m['fields']['judul_id'] }}</th><th class="p-3">{{ $m['status'] }}</th><th class="p-3">{{ $m['actions'] }}</th></tr></thead>
            <tbody>
                <template x-for="(row, index) in rows" :key="row[keys[tab]]">
                    <tr class="border-b border-surface-container-highest align-top">
                        <td class="p-3 text-xs text-on-surface-variant" x-text="index + 1"></td>
                        <td class="p-3">
                            <img x-show="tab === 'photos' && row.url" :src="row.url" :alt="row.caption_id || row.caption_en || ''" class="mb-2 h-16 w-24 rounded object-cover">
                            <p class="font-semibold" x-text="title(row)"></p>
                            <p class="text-xs text-on-surface-variant" x-show="tab === 'event-content'" x-text="sections[row.section] || row.section"></p>
                            <p class="text-xs text-on-surface-variant" x-show="tab === 'legal-documents'" x-text="(legalTypes[row.jenis] || row.jenis) + ' · v' + row.versi"></p>
                            <p class="text-xs text-on-surface-variant" x-show="tab === 'important-dates'" x-text="asicDate(row.tanggal_mulai) + (row.tanggal_selesai ? ' – ' + asicDate(row.tanggal_selesai) : '')"></p>
                            <span x-show="row.translation_complete === false" class="mt-1 inline-block rounded-full bg-tertiary-container px-2 py-0.5 text-[11px] font-semibold text-on-tertiary-container">{{ $m['incomplete_translation'] }}</span>
                        </td>
                        <td class="p-3"><span class="rounded-full bg-surface-container px-2 py-1 text-xs font-semibold" x-text="statuses[row.status] || row.status"></span></td>
                        <td class="whitespace-nowrap p-3 text-sm">
                            <template x-if="tab !== 'legal-documents'"><span class="icon-btn-group">
                                <button type="button" x-show="tab !== 'photos'" @click="openEdit(row)" class="icon-btn" aria-label="{{ $m['edit'] }}"><span class="material-symbols-outlined" aria-hidden="true">edit</span></button>
                                <button type="button" x-show="tab === 'photos'" @click="openEdit(row)" class="icon-btn" aria-label="{{ $m['edit'] }}"><span class="material-symbols-outlined" aria-hidden="true">edit</span></button>
                                <button type="button" @click="setStatus(row, row.status === 'Dipublikasikan' ? 'Draft' : 'Dipublikasikan')" class="icon-btn icon-btn-muted" :aria-label="row.status === 'Dipublikasikan' ? i18n.unpublish : i18n.publish"><span class="material-symbols-outlined" aria-hidden="true" x-text="row.status === 'Dipublikasikan' ? 'visibility_off' : 'publish'"></span></button>
                                <button type="button" @click="move(index, -1)" :disabled="index === 0" class="icon-btn icon-btn-muted" :aria-label="i18n.move_up"><span class="material-symbols-outlined" aria-hidden="true">arrow_upward</span></button>
                                <button type="button" @click="move(index, 1)" :disabled="index === rows.length - 1" class="icon-btn icon-btn-muted" :aria-label="i18n.move_down"><span class="material-symbols-outlined" aria-hidden="true">arrow_downward</span></button>
                                <button type="button" @click="remove(row)" class="icon-btn icon-btn-danger" aria-label="{{ $m['delete'] }}"><span class="material-symbols-outlined" aria-hidden="true">delete</span></button>
                            </span></template>
                            <template x-if="tab === 'legal-documents'"><span class="icon-btn-group">
                                <button type="button" @click="preview = row" class="icon-btn" aria-label="{{ $m['preview'] }}"><span class="material-symbols-outlined" aria-hidden="true">visibility</span></button>
                                <template x-if="row.status === 'Draft'"><span class="icon-btn-group"><button type="button" @click="openEdit(row)" class="icon-btn" aria-label="{{ $m['edit'] }}"><span class="material-symbols-outlined" aria-hidden="true">edit</span></button><button type="button" @click="publishLegal(row)" class="icon-btn" aria-label="{{ $m['publish'] }}"><span class="material-symbols-outlined" aria-hidden="true">publish</span></button><button type="button" @click="remove(row)" class="icon-btn icon-btn-danger" aria-label="{{ $m['delete'] }}"><span class="material-symbols-outlined" aria-hidden="true">delete</span></button></span></template>
                                <span x-show="row.status !== 'Draft'" class="text-xs text-on-surface-variant">{{ $m['locked'] }}</span>
                            </span></template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Pratinjau dokumen hukum: teks biasa --}}
    <section x-show="preview" class="space-y-2 rounded-2xl border border-surface-container-high bg-surface-container-lowest p-5">
        <div class="flex justify-between"><h2 class="font-bold" x-text="preview?.judul_id || preview?.judul_en"></h2><button type="button" @click="preview = null" class="icon-btn icon-btn-muted" aria-label="{{ $m['close'] }}"><span class="material-symbols-outlined" aria-hidden="true">close</span></button></div>
        <p class="whitespace-pre-line text-sm" x-text="preview?.isi_id"></p>
        <hr class="border-surface-container-high">
        <p class="whitespace-pre-line text-sm" x-text="preview?.isi_en"></p>
    </section>

    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="modal = false">
        <section class="max-h-[90vh] w-full max-w-2xl space-y-4 overflow-y-auto rounded-2xl bg-white p-6">
            <h2 class="text-lg font-bold" x-text="(editing ? i18n.edit : i18n.add) + ' · ' + i18n.tabs[tab]"></h2>
            <p x-show="formError" x-text="formError" role="alert" class="text-sm text-error"></p>
            <form @submit.prevent="save($event)" class="space-y-3">
                <template x-for="[name, type, options] in fieldDefs[tab] || []" :key="name">
                    <div x-show="!(editing && (type === 'file' || (tab === 'legal-documents' && (name === 'jenis' || name === 'versi'))))">
                        <label class="block space-y-1 text-sm"><span x-text="label(name)"></span>
                            <template x-if="type === 'textarea'"><textarea x-model="form[name]" :name="name" rows="5" class="w-full rounded-lg border-surface-container-highest"></textarea></template>
                            <template x-if="type === 'select'"><select x-model="form[name]" :name="name" class="w-full rounded-lg border-surface-container-highest"><template x-for="option in options" :key="option"><option :value="option" x-text="sections[option] || legalTypes[option] || option"></option></template></select></template>
                            <template x-if="type === 'file'"><input type="file" :name="name" accept="image/jpeg,image/png,image/webp" class="w-full text-sm"></template>
                            <template x-if="['text', 'date', 'number'].includes(type)"><input :type="type" x-model="form[name]" :name="name" class="w-full rounded-lg border-surface-container-highest"></template>
                        </label>
                        <p x-show="fieldErrors[name]" x-text="(fieldErrors[name] || [])[0]" class="text-xs text-error"></p>
                    </div>
                </template>
                <div class="flex justify-end gap-2"><button type="button" @click="modal = false" class="rounded-lg border px-4 py-2 text-sm">{{ $m['cancel'] }}</button><button :disabled="saving" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white" x-text="saving ? i18n.saving : i18n.save"></button></div>
            </form>
        </section>
    </div>
</section>
