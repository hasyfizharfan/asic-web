<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Penerbitan sertifikat tidak menerima isian; nama diambil dari profil saat terbit. Akses dicek di controller. */
class IssueCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [];
    }
}
