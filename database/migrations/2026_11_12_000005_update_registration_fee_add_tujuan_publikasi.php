<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom tujuan_publikasi pada tabel registration_fee (Poin 4).
     */
    public function up(): void
    {
        Schema::table('registration_fee', function (Blueprint $table) {
            $table->enum('tujuan_publikasi', ['Prosiding', 'Jurnal', 'Tidak'])
                ->nullable()
                ->after('attendance_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_fee', function (Blueprint $table) {
            $table->dropColumn('tujuan_publikasi');
        });
    }
};

