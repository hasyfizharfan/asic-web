<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Mengatur format nomor dokumen terbit (membuat baris Aktif bila jenis masih "Bawaan"). */
class UpsertIssuedTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_event' => ['required', 'integer', 'exists:event,id_event'],
            'format_nomor' => ['required', 'string', 'max:100'],
            'status_template' => ['sometimes', Rule::in(['Aktif', 'Tidak Aktif'])],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! UpdateDocumentTemplateRequest::validFormat((string) $this->input('format_nomor'))) {
                $validator->errors()->add('format_nomor', __('asic-documents.validation.format_tokens'));
            }
        }];
    }
}
