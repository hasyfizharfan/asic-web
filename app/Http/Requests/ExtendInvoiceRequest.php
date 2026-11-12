<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtendInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_jatuh_tempo_baru' => ['required', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_jatuh_tempo_baru.required' => 'Tanggal jatuh tempo baru wajib diisi.',
            'tanggal_jatuh_tempo_baru.date' => 'Format tanggal jatuh tempo baru tidak valid.',
            'tanggal_jatuh_tempo_baru.after' => 'Tanggal jatuh tempo baru harus setelah hari ini.',
        ];
    }
}

