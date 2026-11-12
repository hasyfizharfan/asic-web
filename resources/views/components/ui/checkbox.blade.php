@props([
    'label' => null,
    'description' => null,
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'checkbox_' . uniqid());
@endphp

<div class="flex items-start gap-3">
    <div class="flex h-5 items-center">
        <input type="checkbox"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ $value }}"
               {{ $checked ? 'checked' : '' }}
               {{ $disabled ? 'disabled' : '' }}
               {{ $attributes->merge([
                   'class' => 'h-4 w-4 rounded border-outline/40 text-primary focus:ring-primary/30 focus:ring-offset-0 disabled:opacity-50 cursor-pointer'
               ]) }} />
    </div>
    @if($label || $description)
        <div class="text-xs">
            @if($label)
                <label for="{{ $inputId }}" class="font-semibold text-on-surface cursor-pointer select-none">
                    {{ $label }}
                </label>
            @endif
            @if($description)
                <p class="text-[11px] text-on-surface-variant leading-normal">{{ $description }}</p>
            @endif
        </div>
    @endif
</div>

