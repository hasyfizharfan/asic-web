@props(['theme' => 'light'])

@php
    $currentLocale = app()->getLocale();
    $isDark = $theme === 'dark';
@endphp

<div class="flex items-center rounded-full p-0.5 text-xs font-semibold select-none {{ $isDark ? 'bg-white/10 border border-white/20' : 'bg-surface-container-low border border-surface-container-highest' }}">
    <a href="{{ route('fe.lang.switch', 'id') }}" 
       class="rounded-full px-2.5 py-0.5 transition {{ $currentLocale === 'id' ? ($isDark ? 'bg-gold text-deep font-bold shadow-xs' : 'bg-primary-container text-white font-bold shadow-xs') : ($isDark ? 'text-white/70 hover:text-white' : 'text-on-surface-variant hover:text-on-surface') }}"
       title="Bahasa Indonesia">
        ID
    </a>
    <a href="{{ route('fe.lang.switch', 'en') }}" 
       class="rounded-full px-2.5 py-0.5 transition {{ $currentLocale === 'en' ? ($isDark ? 'bg-gold text-deep font-bold shadow-xs' : 'bg-primary-container text-white font-bold shadow-xs') : ($isDark ? 'text-white/70 hover:text-white' : 'text-on-surface-variant hover:text-on-surface') }}"
       title="English">
        EN
    </a>
</div>
