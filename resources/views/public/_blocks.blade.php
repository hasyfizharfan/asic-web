{{-- Blok konten panitia: teks biasa, di-escape lalu nl2br; tautan hanya https:// atau mailto: (divalidasi saat simpan). --}}
@foreach($blocks as $block)
    <article class="rounded-2xl border border-[#d9e3d0] bg-white p-6">
        <p class="text-xs font-bold uppercase tracking-wider text-[#0F3D24]/70">{{ __('asic-content.sections.'.$block->section) }}</p>
        @if($block->localized('judul'))<h2 class="mt-1 text-xl font-bold text-[#0F3D24]">{{ $block->localized('judul') }}</h2>@endif
        @if($block->localized('isi'))<p class="mt-3 text-sm leading-relaxed text-[#2f3d33]">{!! nl2br(e($block->localized('isi'))) !!}</p>@endif
        @if($block->tautan && preg_match('#^(https://|mailto:)#i', $block->tautan))
            <a href="{{ $block->tautan }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-[#005226] underline">{{ $block->tautan }}</a>
        @endif
    </article>
@endforeach
