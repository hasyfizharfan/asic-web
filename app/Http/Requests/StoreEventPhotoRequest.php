<?php

namespace App\Http\Requests;

use App\Support\Content\ContentTypes;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_event' => ['required', 'integer', 'exists:event,id_event'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'caption_id' => ['nullable', 'string', 'max:300'],
            'caption_en' => ['nullable', 'string', 'max:300'],
            'status' => ['sometimes', Rule::in(ContentTypes::PUBLISH_STATUSES)],
            'urutan' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (trim((string) $this->input('caption_id')) === '' && trim((string) $this->input('caption_en')) === '') {
                $validator->errors()->add('caption_id', __('asic-content.validation.caption_required'));
            }
        }];
    }
}
