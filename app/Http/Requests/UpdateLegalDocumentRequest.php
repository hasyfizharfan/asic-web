<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Hanya draft yang dapat diubah (diperiksa di controller); jenis & versi tetap. */
class UpdateLegalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul_id' => ['sometimes', 'nullable', 'string', 'max:200'],
            'judul_en' => ['sometimes', 'nullable', 'string', 'max:200'],
            'isi_id' => ['sometimes', 'nullable', 'string', 'max:100000'],
            'isi_en' => ['sometimes', 'nullable', 'string', 'max:100000'],
            'berlaku_mulai' => ['sometimes', 'required', 'date'],
        ];
    }
}
