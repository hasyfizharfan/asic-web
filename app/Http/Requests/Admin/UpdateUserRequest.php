<?php

namespace App\Http\Requests\Admin;

use App\Models\UserAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk update akun pengguna oleh Admin.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetUser = $this->route('user') ?? $this->route('id_user');
        $userId = $targetUser instanceof UserAccount ? $targetUser->id_user : $targetUser;

        return [
            'id_role' => ['required', 'integer', 'exists:role,id_role'],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('user_account', 'email')->ignore($userId, 'id_user'),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'status_akun' => ['required', 'in:Aktif,Nonaktif'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'passport_no' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'institution' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:15'],
        ];
    }

    /**
     * Pesan kustom validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_role.required' => 'Peran (Role) wajib dipilih.',
            'id_role.exists' => 'Peran yang dipilih tidak valid.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Panjang email maksimal 100 karakter.',
            'email.unique' => 'Alamat email ini sudah terdaftar untuk pengguna lain.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'status_akun.required' => 'Status akun wajib ditentukan.',
            'status_akun.in' => 'Status akun harus bernilai Aktif atau Nonaktif.',
        ];
    }
}

