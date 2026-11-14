@props([
    'icon' => 'inbox',
    'title' => 'No items found',
    'description' => null
])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4 rounded-xl border border-dashed border-surface-container-highest bg-surface-container-lowest/50 flex flex-col items-center justify-center']) }}>
    <div class="w-14 h-14 rounded-full bg-surface-container-low text-on-surface-variant flex items-center justify-center mb-3">
        <span class="material-symbols-outlined text-[28px]">{{ $icon }}</span>
    </div>
    
    <h3 class="text-sm font-bold text-on-surface font-display">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-xs text-on-surface-variant max-w-sm">{{ $description }}</p>
    @endif

    @if($slot->isNotEmpty())
        <div class="mt-4 flex items-center justify-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>

