@extends('documents.layout')
@section('content')
<h1>Kuitansi / Receipt</h1>
<div class="ref">No: {{ $nomor }}</div>

<table class="grid">
    <tr>
        <td style="width:32%">Sudah terima dari<br><em>Received From</em></td>
        <td colspan="2">{{ $name }}@if(!empty($institution))<br><span class="note">{{ $institution }}</span>@endif</td>
    </tr>
    <tr>
        <td>Banyaknya Uang<br><em>Amount Received</em></td>
        <td colspan="2"><em>{{ \App\Services\DocumentRenderer::amountInWords($amount, $currency ?? 'IDR') }}</em></td>
    </tr>
    @foreach(($items ?? []) as $i => $item)
    <tr>
        @if($i === 0)<td rowspan="{{ count($items) }}">Untuk Pembayaran<br><em>In Payment of</em></td>@endif
        <td>{{ $item['jenis_biaya'] }}@if(!empty($item['deskripsi'])) – {{ $item['deskripsi'] }}@endif</td>
        <td class="right" style="width:22%">{{ \App\Services\DocumentRenderer::money($item['subtotal'], $currency ?? 'IDR') }}</td>
    </tr>
    @endforeach
    <tr><th colspan="2" class="right">TOTAL ({{ $currency ?? 'IDR' }})</th><th class="right">{{ \App\Services\DocumentRenderer::money($amount, $currency ?? 'IDR') }}</th></tr>
</table>

<p class="note">Invoice No. {{ $invoice_nomor }} · Verified on {{ \Carbon\Carbon::parse($verified_at)->format('d F Y H:i') }}</p>
@endsection
