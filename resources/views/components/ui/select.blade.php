@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => [], // ['val' => 'Label'] or slot
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'iconLeft' => null
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'select_' . uniqid());
    $hasError = !empty($error);
    $selectedVal = $name ? old($name, $value) : $value;
    $selectedVal = is_array($selectedVal) ? '' : $selectedVal;
@endphp

<div class="w-full space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold text-on-surface">
            {{ $label }}
            @if($required)
                <span class="text-error font-bold">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-lg shadow-sm">
        @if($iconLeft)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-on-surface-variant">
                <span class="material-symbols-outlined text-[18px]">{{ $iconLeft }}</span>
            </div>
        @endif

        <select id="{{ $inputId }}"
                name="{{ $name }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $attributes->merge([
                    'class' => 'block w-full rounded-lg border text-sm transition-all appearance-none focus:outline-none focus:ring-2 ' . 
                               ($iconLeft ? 'pl-9 ' : 'pl-3.5 ') . 
                               'pr-10 py-2.5 ' .
                               ($hasError 
                                   ? 'border-error text-error focus:border-error focus:ring-error/20 bg-error-container/10' 
                                   : 'border-outline/40 bg-surface-container-lowest text-on-surface hover:border-outline focus:border-primary focus:ring-primary/20') .
                               ($disabled ? ' bg-surface-container-high cursor-not-allowed opacity-60' : '')
                ]) }}>
            @if($placeholder)
                <option value="" disabled {{ empty($selectedVal) ? 'selected' : '' }}>{{ $placeholder }}</option>
            @endif

            @if(!empty($options))
                @foreach($options as $val => $optLabel)
                    <option value="{{ $val }}" {{ (string)$selectedVal === (string)$val ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant">
            <span class="material-symbols-outlined text-[18px]">unfold_more</span>
        </div>
    </div>

    @if($hint && !$hasError)
        <p class="text-[11px] text-on-surface-variant leading-tight">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="text-[11px] text-error flex items-center gap-1 font-medium">
            <span class="material-symbols-outlined text-[14px]">error</span>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>

