<?php

namespace App\Http\Requests;

use App\Support\Content\ContentTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'caption_id' => ['sometimes', 'nullable', 'string', 'max:300'],
            'caption_en' => ['sometimes', 'nullable', 'string', 'max:300'],
            'status' => ['sometimes', Rule::in(ContentTypes::PUBLISH_STATUSES)],
            'urutan' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
