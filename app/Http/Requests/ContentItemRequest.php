<?php

namespace App\Http\Requests;

use App\Support\Content\ContentTypes;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi konten publik sederhana. Jenis diambil dari default route `content_type`,
 * aturan dari ContentTypes. Izin diperiksa middleware permission:manage_website_content.
 */
class ContentItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private function type(): string
    {
        return (string) $this->route()->defaults['content_type'];
    }

    private function partial(): bool
    {
        return $this->isMethod('patch');
    }

    public function rules(): array
    {
        return ContentTypes::rules($this->type(), $this->partial());
    }

    public function messages(): array
    {
        return [
            'tautan.regex' => __('asic-content.validation.link'),
            'telepon.regex' => __('asic-content.validation.phone'),
            'whatsapp.regex' => __('asic-content.validation.phone'),
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $current = $this->route('content_id') !== null
                ? ContentTypes::get($this->type())['model']::find($this->route('content_id'))?->toArray() ?? []
                : [];
            $merged = array_merge($current, $this->validated());
            foreach (ContentTypes::requiredBilingual($this->type()) as $field) {
                if (trim((string) ($merged[$field.'_id'] ?? '')) === '' && trim((string) ($merged[$field.'_en'] ?? '')) === '') {
                    $validator->errors()->add($field.'_id', __('asic-content.validation.one_language', ['field' => $field]));
                }
            }
        }];
    }
}
