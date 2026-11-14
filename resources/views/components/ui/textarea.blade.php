@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'rows' => 4,
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'maxlength' => null
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'textarea_' . uniqid());
    $hasError = !empty($error);
    $initialVal = $name ? old($name, $value ?? '') : ($value ?? '');
    $initialVal = is_array($initialVal) ? '' : (string)$initialVal;
@endphp

<div class="w-full space-y-1.5" x-data="{ count: {{ strlen($initialVal) }}, max: {{ $maxlength ?? 'null' }} }">
    <div class="flex items-center justify-between">
        @if($label)
            <label for="{{ $inputId }}" class="block text-xs font-semibold text-on-surface">
                {{ $label }}
                @if($required)
                    <span class="text-error font-bold">*</span>
                @endif
            </label>
        @endif

        @if($maxlength)
            <span class="text-[11px] font-mono text-on-surface-variant">
                <span x-text="count"></span> / {{ $maxlength }}
            </span>
        @endif
    </div>

    <textarea id="{{ $inputId }}"
              name="{{ $name }}"
              rows="{{ $rows }}"
              placeholder="{{ $placeholder }}"
              {{ $maxlength ? "maxlength={$maxlength}" : '' }}
              @input="count = $el.value.length"
              {{ $required ? 'required' : '' }}
              {{ $disabled ? 'disabled' : '' }}
              {{ $attributes->merge([
                  'class' => 'block w-full rounded-lg border text-sm transition-all focus:outline-none focus:ring-2 p-3 ' .
                             ($hasError 
                                 ? 'border-error text-error placeholder-error/60 focus:border-error focus:ring-error/20 bg-error-container/10' 
                                 : 'border-outline/40 bg-surface-container-lowest text-on-surface placeholder:text-on-surface-variant/50 hover:border-outline focus:border-primary focus:ring-primary/20') .
                             ($disabled ? ' bg-surface-container-high cursor-not-allowed opacity-60' : '')
              ]) }}>{{ $initialVal }}</textarea>

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

