{{-- Logo penyelenggara, pendukung, sponsor, dan mitra per kategori (Konten > Sponsor & mitra). $sponsors: koleksi EventSponsor terpublikasi. --}}
@php($groups = collect(\App\Models\EventSponsor::CATEGORIES)->mapWithKeys(fn ($c) => [$c => $sponsors->where('kategori', $c)->values()])->filter(fn ($g) => $g->isNotEmpty()))
<div class="space-y-10">
    @foreach($groups as $category => $items)
        <div>
            <h3 class="text-center text-xs font-bold uppercase tracking-[0.2em] text-[#5b6b60]">{{ __('asic-public.partner_groups.'.$category) }}</h3>
            <ul class="mt-5 flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
                @foreach($items as $item)
                    <li class="flex flex-col items-center gap-2">
                        @php($inner = $item->logo_url
                            ? '<img src="'.e($item->logo_url).'" alt="'.e($item->nama).'" loading="lazy" class="h-14 w-auto max-w-[180px] object-contain sm:h-16">'
                            : '<span class="rounded-full bg-[#eef3ec] px-4 py-2 text-sm font-semibold text-[#0f3d24]">'.e($item->nama).'</span>')
                        @if($item->url)
                            <a href="{{ $item->url }}" target="_blank" rel="noopener sponsored" class="rounded-lg transition hover:opacity-80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#0B5D2E]" title="{{ $item->nama }}">{!! $inner !!}</a>
                        @else
                            {!! $inner !!}
                        @endif
                        @if($item->tingkat)<span class="text-[11px] font-semibold uppercase tracking-wider text-[#5b6b60]">{{ $item->tingkat }}</span>@endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
