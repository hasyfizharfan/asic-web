<?php

namespace App\Http\Requests;

use App\Models\LegalDocument;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLegalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(LegalDocument::TYPES)],
            'versi' => ['required', 'string', 'max:20', 'regex:/^[0-9A-Za-z.\-]+$/', Rule::unique('legal_document', 'versi')->where('jenis', $this->input('jenis'))],
            'judul_id' => ['nullable', 'string', 'max:200'],
            'judul_en' => ['nullable', 'string', 'max:200'],
            'isi_id' => ['nullable', 'string', 'max:100000'],
            'isi_en' => ['nullable', 'string', 'max:100000'],
            'berlaku_mulai' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (trim((string) $this->input('judul_id')) === '' && trim((string) $this->input('judul_en')) === '') {
                $validator->errors()->add('judul_id', __('asic-content.validation.one_language', ['field' => 'judul']));
            }
        }];
    }
}
