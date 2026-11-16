<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Support\PermissionMatrix;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateMyPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $permission = match ($user?->role?->nama_role) {
            Role::ROLE_PESERTA => 'access_participant_portal',
            Role::ROLE_TIM_ABSTRAK => 'access_reviewer_portal',
            default => null,
        };

        return $permission !== null && app(PermissionMatrix::class)->allows($user, $permission);
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:sanctum'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }
}
