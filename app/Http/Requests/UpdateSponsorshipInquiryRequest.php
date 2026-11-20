<?php

namespace App\Http\Requests;

use App\Models\SponsorshipInquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Hanya status dan catatan internal yang boleh diubah panitia. */
class UpdateSponsorshipInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(SponsorshipInquiry::STATUSES)],
            'catatan_internal' => ['sometimes', 'nullable', 'string', 'max:3000'],
        ];
    }
}
