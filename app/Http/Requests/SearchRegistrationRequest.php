<?php

namespace App\Http\Requests;

use App\Support\PermissionMatrix;
use Illuminate\Foundation\Http\FormRequest;

class SearchRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(PermissionMatrix::class)->allows($this->user(), 'manage_registrations');
    }

    public function rules(): array
    {
        return ['q' => ['required', 'string', 'max:100']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $query = trim((string) $this->input('q', ''));
            if (mb_strlen($query) < 2 && ! ctype_digit($query)) {
                $validator->errors()->add('q', 'Masukkan minimal 2 karakter atau ID registrasi numerik.');
            }
        });
    }
}
