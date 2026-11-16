<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'catatan_review' => ['nullable', 'string', 'max:2000', Rule::requiredIf(fn () => $this->input('hasil_review') === 'Ditolak')],
        ];
    }

    public function messages(): array
    {
        return [
            'hasil_review.required' => 'Keputusan review wajib ditentukan (Diterima atau Ditolak).',
            'hasil_review.in' => 'Hasil review harus berupa Diterima atau Ditolak.',
            'catatan_review.max' => 'Catatan review maksimal 2000 karakter.',
            'catatan_review.required' => 'Catatan review wajib diisi saat abstrak ditolak.',
            'catatan_review.min' => 'Catatan review untuk penolakan minimal 15 karakter.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->sometimes('catatan_review', ['string', 'min:15'], fn () => $this->input('hasil_review') === 'Ditolak');
    }
}
