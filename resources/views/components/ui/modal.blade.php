@props([
    'name', // Unique modal identifier
    'title' => null,
    'size' => 'md', // sm, md, lg, xl
    'icon' => null
])

@php
    $maxWidth = match($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        default => 'max-w-lg',
    };
@endphp

<div x-data="{ open: false }"
     x-show="open"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
     x-on:keydown.escape.window="open = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
         @click="open = false"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <!-- Dialog Panel -->
        <div x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.outside="open = false"
             class="relative transform overflow-hidden rounded-2xl bg-surface-container-lowest text-left shadow-2xl transition-all w-full {{ $maxWidth }} border border-surface-container-highest">
            
            <!-- Header -->
            @if($title || isset($header))
                <div class="px-6 py-4 border-b border-surface-container-highest flex items-center justify-between">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <div class="flex items-center gap-3">
                            @if($icon)
                                <div class="w-9 h-9 rounded-lg bg-primary-fixed/40 text-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                                </div>
                            @endif
                            <h3 class="text-base font-bold text-on-surface font-display">{{ $title }}</h3>
                        </div>
                    @endif

                    <button @click="open = false" type="button" class="p-1 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition" aria-label="Close modal">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
            @endif

            <!-- Body -->
            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if(isset($footer))
                <div class="px-6 py-3.5 bg-surface-container-low border-t border-surface-container-highest flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

