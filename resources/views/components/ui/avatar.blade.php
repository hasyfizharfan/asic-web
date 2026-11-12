@props([
    'name' => null,
    'src' => null,
    'size' => 'md', // xs, sm, md, lg, xl
    'status' => null // online, offline, busy
])

@php
    $sizeStyles = match($size) {
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-xl',
        default => 'w-10 h-10 text-sm',
    };

    $initials = '';
    if ($name) {
        $parts = explode(' ', trim($name));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }

    $avatarSrc = $src ?? asset('images/asic/avatar.svg');
@endphp

<div class="relative inline-block shrink-0">
    @if($src)
        <img src="{{ $avatarSrc }}" alt="{{ $name ?? 'Avatar' }}" class="rounded-full object-cover border border-surface-container-highest {{ $sizeStyles }}">
    @else
        <div class="rounded-full bg-primary-fixed text-primary font-bold flex items-center justify-center border border-primary-fixed-dim {{ $sizeStyles }}">
            <span>{{ $initials ?: '?' }}</span>
        </div>
    @endif

    @if($status)
        @php
            $statusBg = match($status) {
                'online' => 'bg-emerald-500',
                'busy' => 'bg-error',
                default => 'bg-outline',
            };
        @endphp
        <span class="absolute bottom-0 right-0 block w-2.5 h-2.5 rounded-full {{ $statusBg }} ring-2 ring-white"></span>
    @endif
</div>

