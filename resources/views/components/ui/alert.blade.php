@props([
    'variant' => 'info', // info, success, warning, error, neutral
    'title' => null,
    'icon' => null,
    'dismissible' => false
])

@php
    $variantStyles = match($variant) {
        'success' => 'bg-tertiary-fixed/30 border-tertiary-fixed text-primary',
        'warning' => 'bg-secondary-container/30 border-secondary-container text-secondary',
        'error' => 'bg-error-container/40 border-error/30 text-error',
        'neutral' => 'bg-surface-container-low border-surface-container-highest text-on-surface',
        default => 'bg-blue-50 border-blue-200 text-blue-900',
    };

    $defaultIcon = match($variant) {
        'success' => 'check_circle',
        'warning' => 'warning',
        'error' => 'error',
        'neutral' => 'info',
        default => 'info',
    };

    $iconName = $icon ?? $defaultIcon;
@endphp

<div x-data="{ dismissed: false }" 
     x-show="!dismissed" 
     {{ $attributes->merge(['class' => "p-4 rounded-xl border flex items-start gap-3 text-xs leading-relaxed transition-all {$variantStyles}"]) }}>
    
    <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">{{ $iconName }}</span>

    <div class="flex-1">
        @if($title)
            <h4 class="font-bold mb-0.5 text-sm font-display">{{ $title }}</h4>
        @endif
        <div class="space-y-1">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button type="button" @click="dismissed = true" class="p-1 rounded-md opacity-60 hover:opacity-100 transition" aria-label="Dismiss alert">
            <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
    @endif
</div>

