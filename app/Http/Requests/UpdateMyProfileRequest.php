<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->nama_role === Role::ROLE_PESERTA;
    }

    public function rules(): array
    {
        return [
            'prefix' => ['nullable', 'string', 'max:10'], 'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'], 'passport_no' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:100'], 'phone' => ['nullable', 'string', 'max:20'],
            'institution' => ['nullable', 'string', 'max:150'], 'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'], 'country' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:20'],
        ];
    }
}
