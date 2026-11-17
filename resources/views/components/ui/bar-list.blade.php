@props([
    'items' => [], // list of ['label' => string, 'value' => int|float, 'display' => ?string]
    'empty' => '',
    'tone' => 'primary', // primary, accent
])

@php
    $items = collect($items);
    $max = max(1, (float) $items->max('value'));
    $bar = $tone === 'accent' ? 'bg-[#F5C518]' : 'bg-[#168348]';
@endphp

@if($items->isEmpty() || (float) $items->sum('value') === 0.0)
    <p class="text-sm text-on-surface-variant">{{ $empty }}</p>
@else
    <ul {{ $attributes->merge(['class' => 'space-y-3']) }}>
        @foreach($items as $item)
            <li>
                <div class="flex items-baseline justify-between gap-2 text-sm">
                    <span class="truncate font-semibold text-on-surface">{{ $item['label'] }}</span>
                    <span class="shrink-0 font-mono text-xs text-on-surface-variant">{{ $item['display'] ?? $item['value'] }}</span>
                </div>
                <div class="mt-1 h-2 rounded-full bg-[#E6F4EA]" role="presentation">
                    <div class="h-2 rounded-full {{ $bar }}" style="width: {{ round(((float) $item['value'] / $max) * 100, 1) }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
