@props([
    'tabs' => [], // [['id' => 'tab1', 'label' => 'Tab 1', 'icon' => '...']]
    'default' => null,
    'variant' => 'pills' // pills, underline
])

@php
    $defaultTab = $default ?? ($tabs[0]['id'] ?? '');
@endphp

<div x-data="{ activeTab: '{{ $defaultTab }}' }" class="w-full">
    <!-- Tab Navigation -->
    <div class="{{ $variant === 'underline' ? 'border-b border-surface-container-highest' : 'p-1 bg-surface-container-low rounded-xl inline-flex flex-wrap gap-1' }} mb-6">
        @foreach($tabs as $tab)
            <button type="button"
                    @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}' 
                        ? '{{ $variant === 'underline' ? 'border-b-2 border-primary text-primary font-bold' : 'bg-surface-container-lowest text-primary font-bold shadow-xs' }}' 
                        : '{{ $variant === 'underline' ? 'border-b-2 border-transparent text-on-surface-variant hover:text-on-surface' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}'"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition-all cursor-pointer">
                @if(!empty($tab['icon']))
                    <span class="material-symbols-outlined text-[18px]">{{ $tab['icon'] }}</span>
                @endif
                <span>{{ $tab['label'] }}</span>
                @if(!empty($tab['badge']))
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-primary-fixed text-primary">{{ $tab['badge'] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    <!-- Tab Panels Container -->
    <div>
        {{ $slot }}
    </div>
</div>

