@props([
    'variant' => 'primary', // primary, secondary, outline, ghost, danger, gold
    'size' => 'md', // sm, md, lg
    'iconLeft' => null,
    'iconRight' => null,
    'href' => null,
    'type' => 'button',
    'disabled' => false
])

@php
    $baseStyles = 'inline-flex items-center justify-center font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer select-none';

    $sizeStyles = match($size) {
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'lg' => 'px-6 py-3 text-base gap-2.5',
        default => 'px-4 py-2 text-sm gap-2',
    };

    $variantStyles = match($variant) {
        'secondary' => 'bg-secondary text-white hover:brightness-110 active:brightness-95 focus:ring-secondary/40 shadow-sm',
        'outline' => 'border border-outline text-on-surface hover:bg-surface-container active:bg-surface-container-high focus:ring-primary/30',
        'ghost' => 'text-on-surface hover:bg-surface-container active:bg-surface-container-high focus:ring-primary/20',
        'danger' => 'bg-error text-white hover:brightness-110 active:brightness-95 focus:ring-error/40 shadow-sm',
        'gold' => 'bg-secondary-container text-secondary font-bold hover:brightness-105 active:scale-95 focus:ring-secondary/40 shadow-sm',
        'success' => 'bg-tertiary text-white hover:brightness-110 focus:ring-tertiary/40 shadow-sm',
        default => 'bg-primary text-white hover:bg-primary-container active:scale-[0.99] focus:ring-primary/40 shadow-sm',
    };

    $classes = "{$baseStyles} {$sizeStyles} {$variantStyles}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconLeft)
            <span class="material-symbols-outlined text-[18px] shrink-0">{{ $iconLeft }}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight)
            <span class="material-symbols-outlined text-[18px] shrink-0">{{ $iconRight }}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconLeft)
            <span class="material-symbols-outlined text-[18px] shrink-0">{{ $iconLeft }}</span>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight)
            <span class="material-symbols-outlined text-[18px] shrink-0">{{ $iconRight }}</span>
        @endif
    </button>
@endif

