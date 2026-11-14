@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'totalItems' => null,
    'from' => 1,
    'to' => 10
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 bg-surface-container-low border-t border-surface-container-highest text-xs text-on-surface-variant']) }}>
    @if($totalItems)
        <div>
            Showing <span class="font-bold text-on-surface">{{ $from }}</span> to <span class="font-bold text-on-surface">{{ $to }}</span> of <span class="font-bold text-on-surface">{{ $totalItems }}</span> results
        </div>
    @else
        <div>Page {{ $currentPage }} of {{ $totalPages }}</div>
    @endif

    <div class="inline-flex items-center gap-1">
        <button type="button" 
                {{ $currentPage <= 1 ? 'disabled' : '' }} 
                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-surface-container-highest bg-surface-container-lowest font-medium hover:bg-surface-container disabled:opacity-40 disabled:cursor-not-allowed transition">
            <span class="material-symbols-outlined text-[16px]">chevron_left</span>
            <span class="hidden sm:inline">Previous</span>
        </button>

        @for($i = 1; $i <= min($totalPages, 5); $i++)
            <button type="button" 
                    class="w-7 h-7 rounded-lg text-xs font-semibold flex items-center justify-center transition {{ $currentPage === $i ? 'bg-primary text-white font-bold' : 'bg-surface-container-lowest border border-surface-container-highest hover:bg-surface-container text-on-surface' }}">
                {{ $i }}
            </button>
        @endfor

        <button type="button" 
                {{ $currentPage >= $totalPages ? 'disabled' : '' }} 
                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-surface-container-highest bg-surface-container-lowest font-medium hover:bg-surface-container disabled:opacity-40 disabled:cursor-not-allowed transition">
            <span class="hidden sm:inline">Next</span>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </button>
    </div>
</div>

