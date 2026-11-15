@php($pay = array_filter([
    'Bank name' => $payment['bank_name'] ?? null,
    'Account name' => $payment['account_name'] ?? null,
    'Account No.' => $payment['account_no'] ?? null,
    'Swift code' => $payment['swift_code'] ?? null,
    'Bank branch address' => $payment['branch'] ?? null,
]))
@if($pay)
<table class="info">
    @foreach($pay as $label => $value)
    <tr><td>{{ $label }}</td><td>: {{ $value }}</td></tr>
    @endforeach
</table>
@endif
@if(!empty($payment_qr_info))<p class="note">Payment QR: {{ $payment_qr_info }}</p>@endif
@if(!empty($payment['international_note']))<p class="note">{{ $payment['international_note'] }}</p>@endif
