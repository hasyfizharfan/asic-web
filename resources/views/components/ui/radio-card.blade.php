@props([
    'name',
    'value',
    'title',
    'description' => null,
    'price' => null,
    'icon' => null,
    'checked' => false,
    'id' => null,
    'model' => null // Alpine x-model if used
])

@php
    $inputId = $id ?? ($name . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $value));
@endphp

<label for="{{ $inputId }}" 
       @if($model)
           :class="{{ $model }} === '{{ $value }}' ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest bg-surface-container-lowest hover:border-outline/50'"
       @endif
       class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all select-none group {{ $checked && !$model ? 'border-primary ring-2 ring-primary/20 bg-primary-fixed/10' : 'border-surface-container-highest bg-surface-container-lowest hover:border-outline/50' }}">
    
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3">
            @if($icon)
                <div class="w-10 h-10 rounded-lg bg-surface-container-low text-primary flex items-center justify-center shrink-0 group-hover:bg-primary-fixed/30 transition">
                    <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
                </div>
            @endif
            <div>
                <span class="block text-sm font-bold text-on-surface font-display">{{ $title }}</span>
                @if($description)
                    <span class="block text-xs text-on-surface-variant mt-0.5 leading-relaxed">{{ $description }}</span>
                @endif
            </div>
        </div>

        <input type="radio"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ $value }}"
               {{ $checked ? 'checked' : '' }}
               @if($model) x-model="{{ $model }}" @endif
               {{ $attributes->merge(['class' => 'h-4 w-4 text-primary border-outline/40 focus:ring-primary/30 focus:ring-offset-0 mt-1 cursor-pointer shrink-0']) }} />
    </div>

    @if($price || $slot->isNotEmpty())
        <div class="mt-3 pt-3 border-t border-surface-container-highest flex items-center justify-between">
            @if($price)
                <span class="text-xs font-bold text-primary font-mono">{{ $price }}</span>
            @endif
            <div>{{ $slot }}</div>
        </div>
    @endif
</label>

