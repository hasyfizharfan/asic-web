<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Verifikasi email peserta yang mendaftar sendiri. Akun yang sudah ada saat migrasi
 * (staf, akun uji, peserta contoh) dianggap sudah terverifikasi agar tidak terkunci.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_account', 'email_verified_at')) {
            Schema::table('user_account', function (Blueprint $table): void {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }
        DB::table('user_account')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_account', 'email_verified_at')) {
            Schema::table('user_account', function (Blueprint $table): void {
                $table->dropColumn('email_verified_at');
            });
        }
    }
};
