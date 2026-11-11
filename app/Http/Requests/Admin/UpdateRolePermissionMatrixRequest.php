<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermissionMatrixRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     * Hanya pengguna dengan peran Admin yang dapat mengubah matriks hak akses.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk pembaruan matriks Role & Permission.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'matrix' => ['required', 'array'],
            'matrix.*' => ['required', 'array'],
            'matrix.*.*' => ['required', 'string', 'in:full_access,view_only,restricted,Full Access,View Only,Restricted'],
            'audit_note' => ['nullable', 'string', 'max:255'],
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
            'matrix.required' => 'Data matriks hak akses wajib disertakan.',
            'matrix.array' => 'Format data matriks hak akses tidak valid.',
            'matrix.*.*.in' => 'Tingkat hak akses harus bernilai full_access, view_only, atau restricted.',
        ];
    }
}

