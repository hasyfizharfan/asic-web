<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ditegakkan middleware permission:verify_payment
    }

    public function rules(): array
    {
        return [
            'hasil_verifikasi' => ['required', 'in:Valid,Tidak Valid'],
            'alasan_penolakan' => ['required_if:hasil_verifikasi,Tidak Valid', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'hasil_verifikasi.required' => 'Hasil verifikasi wajib diisi (Valid atau Tidak Valid).',
            'hasil_verifikasi.in' => 'Hasil verifikasi harus Valid atau Tidak Valid.',
            'alasan_penolakan.required_if' => 'Alasan penolakan wajib diisi jika bukti pembayaran tidak valid.',
        ];
    }
}
