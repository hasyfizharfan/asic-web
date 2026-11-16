<?php

namespace App\Http\Requests;

use App\Models\DocumentTemplate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Kelompok 1 (dokumen terbit): hanya format_nomor dan status_template.
 * Kelompok 2 (berkas unduhan): nama, status, subjenis, bahasa, versi.
 */
class UpdateDocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private function template(): ?DocumentTemplate
    {
        return DocumentTemplate::find($this->route('id_template'));
    }

    public function rules(): array
    {
        $issued = in_array($this->template()?->jenis_dokumen, DocumentTemplate::ISSUED, true);
        $common = ['status_template' => ['sometimes', Rule::in(['Aktif', 'Tidak Aktif'])]];

        return $issued
            ? $common + ['format_nomor' => ['sometimes', 'required', 'string', 'max:100']]
            : $common + [
                'nama_template' => ['sometimes', 'required', 'string', 'max:150'],
                'subjenis' => ['sometimes', 'nullable', Rule::in(DocumentTemplate::SUBJENIS)],
                'bahasa' => ['sometimes', 'nullable', Rule::in(['id', 'en'])],
                'versi' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[0-9A-Za-z.\-]+$/'],
            ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->has('format_nomor') && ! self::validFormat((string) $this->input('format_nomor'))) {
                $validator->errors()->add('format_nomor', __('asic-documents.validation.format_tokens'));
            }
        }];
    }

    /** Hanya token {SEQ} {SCOPE} {EDITION} {YYYY} {MM} yang dikenal, dan {SEQ} wajib ada. */
    public static function validFormat(string $format): bool
    {
        preg_match_all('/\{[^}]*\}/', $format, $m);
        foreach ($m[0] as $token) {
            if (! in_array($token, DocumentTemplate::NUMBER_TOKENS, true)) {
                return false;
            }
        }

        return str_contains($format, '{SEQ}') && ! preg_match('/[{}]/', preg_replace('/\{(SEQ|SCOPE|EDITION|YYYY|MM)\}/', '', $format));
    }
}
