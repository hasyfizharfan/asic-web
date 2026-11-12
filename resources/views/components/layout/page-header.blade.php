@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => []
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4']) }}>
    <div>
        @if(!empty($breadcrumbs))
            <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-2">
                @foreach($breadcrumbs as $crumb)
                    @if(!$loop->last && !empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="hover:text-primary transition">{{ $crumb['label'] }}</a>
                        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    @else
                        <span class="text-on-surface font-semibold">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <h1 class="text-2xl font-bold tracking-tight text-on-surface font-display">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 text-sm text-on-surface-variant">{{ $subtitle }}</p>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="flex items-center gap-3 shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>

