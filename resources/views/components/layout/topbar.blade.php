@props([
    'role' => 'participant',
    'roleName' => 'Participant',
    'userName' => null
])

@php
    $user = auth()->user();
    $displayName = $userName ?? $user?->participant?->full_name ?? $user?->email ?? '';
    $profileRoute = match ($role) {
        'participant' => 'portal.participant.profile',
        'reviewer' => 'portal.reviewer.profile',
        default => null,
    };
    $bellUrl = $role === 'participant' ? route('portal.participant.notifications') : null;
@endphp

<header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface-container-lowest px-4 sm:px-6">
    <div class="flex min-w-0 items-center gap-3">
        <button @click="sidebarOpen = true" type="button" class="rounded-lg p-2 text-on-surface hover:bg-surface-container-low lg:hidden" aria-label="{{ __('asic-common.open_menu') }}">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
        <div class="hidden min-w-0 items-center gap-2 text-sm text-on-surface-variant sm:flex">
            <span class="font-bold text-primary">ASIC 2027</span>
            <span aria-hidden="true">/</span>
            <span class="truncate">{{ $roleName }}</span>
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        {{-- KF-30: lonceng notifikasi (jumlah belum dibaca milik pengguna sendiri) --}}
        <div x-data="{ unread: 0 }" x-init="window.asicApi.get('/api/me/notifications/unread-count').then(r => { if (r.ok) unread = Number(r.data?.data?.unread_count || 0); })">
            <{{ $bellUrl ? 'a href='.e($bellUrl) : 'span' }} class="relative inline-flex rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface" aria-label="{{ __('asic-notifications.page.bell_label') }}" title="{{ __('asic-notifications.page.bell_label') }}" data-asic-bell>
                <span class="material-symbols-outlined text-[24px]">notifications</span>
                <span x-show="unread > 0" x-text="unread > 99 ? '99+' : unread" class="absolute -right-0.5 -top-0.5 min-w-[18px] rounded-full bg-error px-1 text-center text-[10px] font-bold leading-[18px] text-white" style="display: none;"></span>
            </{{ $bellUrl ? 'a' : 'span' }}>
        </div>

        <x-layout.lang-switcher theme="light" />

        <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
            <button @click="open = !open" type="button" :aria-expanded="open.toString()" aria-haspopup="menu"
                class="flex items-center gap-2 rounded-full p-1 pr-2 hover:bg-surface-container-low">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#E6F4EA] text-primary"><span class="material-symbols-outlined text-[22px]">person</span></span>
                <span class="hidden max-w-[180px] text-left leading-tight md:block">
                    <span class="block truncate text-sm font-semibold text-on-surface">{{ $displayName }}</span>
                    <span class="block truncate text-xs text-on-surface-variant">{{ $roleName }}</span>
                </span>
                <span class="material-symbols-outlined hidden text-[18px] text-on-surface-variant md:block" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>

            <div x-show="open" x-transition role="menu" style="display: none;"
                class="absolute right-0 z-50 mt-2 w-60 origin-top-right rounded-xl border border-surface-container-highest bg-surface-container-lowest p-2 shadow-xl">
                <div class="mb-1 border-b border-surface-container-highest px-3 py-2">
                    <p class="truncate text-sm font-bold text-on-surface">{{ $displayName }}</p>
                    @if($user?->email && $user->email !== $displayName)<p class="truncate text-xs text-on-surface-variant">{{ $user->email }}</p>@endif
                </div>
                @if($profileRoute && \Illuminate\Support\Facades\Route::has($profileRoute))
                    <a href="{{ route($profileRoute) }}" role="menuitem" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-[20px] text-on-surface-variant">account_circle</span>{{ __('asic-nav.profile') }}
                    </a>
                @endif
                @if($bellUrl)
                    <a href="{{ $bellUrl }}" role="menuitem" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-on-surface hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-[20px] text-on-surface-variant">notifications</span>{{ __('asic-nav.notifications') }}
                    </a>
                @endif
                <form data-asic-logout action="{{ route('api.logout') }}" method="POST" class="mt-1 border-t border-surface-container-highest pt-1">
                    @csrf
                    <button type="submit" role="menuitem" class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-error hover:bg-error-container/40">
                        <span class="material-symbols-outlined text-[20px]">logout</span>{{ __('asic-nav.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
