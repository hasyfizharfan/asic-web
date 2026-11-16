<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanReregistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // izin diperiksa middleware permission:scan_attendance_qr
    }

    public function rules(): array
    {
        return ['qr_token' => ['required', 'string', 'max:100']];
    }
}
