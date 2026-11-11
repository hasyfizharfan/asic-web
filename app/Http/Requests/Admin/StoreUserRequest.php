<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Persiapkan data untuk validasi (normalisasi field UI).
     */
    protected function prepareForValidation(): void
    {
        // Normalisasi role jika dikirimkan sebagai string nama peran (e.g., "Secretariat", "Participant")
        if (!$this->filled('id_role') && $this->filled('role')) {
            $roleInput = strtolower(trim((string) $this->input('role')));
            $roleMap = [
                'participant' => Role::ROLE_PESERTA,
                'peserta' => Role::ROLE_PESERTA,
                'secretariat' => Role::ROLE_SEKRETARIAT,
                'sekretariat' => Role::ROLE_SEKRETARIAT,
                'treasurer' => Role::ROLE_BENDAHARA,
                'bendahara' => Role::ROLE_BENDAHARA,
                'abstract_team' => Role::ROLE_TIM_ABSTRAK,
                'tim abstrak' => Role::ROLE_TIM_ABSTRAK,
                'admin' => Role::ROLE_ADMIN,
            ];

            $targetRoleName = $roleMap[$roleInput] ?? Role::ROLE_PESERTA;
            $role = Role::where('nama_role', $targetRoleName)->first();
            if ($role) {
                $this->merge(['id_role' => $role->id_role]);
            }
        }

        // Normalisasi status_akun jika dikirimkan dalam bahasa Inggris
        if ($this->filled('status')) {
            $statusInput = strtolower(trim((string) $this->input('status')));
            $statusAkun = in_array($statusInput, ['active', 'aktif'], true) ? 'Aktif' : 'Nonaktif';
            $this->merge(['status_akun' => $statusAkun]);
        } elseif (!$this->filled('status_akun')) {
            $this->merge(['status_akun' => 'Aktif']);
        }
    }

    /**
     * Aturan validasi untuk pembuatan akun pengguna oleh Admin / modal "+ Invite New User".
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_role' => ['required', 'integer', 'exists:role,id_role'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:user_account,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'status_akun' => ['required', 'in:Aktif,Nonaktif'],
            'name' => ['nullable', 'string', 'max:100'],
            'full_name' => ['nullable', 'string', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:30'],
            'passport_no' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'affiliation' => ['nullable', 'string', 'max:150'],
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
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'status_akun.required' => 'Status akun wajib ditentukan.',
            'status_akun.in' => 'Status akun harus bernilai Aktif atau Nonaktif.',
        ];
    }
}
