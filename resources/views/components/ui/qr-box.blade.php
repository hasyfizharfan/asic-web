@props([
    'code' => 'ASIC-2027-TBA',
    'title' => 'Scan QR Code',
    'subtitle' => null,
    'size' => 180,
    'src' => null
])

@php
    $qrSrc = $src ?? asset('images/asic/qr-sample.svg');
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-6 bg-surface-container-lowest rounded-2xl border border-surface-container-highest shadow-sm text-center max-w-xs mx-auto']) }}>
    <div class="p-3 bg-white rounded-xl border border-surface-container-highest shadow-inner mb-4">
        <img src="{{ $qrSrc }}" alt="QR Code {{ $code }}" class="w-44 h-44 object-contain">
    </div>

    <div class="space-y-1">
        <p class="text-xs font-bold font-mono tracking-widest text-primary uppercase bg-primary-fixed/30 px-3 py-1 rounded-full border border-primary-fixed">
            {{ $code }}
        </p>
        @if($title)
            <h4 class="text-sm font-bold text-on-surface pt-2 font-display">{{ $title }}</h4>
        @endif
        @if($subtitle)
            <p class="text-xs text-on-surface-variant">{{ $subtitle }}</p>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-surface-container-highest w-full flex items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

