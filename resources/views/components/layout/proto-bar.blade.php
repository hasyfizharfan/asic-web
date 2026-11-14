@php
    $currentRole = match(true) {
        request()->is('_fe/participant*') || request()->is('participant*') => 'participant',
        request()->is('_fe/secretariat*') || request()->is('secretariat*') => 'secretariat',
        request()->is('_fe/treasurer*') || request()->is('treasurer*') => 'treasurer',
        request()->is('_fe/admin*') || request()->is('admin*') => 'admin',
        default => 'website',
    };
    $locale = app()->getLocale();
@endphp

<aside id="proto-review-bar" class="sticky top-0 z-50 h-11 bg-[#0F3D24] text-white flex items-center justify-between px-3 sm:px-6 text-xs border-b border-white/10 shadow-md select-none font-sans">
    <!-- Left: Branding -->
    <div class="flex items-center gap-2">
        <a href="{{ route('fe.home') }}" class="font-bold tracking-tight text-white flex items-center gap-1.5 hover:opacity-90 transition">
            <span class="w-2.5 h-2.5 rounded-full bg-[#F5C518] inline-block animate-pulse"></span>
            <span class="font-display font-bold">ASIC 2027</span>
        </a>
        <span class="hidden md:inline-block text-[11px] text-white/60 font-mono">| Review Mode</span>
    </div>

    <!-- Center: Role Switcher Buttons (Matches Prototype #bar) -->
    <nav class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto py-1 scrollbar-none" aria-label="Portal Role Navigation">
        <a href="{{ route('fe.home') }}" 
           class="px-2.5 py-1 rounded-md text-xs transition whitespace-nowrap {{ $currentRole === 'website' ? 'bg-[#F5C518] text-[#111827] font-bold shadow-xs' : 'text-white/80 hover:text-white hover:bg-white/10 border border-white/20' }}">
            {{ $locale === 'id' ? 'Situs Web' : 'Website' }}
        </a>
        <a href="{{ session('logged_in_participant') ? route('fe.participant.dashboard') : route('fe.participant.login') }}" 
           class="px-2.5 py-1 rounded-md text-xs transition whitespace-nowrap {{ $currentRole === 'participant' ? 'bg-[#F5C518] text-[#111827] font-bold shadow-xs' : 'text-white/80 hover:text-white hover:bg-white/10 border border-white/20' }}">
            {{ $locale === 'id' ? 'Peserta' : 'Participant' }}
        </a>
        <a href="{{ session('logged_in_secretariat') ? route('fe.secretariat.abstracts.index') : route('fe.secretariat.login') }}" 
           class="px-2.5 py-1 rounded-md text-xs transition whitespace-nowrap {{ $currentRole === 'secretariat' ? 'bg-[#F5C518] text-[#111827] font-bold shadow-xs' : 'text-white/80 hover:text-white hover:bg-white/10 border border-white/20' }}">
            {{ $locale === 'id' ? 'Sekretariat' : 'Secretariat' }}
        </a>
        <a href="{{ session('logged_in_treasurer') ? route('fe.treasurer.tariffs') : route('fe.treasurer.login') }}" 
           class="px-2.5 py-1 rounded-md text-xs transition whitespace-nowrap {{ $currentRole === 'treasurer' ? 'bg-[#F5C518] text-[#111827] font-bold shadow-xs' : 'text-white/80 hover:text-white hover:bg-white/10 border border-white/20' }}">
            {{ $locale === 'id' ? 'Bendahara' : 'Treasurer' }}
        </a>
        <a href="{{ session('logged_in_admin') ? route('fe.admin.overview') : route('fe.admin.login') }}" 
           class="px-2.5 py-1 rounded-md text-xs transition whitespace-nowrap {{ $currentRole === 'admin' ? 'bg-[#F5C518] text-[#111827] font-bold shadow-xs' : 'text-white/80 hover:text-white hover:bg-white/10 border border-white/20' }}">
            Admin
        </a>
    </nav>

    <!-- Right: Language Toggle Segmented Pill -->
    <div class="flex items-center gap-1 bg-black/20 p-0.5 rounded-lg border border-white/15">
        <a href="{{ route('fe.lang.switch', 'id') }}" 
           class="px-2 py-0.5 rounded text-[11px] font-bold transition {{ $locale === 'id' ? 'bg-[#F5C518] text-[#111827] shadow-xs' : 'text-white/70 hover:text-white' }}"
           title="Bahasa Indonesia">
            ID
        </a>
        <a href="{{ route('fe.lang.switch', 'en') }}" 
           class="px-2 py-0.5 rounded text-[11px] font-bold transition {{ $locale === 'en' ? 'bg-[#F5C518] text-[#111827] shadow-xs' : 'text-white/70 hover:text-white' }}"
           title="English">
            EN
        </a>
    </div>
</aside>

