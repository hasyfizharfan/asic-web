<div x-data class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none" aria-live="assertive">
    <template x-for="item in $store.toast.items" :key="item.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-3 scale-95"
             :class="{
                 'border-tertiary-fixed bg-surface-container-lowest text-on-surface shadow-xl': item.type === 'success',
                 'border-error bg-surface-container-lowest text-error shadow-xl': item.type === 'error',
                 'border-secondary bg-surface-container-lowest text-on-surface shadow-xl': item.type === 'warning',
                 'border-outline bg-surface-container-lowest text-on-surface shadow-xl': item.type === 'info'
             }"
             class="pointer-events-auto p-4 rounded-xl border flex items-start gap-3 shadow-lg">
            
            <span class="material-symbols-outlined text-[20px] shrink-0"
                  :class="{
                      'text-primary': item.type === 'success',
                      'text-error': item.type === 'error',
                      'text-secondary': item.type === 'warning',
                      'text-on-surface-variant': item.type === 'info'
                  }"
                  x-text="item.type === 'success' ? 'check_circle' : (item.type === 'error' ? 'error' : 'info')"></span>

            <div class="flex-1 text-xs">
                <p class="font-bold font-display" x-text="item.type === 'success' ? 'Success' : (item.type === 'error' ? 'Error' : 'Notification')"></p>
                <p class="mt-0.5 text-on-surface-variant leading-relaxed" x-text="item.message"></p>
            </div>

            <button type="button" 
                    @click="$store.toast.remove(item.id)" 
                    class="p-1 text-on-surface-variant hover:text-on-surface rounded-md transition" 
                    aria-label="Close notification">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
    </template>
</div>

