<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Support\PermissionMatrix;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateRolePermissionMatrixRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     * Otorisasi pembaruan dilakukan oleh middleware permission pada rute.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk pembaruan matriks Role & Permission.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $matrix = $this->input('matrix', []);
            if (! is_array($matrix)) {
                return;
            }

            $expectedPermissions = array_column(PermissionMatrix::capabilities(), 'id');
            $actualPermissions = array_map('strval', array_keys($matrix));
            sort($expectedPermissions);
            sort($actualPermissions);

            if ($actualPermissions !== $expectedPermissions) {
                $validator->errors()->add('matrix', 'Matriks harus memuat seluruh permission yang terdaftar dan tidak boleh memuat slug lain.');

                return;
            }

            $expectedRoleIds = Role::query()->pluck('id_role')->map(fn ($id): string => (string) $id)->all();
            sort($expectedRoleIds);

            foreach ($matrix as $permission => $roleLevels) {
                if (! is_array($roleLevels)) {
                    continue;
                }

                $actualRoleIds = array_map('strval', array_keys($roleLevels));
                sort($actualRoleIds);

                if ($actualRoleIds !== $expectedRoleIds) {
                    $validator->errors()->add("matrix.{$permission}", 'Matriks harus memuat seluruh ID peran yang tersedia.');
                }
            }
        });
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
