@extends('documents.layout')
@section('content')
<h1>INVOICE</h1>

<table style="width:100%; margin-bottom:8px">
    <tr>
        <td style="width:55%; vertical-align:top">
            <strong>BILL TO</strong><br>
            {{ $name }}<br>
            {{ $institution }}@if(!empty($country)), {{ $country }}@endif<br>
            {{ $email }}
        </td>
        <td style="vertical-align:top">
            <table class="info">
                <tr><td>Date</td><td>: {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</td></tr>
                <tr><td>Invoice ID</td><td>: {{ $nomor }}</td></tr>
                @if(!empty($period_name))<tr><td>Period</td><td>: {{ $period_name }}</td></tr>@endif
            </table>
        </td>
    </tr>
</table>

<table class="grid">
    <tr><th>DESCRIPTION OF FEE PAYMENT</th><th class="right" style="width:30%">AMOUNT ({{ $currency ?? 'IDR' }})</th></tr>
    @foreach($items as $item)
    <tr>
        <td>{{ $item['jenis_biaya'] }}@if(!empty($item['deskripsi']))<br><span class="note">{{ $item['deskripsi'] }}</span>@endif
            @if(($item['jumlah'] ?? 1) > 1) (×{{ $item['jumlah'] }})@endif</td>
        <td class="right">{{ \App\Services\DocumentRenderer::money($item['subtotal'], $currency ?? 'IDR') }}</td>
    </tr>
    @endforeach
    <tr><th class="right">TOTAL ({{ $currency ?? 'IDR' }})</th><th class="right">{{ \App\Services\DocumentRenderer::money($total, $currency ?? 'IDR') }}</th></tr>
</table>

<p><strong>NOTES:</strong></p>
<p>Transfer to:</p>
@include('documents._payment')
<p>Payment due: <strong>{{ \Carbon\Carbon::parse($invoice_due)->format('d F Y') }} (23.59 WIB)</strong>.
    Please upload the proof of payment through the conference website.
    The receipt will be issued after being verified by the treasurer.</p>
@endsection
