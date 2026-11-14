<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registration_period MODIFY status_periode ENUM('Aktif','Tidak Aktif','Ditutup') NOT NULL DEFAULT 'Tidak Aktif'");
        }
    }

    public function down(): void
    {
        DB::table('registration_period')->where('status_periode', 'Ditutup')->update(['status_periode' => 'Tidak Aktif']);
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registration_period MODIFY status_periode ENUM('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Tidak Aktif'");
        }
    }
};
