@props([
    'label' => null,
    'description' => null,
    'name' => null,
    'id' => null,
    'checked' => false,
    'model' => null
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'toggle_' . uniqid());
@endphp

<div x-data="{ on: {{ $checked ? 'true' : 'false' }} }" class="flex items-center justify-between gap-4">
    @if($label || $description)
        <div class="flex-1">
            @if($label)
                <span class="block text-xs font-semibold text-on-surface">{{ $label }}</span>
            @endif
            @if($description)
                <span class="block text-[11px] text-on-surface-variant leading-tight">{{ $description }}</span>
            @endif
        </div>
    @endif

    <button type="button" 
            role="switch" 
            :aria-checked="on.toString()"
            @click="on = !on; $refs.checkbox.checked = on; $refs.checkbox.dispatchEvent(new Event('change'))"
            :class="on ? 'bg-primary' : 'bg-surface-container-highest'"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary/20">
        <span :class="on ? 'translate-x-5' : 'translate-x-0'"
              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
    </button>

    <input type="checkbox" 
           x-ref="checkbox"
           id="{{ $inputId }}"
           name="{{ $name }}"
           value="1"
           {{ $checked ? 'checked' : '' }}
           @if($model) x-model="{{ $model }}" @endif
           class="sr-only" />
</div>

