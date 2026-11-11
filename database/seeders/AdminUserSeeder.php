<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('nama_role', Role::ROLE_ADMIN)->firstOrFail();

        UserAccount::updateOrCreate(
            ['email' => 'admin@asic.unand.ac.id'],
            [
                'id_role' => $adminRole->id_role,
                'password' => Hash::make('AdminASIC2026!'),
                'status_akun' => 'Aktif',
            ]
        );
    }
}

