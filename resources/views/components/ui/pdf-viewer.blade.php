{{-- Jendela pratinjau PDF bersama. Buka lewat window.asicPreview(url, judul). Di HP (tanpa penampil PDF bawaan) dibuka di tab baru. --}}
<div x-data="{
        open: false, url: '', title: '',
        show(detail) { this.url = detail.url; this.title = detail.title || ''; this.open = true; document.body.style.overflow = 'hidden'; },
        close() { this.open = false; this.url = ''; document.body.style.overflow = ''; },
        withInline(u) { return u + (u.includes('?') ? '&' : '?') + 'inline=1'; },
     }"
     @asic-preview.window="show($event.detail)" @keydown.escape.window="open && close()">
    <div x-show="open" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 p-3 sm:p-6" role="dialog" aria-modal="true" :aria-label="title" @click.self="close()">
        <div class="flex h-full max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="relative z-10 flex items-center justify-between gap-3 border-b border-surface-container-high px-4 py-3">
                <h2 class="min-w-0 truncate font-bold text-on-surface" x-text="title"></h2>
                <div class="flex shrink-0 items-center gap-2">
                    <a :href="withInline(url)" target="_blank" rel="noopener" class="icon-btn tip-below" aria-label="{{ __('asic-common.open_new_tab') }}"><span class="material-symbols-outlined" aria-hidden="true">open_in_new</span></a>
                    <a :href="url" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3 py-1.5 text-sm font-bold text-white"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>{{ __('asic-common.download') }}</a>
                    <button type="button" @click="close()" class="icon-btn tip-below tip-end" aria-label="{{ __('asic-common.close') }}"><span class="material-symbols-outlined" aria-hidden="true">close</span></button>
                </div>
            </div>
            <template x-if="open">
                <iframe :src="withInline(url)" class="h-full w-full flex-1 bg-surface-container-low" :title="title"></iframe>
            </template>
        </div>
    </div>
</div>
