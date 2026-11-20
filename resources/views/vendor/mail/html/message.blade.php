<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<span style="display:block;font-size:22px;font-weight:800;color:#0B5D2E;letter-spacing:.2px;">{{ config('asic.event_short_name', '4th ASIC 2027') }}</span>
<span style="display:block;margin-top:4px;font-size:12px;font-weight:600;color:#66746C;">Agrifood System International Conference &middot; Universitas Andalas</span>
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ __('asic-notifications.mail.copyright', ['event' => config('asic.event_short_name', '4th ASIC 2027')]) }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
