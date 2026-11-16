@php($t = fn ($k, $r = []) => __('asic-notifications.page.'.$k, $r))
@extends('layouts.portal', ['role' => 'participant', 'roleName' => __('asic-notifications.page.title'), 'nav' => $participantNav, 'title' => __('asic-notifications.page.title')])

@section('content')
<main class="mx-auto max-w-3xl space-y-5" x-data="{
    i18n: @js(trans('asic-notifications.page')),
    items: [], unreadOnly: false, page: 1, lastPage: 1, unread: 0,
    loading: true, error: '', busy: false,
    announcements: [], announcementsError: '',
    async init() { await Promise.all([this.load(true), this.loadAnnouncements()]); },
    async load(reset = false) {
        if (reset) { this.page = 1; this.items = []; }
        this.loading = true; this.error = '';
        const params = { page: this.page, per_page: 15 };
        if (this.unreadOnly) params.unread = 1;
        const response = await window.asicApi.get('/api/me/notifications', params);
        this.loading = false;
        if (!response.ok) { this.error = response.error || this.i18n.load_failed; return; }
        this.items = reset ? (response.data?.data || []) : this.items.concat(response.data?.data || []);
        this.lastPage = response.data?.pagination?.last_page || 1;
        this.unread = Number(response.data?.unread_count || 0);
    },
    async more() { if (this.page < this.lastPage) { this.page++; await this.load(false); } },
    async markRead(item) {
        const response = await window.asicApi.patch('/api/me/notifications/' + item.id + '/read', {});
        if (!response.ok) { window.asicApi.showToast(response.error || this.i18n.action_failed); return; }
        item.read_at = response.data?.data?.read_at;
        this.unread = Math.max(0, this.unread - 1);
    },
    async markAll() {
        this.busy = true;
        const response = await window.asicApi.post('/api/me/notifications/read-all', {});
        this.busy = false;
        if (!response.ok) { window.asicApi.showToast(response.error || this.i18n.action_failed); return; }
        await this.load(true);
    },
    async loadAnnouncements() {
        const response = await window.asicApi.get('/api/announcements', { per_page: 5 });
        if (!response.ok) { this.announcementsError = response.error || this.i18n.load_failed; return; }
        const rows = response.data?.data;
        this.announcements = Array.isArray(rows) ? rows : (rows?.data || []);
    },
    when(value) { return value ? new Date(value).toLocaleString(document.documentElement.lang === 'en' ? 'en-GB' : 'id-ID') : ''; }
}">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-on-surface">{{ $t('heading') }}</h1><p class="mt-1 text-sm text-on-surface-variant">{{ $t('subtitle') }}</p></div>
        <button type="button" @click="markAll()" :disabled="busy || unread === 0" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white disabled:opacity-40">{{ $t('mark_all_read') }}</button>
    </header>

    <div class="flex gap-2 text-sm" role="tablist">
        <button type="button" role="tab" :aria-selected="!unreadOnly" @click="unreadOnly = false; load(true)" :class="!unreadOnly ? 'bg-primary text-white' : 'bg-surface-container-low'" class="rounded-full px-4 py-1.5 font-semibold">{{ $t('tab_all') }}</button>
        <button type="button" role="tab" :aria-selected="unreadOnly" @click="unreadOnly = true; load(true)" :class="unreadOnly ? 'bg-primary text-white' : 'bg-surface-container-low'" class="rounded-full px-4 py-1.5 font-semibold"><span>{{ $t('tab_unread') }}</span> <span x-show="unread > 0" x-text="'(' + unread + ')'"></span></button>
    </div>

    <p x-show="loading && items.length === 0" role="status" class="rounded-xl bg-surface-container-low p-4 text-sm">{{ $t('loading') }}</p>
    <p x-show="error" x-text="error" role="alert" class="rounded-xl bg-error-container p-4 text-sm text-on-error-container"></p>
    <p x-show="!loading && !error && items.length === 0" class="rounded-xl border border-surface-container-highest p-5 text-sm text-on-surface-variant" x-text="unreadOnly ? i18n.empty_unread : i18n.empty"></p>

    <ul class="space-y-3">
        <template x-for="item in items" :key="item.id">
            <li class="rounded-2xl border p-4" :class="item.read_at ? 'border-surface-container-highest bg-surface-container-lowest' : 'border-primary/40 bg-primary-fixed/20'">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-bold" x-text="item.title"></p>
                        <p class="mt-1 text-sm text-on-surface-variant" x-text="item.body"></p>
                        <p class="mt-2 text-xs text-on-surface-variant" x-text="when(item.created_at)"></p>
                    </div>
                    <div class="flex shrink-0 gap-2 text-sm">
                        <a x-show="item.url" :href="item.url" class="font-semibold text-primary">{{ $t('open') }}</a>
                        <button type="button" x-show="!item.read_at" @click="markRead(item)" class="font-semibold text-on-surface-variant">{{ $t('mark_read') }}</button>
                    </div>
                </div>
            </li>
        </template>
    </ul>
    <button type="button" x-show="page < lastPage" @click="more()" :disabled="loading" class="rounded-xl border border-surface-container-highest px-4 py-2 text-sm font-semibold">{{ $t('load_more') }}</button>

    <section class="space-y-3 rounded-2xl border border-surface-container-highest bg-surface-container-lowest p-5">
        <h2 class="font-bold">{{ $t('announcements') }}</h2>
        <p x-show="announcementsError" x-text="announcementsError" role="alert" class="text-sm text-error"></p>
        <p x-show="!announcementsError && announcements.length === 0" class="text-sm text-on-surface-variant">{{ $t('announcements_empty') }}</p>
        <template x-for="row in announcements" :key="row.id_announcement">
            <article class="border-t border-surface-container-highest pt-3"><p class="font-semibold" x-text="row.judul"></p><p class="mt-1 whitespace-pre-line text-sm text-on-surface-variant" x-text="row.isi_pengumuman"></p><p class="mt-1 text-xs text-on-surface-variant" x-text="when(row.tanggal_publikasi)"></p></article>
        </template>
    </section>
</main>
@endsection
