<?php

namespace Database\Seeders;

use App\Support\PermissionMatrix;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role_permission_matrix')->updateOrInsert(
            ['id' => 1],
            [
                'matrix' => json_encode(PermissionMatrix::defaultMatrix(), JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]
        );
    }
}
