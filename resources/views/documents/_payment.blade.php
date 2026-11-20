@php($pay = array_filter([
    'Bank name' => $payment['bank_name'] ?? null,
    'Account name' => $payment['account_name'] ?? null,
    'Account No.' => $payment['account_no'] ?? null,
    'Swift code' => $payment['swift_code'] ?? null,
    'Bank branch address' => $payment['branch'] ?? null,
    'PayPal' => $payment['paypal_account'] ?? null,
]))
@if($pay)
<table class="info">
    @foreach($pay as $label => $value)
    <tr><td>{{ $label }}</td><td>: {{ $value }}</td></tr>
    @endforeach
</table>
@endif
@if(!empty($payment['account_no']))<p class="note">International participants may also pay via Wise or Flip to the bank account above{{ !empty($payment['paypal_account']) ? ', or via PayPal' : '' }}.</p>@endif
<p class="note">All bank charges, PayPal fees, and currency conversion costs are borne by the participant; the amount received by the committee must equal the invoice total. For international bank transfers, please choose the OUR charge option.</p>
@if(!empty($payment_qr_info))<p class="note">Payment QR: {{ $payment_qr_info }}</p>@endif
@if(!empty($payment['international_note']))<p class="note">{{ $payment['international_note'] }}</p>@endif
