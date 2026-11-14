@props([
    'steps' => [] // [['title' => '...', 'date' => '...', 'status' => 'completed|current|upcoming', 'description' => '...']]
])

<div {{ $attributes->merge(['class' => 'flow-root']) }}>
    <ul role="list" class="-mb-8">
        @foreach($steps as $step)
            @php
                $status = $step['status'] ?? 'upcoming';
                $isLast = $loop->last;
            @endphp
            <li>
                <div class="relative pb-8">
                    @if(!$isLast)
                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 {{ $status === 'completed' ? 'bg-primary' : 'bg-surface-container-highest' }}" aria-hidden="true"></span>
                    @endif

                    <div class="relative flex items-start space-x-3">
                        <div>
                            @if($status === 'completed')
                                <span class="h-8 w-8 rounded-full bg-primary flex items-center justify-center text-white ring-4 ring-surface-container-lowest">
                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                </span>
                            @elseif($status === 'current')
                                <span class="h-8 w-8 rounded-full bg-primary-fixed border-2 border-primary flex items-center justify-center text-primary ring-4 ring-surface-container-lowest animate-pulse">
                                    <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                                </span>
                            @else
                                <span class="h-8 w-8 rounded-full bg-surface-container-high border border-outline/40 flex items-center justify-center text-on-surface-variant ring-4 ring-surface-container-lowest">
                                    <span class="w-2 h-2 rounded-full bg-outline"></span>
                                </span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                            <div>
                                <p class="text-xs font-bold {{ $status === 'current' ? 'text-primary' : 'text-on-surface' }} font-display">
                                    {{ $step['title'] }}
                                </p>
                                @if(!empty($step['description']))
                                    <p class="mt-0.5 text-xs text-on-surface-variant leading-relaxed">{{ $step['description'] }}</p>
                                @endif
                            </div>
                            @if(!empty($step['date']))
                                <div class="text-right text-[11px] whitespace-nowrap text-on-surface-variant font-mono">
                                    {{ $step['date'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>

