{{--
    KF-30: lonceng notifikasi bergaya popup (seperti YouTube). Klik lonceng → panel berisi 8 notifikasi terbaru
    milik pengguna sendiri, keadaan kosong/gagal/memuat, tandai semua dibaca, dan tautan "Lihat semua" (peserta).
    Klik satu notifikasi → ditandai dibaca lalu membuka halamannya. Di HP panel melebar selebar layar.
--}}
@props(['allUrl' => null, 'staff' => false])
@php($b = trans('asic-notifications.bell'))
@php($bellJs = ['failed' => $b['failed'], 'just_now' => $b['just_now'], 'unread' => $b['unread']])
<div class="relative" x-data="{
    open: false, unread: 0, items: [], loaded: false, loading: false, error: false, busy: false,
    i18n: @js($bellJs),
    icons: {
        loa_issued: 'verified', invoice_issued: 'receipt_long', receipt_issued: 'task_alt', rejection_issued: 'cancel',
        certificate_issued: 'workspace_premium', invoice_extended: 'event_repeat', invoice_expired: 'event_busy',
        invoice_repriced: 'price_change', registration_cancelled: 'block', payment_valid: 'paid', payment_invalid: 'money_off',
        abstract_accepted: 'thumb_up', abstract_rejected: 'thumb_down', reregistered: 'how_to_reg',
        payment_proof_submitted: 'upload_file', abstract_submitted: 'description', abstract_resubmitted: 'restart_alt', sponsorship_inquiry: 'handshake'
    },
    tone(kind) {
        if (['rejection_issued', 'invoice_expired', 'registration_cancelled', 'payment_invalid', 'abstract_rejected'].includes(kind)) return 'bg-[#FDECEA] text-error';
        if (['invoice_extended', 'invoice_repriced', 'abstract_resubmitted'].includes(kind)) return 'bg-[#FEF6D8] text-[#7a5d00]';
        return 'bg-[#E6F4EA] text-primary';
    },
    async count() {
        const r = await window.asicApi.get('/api/me/notifications/unread-count');
        if (r.ok) this.unread = Number(r.data?.data?.unread_count || 0);
    },
    async load() {
        this.loading = true; this.error = false;
        const r = await window.asicApi.get('/api/me/notifications', { per_page: 8 });
        this.loading = false;
        if (!r.ok) { this.error = true; return; }
        this.items = r.data?.data || [];
        this.unread = Number(r.data?.unread_count || 0);
        this.loaded = true;
    },
    toggle() { this.open = !this.open; if (this.open) this.load(); },
    async openItem(item) {
        if (!item.read_at) {
            const r = await window.asicApi.patch('/api/me/notifications/' + item.id + '/read', {});
            if (r.ok) { item.read_at = r.data?.data?.read_at || new Date().toISOString(); this.unread = Math.max(0, this.unread - 1); }
        }
        if (item.url) window.location.href = item.url;
    },
    async markAll() {
        this.busy = true;
        const r = await window.asicApi.post('/api/me/notifications/read-all', {});
        this.busy = false;
        if (!r.ok) { window.asicApi.showToast(this.i18n.failed); return; }
        const now = new Date().toISOString();
        this.items.forEach((i) => { i.read_at = i.read_at || now; });
        this.unread = 0;
    },
    ago(iso) {
        if (!iso) return '';
        const rtf = new Intl.RelativeTimeFormat(document.documentElement.lang === 'en' ? 'en' : 'id', { numeric: 'always' });
        const s = Math.round((new Date(iso) - Date.now()) / 1000);
        for (const [unit, sec] of [['year', 31536000], ['month', 2592000], ['week', 604800], ['day', 86400], ['hour', 3600], ['minute', 60]]) {
            if (Math.abs(s) >= sec) return rtf.format(Math.round(s / sec), unit);
        }
        return this.i18n.just_now;
    }
}" x-init="count()" @asic-notifications-changed.window="count(); if (open) load()" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="toggle()" :aria-expanded="open.toString()" aria-haspopup="dialog" aria-controls="asic-bell-panel"
        class="relative inline-flex rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface"
        :class="open ? 'bg-surface-container-low text-on-surface' : ''" aria-label="{{ $b['open'] }}" title="{{ $b['title'] }}" data-asic-bell>
        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">notifications</span>
        <span x-show="unread > 0" x-text="unread > 99 ? '99+' : unread" class="absolute -right-0.5 -top-0.5 min-w-[18px] rounded-full bg-error px-1 text-center text-[10px] font-bold leading-[18px] text-white" style="display: none;"></span>
    </button>

    <div id="asic-bell-panel" x-show="open" x-transition.origin.top.right role="dialog" aria-label="{{ $b['title'] }}" style="display: none;"
        class="fixed inset-x-3 top-[4.25rem] z-50 flex max-h-[min(75vh,560px)] flex-col overflow-hidden rounded-2xl border border-surface-container-highest bg-surface-container-lowest shadow-2xl sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-[380px]">
        <div class="flex items-center justify-between gap-3 border-b border-surface-container-highest px-4 py-3">
            <p class="font-bold text-on-surface">{{ $b['title'] }}</p>
            <button type="button" x-show="unread > 0" @click="markAll()" :disabled="busy" class="text-xs font-semibold text-primary hover:underline disabled:opacity-50">{{ $b['mark_all'] }}</button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
            <div x-show="loading && !loaded" role="status" aria-label="{{ $b['loading'] }}" class="space-y-1 p-2">
                <template x-for="n in 3" :key="n">
                    <div class="flex animate-pulse gap-3 px-2 py-3"><span class="h-9 w-9 shrink-0 rounded-full bg-surface-container-high"></span><span class="flex-1 space-y-2"><span class="block h-3 w-2/3 rounded bg-surface-container-high"></span><span class="block h-3 w-full rounded bg-surface-container-high"></span></span></div>
                </template>
            </div>

            <div x-show="error" class="px-6 py-8 text-center" role="alert">
                <p class="text-sm text-on-surface-variant">{{ $b['failed'] }}</p>
                <button type="button" @click="load()" class="mt-3 rounded-full border border-surface-container-highest px-4 py-1.5 text-sm font-semibold text-primary hover:bg-surface-container-low">{{ $b['retry'] }}</button>
            </div>

            <div x-show="loaded && !error && items.length === 0" class="px-6 py-10 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined text-[28px]" aria-hidden="true">notifications_off</span></span>
                <p class="mt-3 font-bold text-on-surface">{{ $b['empty_title'] }}</p>
                <p class="mx-auto mt-1 max-w-[260px] text-sm text-on-surface-variant">{{ $staff ? $b['empty_body_staff'] : $b['empty_body'] }}</p>
            </div>

            <ul x-show="!error && items.length > 0" class="divide-y divide-surface-container-highest">
                <template x-for="item in items" :key="item.id">
                    <li>
                        <a :href="item.url || '#'" @click.prevent="openItem(item)" class="flex gap-3 px-4 py-3 transition hover:bg-surface-container-low" :class="item.read_at ? '' : 'bg-[#F3FAF5]'">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="tone(item.kind)"><span class="material-symbols-outlined text-[20px]" aria-hidden="true" x-text="icons[item.kind] || 'notifications'"></span></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm text-on-surface" :class="item.read_at ? 'font-medium' : 'font-bold'" x-text="item.title"></span>
                                <span class="mt-0.5 line-clamp-2 block text-xs leading-snug text-on-surface-variant" x-text="item.body"></span>
                                <span class="mt-1 block text-[11px] text-on-surface-variant" x-text="ago(item.created_at)"></span>
                            </span>
                            <span x-show="!item.read_at" class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-primary" :title="i18n.unread"><span class="sr-only" x-text="i18n.unread"></span></span>
                        </a>
                    </li>
                </template>
            </ul>
        </div>

        @if($allUrl)
            <a href="{{ $allUrl }}" class="block border-t border-surface-container-highest px-4 py-3 text-center text-sm font-semibold text-primary hover:bg-surface-container-low">{{ $b['see_all'] }}</a>
        @endif
    </div>
</div>
