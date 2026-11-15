<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = config('asic.admin_default_password');
        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException('ADMIN_DEFAULT_PASSWORD wajib diatur sebelum AdminUserSeeder dijalankan.');
        }

        $adminRole = Role::where('nama_role', Role::ROLE_ADMIN)->firstOrFail();

        UserAccount::updateOrCreate(
            ['email' => 'admin@asic.unand.ac.id'],
            [
                'id_role' => $adminRole->id_role,
                'password' => Hash::make($password),
                'status_akun' => 'Aktif',
            ]
        );
    }
}
