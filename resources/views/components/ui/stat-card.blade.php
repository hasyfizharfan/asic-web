@props([
    'title',
    'value',
    'icon' => null,
    'change' => null, // e.g. '+12%'
    'trend' => 'up', // up, down, neutral
    'description' => null,
    'color' => 'primary' // primary, secondary, tertiary, error, info
])

@php
    $iconBg = match($color) {
        'secondary' => 'bg-secondary-container/40 text-secondary',
        'tertiary' => 'bg-tertiary-fixed/40 text-tertiary',
        'error' => 'bg-error-container/40 text-error',
        'info' => 'bg-blue-100 text-blue-800',
        default => 'bg-primary-fixed/40 text-primary',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest rounded-xl border border-surface-container-highest p-5 shadow-xs flex flex-col justify-between']) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ $title }}</p>
            <p class="mt-2 text-2xl font-bold text-on-surface font-display">{{ $value }}</p>
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
            </div>
        @endif
    </div>

    @if($change || $description)
        <div class="mt-4 pt-3 border-t border-surface-container-highest flex items-center gap-2 text-xs">
            @if($change)
                <span class="inline-flex items-center gap-0.5 font-bold {{ $trend === 'down' ? 'text-error' : 'text-primary' }}">
                    <span class="material-symbols-outlined text-[14px]">{{ $trend === 'down' ? 'trending_down' : 'trending_up' }}</span>
                    <span>{{ $change }}</span>
                </span>
            @endif
            @if($description)
                <span class="text-on-surface-variant truncate">{{ $description }}</span>
            @endif
        </div>
    @endif
</div>

