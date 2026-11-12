@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'padding' => 'p-6',
    'headerClass' => '',
    'footerClass' => ''
])

<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest rounded-xl border border-surface-container-highest shadow-sm overflow-hidden']) }}>
    @if($title || isset($header) || isset($actions))
        <div class="px-6 py-4 border-b border-surface-container-highest flex items-center justify-between gap-4 {{ $headerClass }}">
            @if(isset($header))
                {{ $header }}
            @else
                <div class="flex items-center gap-3">
                    @if($icon)
                        <div class="w-8 h-8 rounded-lg bg-primary-fixed/40 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-base font-bold text-on-surface font-display">{{ $title }}</h3>
                        @if($subtitle)
                            <p class="text-xs text-on-surface-variant">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if(isset($actions))
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-3 bg-surface-container-low border-t border-surface-container-highest flex items-center justify-between {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endif
</div>

