@props([
    'variant' => 'neutral', // success, warning, danger, info, neutral, amber
    'size' => 'md', // sm, md
    'dot' => false,
    'icon' => null
])

@php
    $baseStyles = 'inline-flex items-center font-semibold rounded-full tracking-wide select-none';

    $sizeStyles = match($size) {
        'sm' => 'px-2 py-0.5 text-[10px] gap-1',
        default => 'px-2.5 py-1 text-xs gap-1.5',
    };

    $variantStyles = match($variant) {
        'success' => 'bg-tertiary-fixed text-primary border border-tertiary-fixed-dim/50',
        'warning' => 'bg-secondary-container text-secondary border border-secondary-fixed-dim/50',
        'amber' => 'bg-amber-100 text-amber-900 border border-amber-300',
        'danger' => 'bg-error-container text-error border border-error/20',
        'info' => 'bg-blue-100 text-blue-900 border border-blue-200',
        default => 'bg-surface-container-high text-on-surface-variant border border-surface-container-highest',
    };

    $dotColors = match($variant) {
        'success' => 'bg-primary',
        'warning' => 'bg-secondary',
        'amber' => 'bg-amber-600',
        'danger' => 'bg-error',
        'info' => 'bg-blue-600',
        default => 'bg-outline',
    };

    $classes = "{$baseStyles} {$sizeStyles} {$variantStyles}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }}"></span>
    @endif
    @if($icon)
        <span class="material-symbols-outlined text-[14px]">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
</span>

