<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $isSecretariat = $user?->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT]) ?? false;

        return [
            'id_registration' => ['required', 'integer', 'exists:registration,id_registration'],
            'qr_token' => $isSecretariat ? ['sometimes', 'string'] : ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_registration.required' => 'ID Pendaftaran peserta wajib disertakan.',
            'id_registration.exists' => 'Data pendaftaran peserta tidak ditemukan.',
            'qr_token.required' => 'QR token sesi wajib disertakan.',
            'qr_token.string' => 'QR token sesi tidak valid.',
        ];
    }
}
