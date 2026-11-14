@props([
    'headers' => [], // ['Col 1', 'Col 2']
    'striped' => false
])

<div class="w-full overflow-hidden rounded-xl border border-surface-container-highest bg-surface-container-lowest shadow-xs">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full text-left text-xs text-on-surface border-collapse']) }}>
            @if(!empty($headers) || isset($header))
                <thead class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider text-[11px] font-bold border-b border-surface-container-highest">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <tr>
                            @foreach($headers as $h)
                                <th scope="col" class="px-5 py-3.5">{{ $h }}</th>
                            @endforeach
                        </tr>
                    @endif
                </thead>
            @endif

            <tbody class="divide-y divide-surface-container-highest {{ $striped ? '[&>tr:nth-child(even)]:bg-surface-container-low/40' : '' }}">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($footer))
        <div class="px-5 py-3 border-t border-surface-container-highest bg-surface-container-low text-xs text-on-surface-variant flex items-center justify-between">
            {{ $footer }}
        </div>
    @endif
</div>

