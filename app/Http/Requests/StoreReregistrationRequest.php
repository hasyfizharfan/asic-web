<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReregistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // izin diperiksa middleware permission:scan_attendance_qr
    }

    public function rules(): array
    {
        return ['id_registration' => ['required', 'integer', 'exists:registration,id_registration']];
    }
}
