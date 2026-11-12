@props([
    'role' => 'participant',
    'roleName' => 'Participant',
    'userName' => null
])

@php
    $defaultNames = [
        'participant' => 'Dr. Ahmad Fauzi',
        'reviewer' => 'Dr. Sarah Jenkins',
        'secretariat' => 'Rina S., M.Si (Secretariat)',
        'treasurer' => 'Budi Santoso, S.E. (Treasurer)',
        'admin' => 'Administrator ASIC',
    ];
    $displayName = $userName ?? $defaultNames[$role] ?? 'User';

    $logoutRoute = match($role) {
        'reviewer' => route('fe.reviewer.logout'),
        'secretariat' => route('fe.secretariat.logout'),
        'treasurer' => route('fe.treasurer.logout'),
        'admin' => route('fe.admin.logout'),
        default => route('fe.participant.logout'),
    };
@endphp

<header class="sticky top-0 z-20 h-16 bg-surface-container-lowest border-b border-surface-container-highest px-4 sm:px-6 flex items-center justify-between">
    <!-- Left Section: Mobile Menu Trigger & Conference Context -->
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = true" 
                type="button" 
                class="lg:hidden p-2 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low rounded-lg transition"
                aria-label="Open sidebar">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>

        <div class="hidden sm:flex items-center gap-2 text-xs text-on-surface-variant">
            <span class="font-bold text-primary">ASIC 2027</span>
            <span>/</span>
            <span class="truncate max-w-[200px] md:max-w-xs">Agrifood System International Conference</span>
        </div>
    </div>

    <!-- Right Section: Role chip with switcher, Lang switcher, User Profile -->
    <div class="flex items-center gap-2 sm:gap-4">
        <!-- Interactive Role Switcher Chip -->
        <div x-data="{ roleMenuOpen: false }" @click.outside="roleMenuOpen = false" class="relative">
            <button @click="roleMenuOpen = !roleMenuOpen" 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary-fixed/40 border border-primary-fixed text-primary text-[11px] font-semibold tracking-wide hover:bg-primary-fixed/70 transition"
                    title="Switch Portal">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                <span class="uppercase font-bold">{{ $roleName }}</span>
                <span class="material-symbols-outlined text-[14px]" :class="roleMenuOpen ? 'rotate-180' : ''">expand_more</span>
            </button>

            <!-- Role Selector Dropdown -->
            <div x-show="roleMenuOpen" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-surface-container-lowest p-2 shadow-xl ring-1 ring-black/5 border border-surface-container-highest focus:outline-none z-50"
                 style="display: none;">
                
                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                    {{ __('asic-common.switch_portal') ?? 'Switch Portal / Role' }}
                </div>

                <a href="{{ route('fe.participant.dashboard') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs transition {{ $role === 'participant' ? 'bg-primary-fixed/40 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                        <span>Participant (Peserta)</span>
                    </div>
                    @if($role === 'participant')
                        <span class="material-symbols-outlined text-[16px] text-primary">check</span>
                    @endif
                </a>

                <a href="{{ route('fe.reviewer.queue') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs transition {{ $role === 'reviewer' ? 'bg-primary-fixed/40 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">rate_review</span>
                        <span>Reviewer (Reviewer Abstrak)</span>
                    </div>
                    @if($role === 'reviewer')
                        <span class="material-symbols-outlined text-[16px] text-primary">check</span>
                    @endif
                </a>

                <a href="{{ route('fe.secretariat.abstracts.index') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs transition {{ $role === 'secretariat' ? 'bg-primary-fixed/40 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">badge</span>
                        <span>Secretariat (Sekretariat)</span>
                    </div>
                    @if($role === 'secretariat')
                        <span class="material-symbols-outlined text-[16px] text-primary">check</span>
                    @endif
                </a>

                <a href="{{ route('fe.treasurer.tariffs') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs transition {{ $role === 'treasurer' ? 'bg-primary-fixed/40 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">payments</span>
                        <span>Treasurer (Bendahara)</span>
                    </div>
                    @if($role === 'treasurer')
                        <span class="material-symbols-outlined text-[16px] text-primary">check</span>
                    @endif
                </a>

                <a href="{{ route('fe.admin.overview') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs transition {{ $role === 'admin' ? 'bg-primary-fixed/40 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                        <span>Admin (Super Admin)</span>
                    </div>
                    @if($role === 'admin')
                        <span class="material-symbols-outlined text-[16px] text-primary">check</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Language Switcher -->
        <x-layout.lang-switcher theme="light" />

        <!-- User Profile Dropdown -->
        <div x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false" class="relative">
            <button @click="userMenuOpen = !userMenuOpen" 
                    type="button" 
                    class="flex items-center gap-2 p-1 rounded-full hover:bg-surface-container-low transition focus:outline-none focus:ring-2 focus:ring-primary/20"
                    aria-expanded="false" 
                    aria-haspopup="true">
                <img src="{{ asset('images/asic/avatar.svg') }}" alt="{{ $displayName }}" class="w-8 h-8 rounded-full border border-surface-container-highest">
                <span class="hidden md:block text-xs font-semibold text-on-surface text-left leading-tight max-w-[120px] truncate">
                    {{ $displayName }}
                </span>
                <span class="material-symbols-outlined text-[16px] text-on-surface-variant hidden md:block" :class="userMenuOpen ? 'rotate-180' : ''">expand_more</span>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="userMenuOpen" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-surface-container-lowest p-2 shadow-xl ring-1 ring-black/5 border border-surface-container-highest focus:outline-none z-50"
                 style="display: none;">
                
                <div class="px-3 py-2 border-b border-surface-container-highest mb-1">
                    <p class="text-xs font-bold text-on-surface truncate">{{ $displayName }}</p>
                    <p class="text-[11px] text-on-surface-variant capitalize">{{ $roleName }}</p>
                </div>

                @if($role === 'participant')
                    <a href="{{ route('fe.participant.profile') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">account_circle</span>
                        <span>{{ __('asic-nav.profile') ?? 'My Profile' }}</span>
                    </a>
                    <a href="{{ route('fe.participant.notifications') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">notifications</span>
                        <span>{{ __('asic-nav.notifications') ?? 'Notifications' }}</span>
                    </a>
                    <div class="my-1 border-t border-surface-container-highest"></div>
                @elseif($role === 'reviewer')
                    <a href="{{ route('fe.reviewer.profile') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">account_circle</span>
                        <span>{{ __('asic-reviewer.profile_title') ?? 'Profil Reviewer' }}</span>
                    </a>
                    <div class="my-1 border-t border-surface-container-highest"></div>
                @endif

                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                    {{ __('asic-common.quick_switch') ?? 'Quick Switch Portal' }}
                </div>
                <a href="{{ route('fe.participant.dashboard') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                    <span class="material-symbols-outlined text-[16px] text-primary">person</span>
                    <span>Participant Portal</span>
                </a>
                <a href="{{ route('fe.reviewer.queue') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                    <span class="material-symbols-outlined text-[16px] text-primary">rate_review</span>
                    <span>Reviewer Portal</span>
                </a>
                <a href="{{ route('fe.secretariat.abstracts.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                    <span class="material-symbols-outlined text-[16px] text-primary">badge</span>
                    <span>Secretariat Portal</span>
                </a>
                <a href="{{ route('fe.treasurer.tariffs') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                    <span class="material-symbols-outlined text-[16px] text-primary">payments</span>
                    <span>Treasurer Portal</span>
                </a>
                <a href="{{ route('fe.admin.overview') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-on-surface hover:bg-surface-container-low transition">
                    <span class="material-symbols-outlined text-[16px] text-primary">admin_panel_settings</span>
                    <span>Admin Portal</span>
                </a>

                <div class="my-1 border-t border-surface-container-highest"></div>

                <a href="{{ $logoutRoute }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-error hover:bg-error-container/30 transition">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>{{ __('asic-nav.logout') ?? 'Sign Out' }}</span>
                </a>
            </div>
        </div>
    </div>
</header>

