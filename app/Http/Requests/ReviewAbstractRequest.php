<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewAbstractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hasil_review' => ['required', 'in:Diterima,Ditolak'],
            'catatan_review' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'hasil_review.required' => 'Keputusan review wajib ditentukan (Diterima atau Ditolak).',
            'hasil_review.in' => 'Hasil review harus berupa Diterima atau Ditolak.',
            'catatan_review.max' => 'Catatan review maksimal 2000 karakter.',
        ];
    }
}

