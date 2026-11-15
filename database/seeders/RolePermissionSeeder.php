<?php

namespace Database\Seeders;

use App\Support\PermissionMatrix;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = PermissionMatrix::defaultMatrix();
        $stored = DB::table('role_permission_matrix')->where('id', 1)->value('matrix');
        $matrix = is_string($stored) ? json_decode($stored, true) : null;
        $matrix = is_array($matrix) ? $matrix : [];

        foreach ($defaults as $permission => $roleLevels) {
            $matrix[$permission] ??= [];
            foreach ($roleLevels as $roleId => $level) {
                $matrix[$permission][(string) $roleId] ??= $level;
            }
        }

        DB::table('role_permission_matrix')->updateOrInsert(
            ['id' => 1],
            [
                'matrix' => json_encode($matrix, JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]
        );
    }
}
