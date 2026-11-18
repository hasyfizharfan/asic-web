<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // izin diperiksa middleware permission:manage_tariffs
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'account_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'account_no' => ['sometimes', 'nullable', 'string', 'max:50', 'regex:/^[0-9A-Za-z\-\s]+$/'],
            'swift_code' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'branch' => ['sometimes', 'nullable', 'string', 'max:255'],
            'paypal_account' => ['sometimes', 'nullable', 'string', 'max:150'],
            'usd_idr_rate' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:1000000'],
            'paypal_fee_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:20'],
            'qr_info' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'international_note' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
