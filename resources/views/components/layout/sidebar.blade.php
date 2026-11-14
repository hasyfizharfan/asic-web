@props([
    'role' => 'participant',
    'roleName' => 'Participant',
    'nav' => []
])

@php
    $portalTitles = [
        'participant' => __('asic-nav.portal_participant') ?? 'Participant Portal',
        'reviewer' => __('asic-nav.portal_reviewer') ?? 'Reviewer Portal',
        'secretariat' => __('asic-nav.portal_secretariat') ?? 'Secretariat Portal',
        'treasurer' => __('asic-nav.portal_treasurer') ?? 'Treasurer Portal',
        'admin' => __('asic-nav.portal_admin') ?? 'Admin Portal',
    ];
    $portalSubtitle = $portalTitles[$role] ?? ucfirst($role) . ' Portal';
    
    $logoutRoute = match($role) {
        'reviewer' => route('fe.reviewer.logout'),
        'secretariat' => route('fe.secretariat.logout'),
        'treasurer' => route('fe.treasurer.logout'),
        'admin' => route('fe.admin.logout'),
        default => route('fe.participant.logout'),
    };
@endphp

<!-- Mobile Backdrop -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false" 
     class="fixed inset-0 z-40 bg-black/50 lg:hidden"
     style="display: none;"></div>

<!-- Sidebar Container -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-50 w-60 bg-primary text-white flex flex-col transition-transform duration-200 ease-in-out shadow-xl lg:shadow-none scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
    
    <!-- Header Brand -->
    <div class="h-16 px-5 flex items-center justify-between border-b border-primary-container/60 shrink-0">
        <a href="{{ route('fe.home') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('images/asic/logo.svg') }}" alt="Logo" class="w-8 h-8 rounded-md bg-white p-0.5 shadow-sm group-hover:scale-105 transition-transform">
            <div>
                <span class="block text-base font-bold tracking-tight text-white font-display">ASIC 2027</span>
                <span class="block text-[11px] text-tertiary-fixed font-medium truncate max-w-[140px]">{{ $portalSubtitle }}</span>
            </div>
        </a>

        <!-- Mobile close button -->
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 text-secondary-fixed-dim hover:text-white rounded-md hover:bg-primary-container transition" aria-label="Close sidebar">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>
    </div>

    <!-- Navigation List (Scrollbar hidden across all browsers) -->
    <nav class="flex-1 overflow-y-auto scrollbar-none py-4 px-2 space-y-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
        @foreach($nav as $item)
            @php
                $isActive = !empty($item['route']) && (request()->routeIs($item['route']) || (isset($item['active_pattern']) && request()->is($item['active_pattern'])));
                $url = !empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
            @endphp
            <a href="{{ $url }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-r-lg text-xs tracking-wide transition group {{ $isActive ? 'bg-primary-container text-white border-l-4 border-tertiary-fixed font-semibold shadow-inner' : 'text-secondary-fixed-dim hover:text-white hover:bg-primary-container/50' }}"
               {!! $isActive ? 'aria-current="page"' : '' !!}>
                <span class="material-symbols-outlined text-[20px] shrink-0 transition-transform group-hover:scale-110 {{ $isActive ? 'text-tertiary-fixed' : 'text-secondary-fixed-dim group-hover:text-white' }}">{{ $item['icon'] ?? 'circle' }}</span>
                <span class="flex-1 truncate">{{ $item['label'] ?? '' }}</span>
                @if(!empty($item['badge']))
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $item['badge_class'] ?? 'bg-tertiary-fixed text-primary' }}">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>

    <!-- Sidebar Footer / Logout -->
    <div class="p-3 border-t border-primary-container/60">
        <a href="{{ $logoutRoute }}" class="flex items-center gap-3 px-3 py-2 text-xs text-secondary-fixed-dim hover:text-white hover:bg-primary-container/40 rounded-lg transition">
            <span class="material-symbols-outlined text-[18px]">logout</span>
            <span>{{ __('asic-nav.logout') ?? 'Sign Out' }}</span>
        </a>
    </div>
</aside>

