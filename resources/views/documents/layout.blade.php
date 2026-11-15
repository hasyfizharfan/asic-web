<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 1.6cm 2cm 1.8cm 2cm; }
    body { font-family: "Times New Roman", Times, serif; font-size: 11.5pt; line-height: 1.35; color: #000; }
    .kop { width: 100%; border-bottom: 3px double #1f5c3a; padding-bottom: 6px; margin-bottom: 14px; }
    .kop td { vertical-align: middle; }
    .kop .logo { width: 70px; text-align: center; }
    .kop .logo img { max-width: 64px; max-height: 64px; }
    .kop .text { text-align: center; }
    .kop .t1 { font-size: 13pt; font-weight: bold; color: #1f5c3a; }
    .kop .t2 { font-size: 11pt; font-weight: bold; }
    .kop .t3 { font-size: 8.5pt; }
    h1 { font-size: 15pt; text-align: center; margin: 4px 0 2px; }
    .ref { text-align: center; margin-bottom: 14px; }
    p { margin: 0 0 8px; text-align: justify; }
    .title { text-align: center; font-weight: bold; font-style: italic; margin: 6px 0 8px; }
    table.info td { padding: 1px 4px; vertical-align: top; }
    table.grid { width: 100%; border-collapse: collapse; margin: 6px 0; }
    table.grid th, table.grid td { border: 1px solid #555; padding: 4px 6px; vertical-align: top; }
    table.grid th { background: #e8f3ec; }
    .right { text-align: right; }
    .sign { width: 260px; margin-left: auto; margin-top: 18px; }
    .sign .box { position: relative; height: 80px; }
    .sign .ttd { position: absolute; left: 10px; top: 0; max-height: 75px; max-width: 170px; }
    .sign .stempel { position: absolute; left: 0; top: 0; max-height: 80px; max-width: 80px; opacity: 0.85; }
    .sign .name { font-weight: bold; text-decoration: underline; }
    .note { font-size: 10pt; }
</style>
</head>
<body>
<table class="kop">
    <tr>
        <td class="logo">@if($logoLeft)<img src="{{ $logoLeft }}">@endif</td>
        <td class="text">
            <div class="t1">{{ $letterhead['title'] }}</div>
            <div class="t2">{{ $letterhead['subtitle'] }}</div>
            <div class="t3">Address: {{ $letterhead['address'] }}</div>
            @if(!empty($letterhead['contact']))<div class="t3">{{ $letterhead['contact'] }}</div>@endif
        </td>
        <td class="logo">@if($logoRight)<img src="{{ $logoRight }}">@endif</td>
    </tr>
</table>

@yield('content')

@hasSection('no-sign')
@else
<div class="sign">
    <div>{{ $city }}, {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</div>
    @if(($signer['title'] ?? '') === 'Chair Person')<div>Chair Person,</div>@endif
    <div class="box">
        @if(!empty($signer['stempel']))<img class="stempel" src="{{ $signer['stempel'] }}">@endif
        @if(!empty($signer['ttd']))<img class="ttd" src="{{ $signer['ttd'] }}">@endif
    </div>
    <div class="name">{{ $signer['name'] }}</div>
    @if(($signer['title'] ?? '') !== 'Chair Person')<div>{{ $signer['title'] }}</div>@endif
</div>
@endif
</body>
</html>
