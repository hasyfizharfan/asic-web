@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'iconLeft' => null,
    'iconRight' => null
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'input_' . uniqid());
    $hasError = !empty($error);
    $inputValue = $name ? old($name, $value) : $value;
    $inputValue = is_array($inputValue) ? '' : $inputValue;
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

        <input type="{{ $type }}"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ $inputValue }}"
               placeholder="{{ $placeholder }}"
               {{ $required ? 'required' : '' }}
               {{ $disabled ? 'disabled' : '' }}
               {{ $attributes->merge([
                   'class' => 'block w-full rounded-lg border text-sm transition-all focus:outline-none focus:ring-2 ' . 
                              ($iconLeft ? 'pl-9 ' : 'pl-3.5 ') . 
                              ($iconRight ? 'pr-9 ' : 'pr-3.5 ') . 
                              'py-2.5 ' .
                              ($hasError 
                                  ? 'border-error text-error placeholder-error/60 focus:border-error focus:ring-error/20 bg-error-container/10' 
                                  : 'border-outline/40 bg-surface-container-lowest text-on-surface placeholder:text-on-surface-variant/50 hover:border-outline focus:border-primary focus:ring-primary/20') .
                              ($disabled ? ' bg-surface-container-high cursor-not-allowed opacity-60' : '')
               ]) }} />

        @if($iconRight)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant">
                <span class="material-symbols-outlined text-[18px]">{{ $iconRight }}</span>
            </div>
        @endif
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

