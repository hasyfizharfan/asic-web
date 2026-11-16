{{-- KF-27: sertifikat A4 landscape, dua bahasa. Bahasa utama Indonesia untuk peserta dari Indonesia, Inggris selain itu.
     Seluruh isi berasal dari data registrasi/event; tidak ada teks karangan di luar label. --}}
@php
    $primary = ($certificate_lang ?? 'id') === 'en' ? 'en' : 'id';
    $secondary = $primary === 'id' ? 'en' : 'id';
    $l = fn (string $key, string $loc) => __('asic-documents.certificate.pdf.'.$key, [], $loc);
    $role = fn (string $loc) => __('asic-documents.certificate.roles.'.($participation_type ?? 'Listener'), [], $loc);
@endphp
<!DOCTYPE html>
<html lang="{{ $primary }}">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 18mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #17261D; text-align: center; }
    .frame { border: 6px double #0B5D2E; padding: 18px 28px; height: 150mm; }
    .title { font-size: 34px; font-weight: bold; letter-spacing: 6px; color: #0B5D2E; margin: 6px 0 0; }
    .sub { font-size: 12px; color: #4a5a50; font-style: italic; margin: 2px 0 10px; }
    .name { font-size: 28px; font-weight: bold; margin: 8px 0 2px; border-bottom: 1px solid #0B5D2E; display: inline-block; padding: 0 24px 4px; }
    .line { font-size: 13px; margin: 4px 0; }
    .muted { font-size: 11px; color: #4a5a50; font-style: italic; }
    .event { font-size: 16px; font-weight: bold; color: #0B5D2E; margin: 6px 0 2px; }
    .sign { width: 60%; margin: 10px auto 0; }
    .sign img { max-height: 55px; }
    .number { position: absolute; left: 30mm; bottom: 26mm; font-size: 10px; color: #4a5a50; text-align: left; }
    .sample-watermark { position: fixed; top: 40%; left: 0; right: 0; text-align: center; font-size: 64px; font-weight: bold; color: rgba(200, 0, 0, 0.18); transform: rotate(-20deg); }
</style>
</head>
<body>
@if(! empty($sample))<div class="sample-watermark">CONTOH / SAMPLE</div>@endif
<div class="frame">
    <p class="line" style="font-weight: bold;">{{ $letterhead['title'] ?? $event_name }}</p>
    <p class="title">{{ $l('title', $primary) }}</p>
    <p class="sub">{{ $l('title', $secondary) }}</p>
    <p class="line">{{ $l('awarded', $primary) }} <span class="muted">/ {{ $l('awarded', $secondary) }}</span></p>
    <p class="name">{{ $name }}</p>
    <p class="line">{{ $l('as', $primary) }} <strong>{{ $role($primary) }}</strong> <span class="muted">/ {{ $l('as', $secondary) }} {{ $role($secondary) }}</span></p>
    @if(! empty($is_presenter) && ! empty($abstract_title))
        <p class="line">{{ $l('paper', $primary) }} <span class="muted">/ {{ $l('paper', $secondary) }}</span></p>
        <p class="line"><em>"{{ $abstract_title }}"</em></p>
    @endif
    <p class="line">{{ $l('at', $primary) }} <span class="muted">/ {{ $l('at', $secondary) }}</span></p>
    <p class="event">{{ $event_name }}</p>
    @if(! empty($event_theme))<p class="line"><em>{{ $event_theme }}</em></p>@endif
    <p class="line">{{ collect([$event_dates ?? null, $event_location ?? null])->filter()->implode(' · ') }}</p>

    <div class="sign">
        <p class="line">{{ $city ?? '' }}</p>
        @if(! empty($signer['ttd']))<img src="{{ $signer['ttd'] }}" alt="">@endif
        <p class="line" style="font-weight: bold; margin-top: 4px;">{{ $signer['name'] ?? '' }}</p>
        <p class="muted">{{ $signer['title'] ?? '' }}</p>
    </div>
</div>
<div class="number">{{ $l('number', $primary) }} / {{ $l('number', $secondary) }}: {{ $nomor }}</div>
</body>
</html>
