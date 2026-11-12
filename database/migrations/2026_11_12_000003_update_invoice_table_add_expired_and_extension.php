<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah tabel invoice untuk mendukung status Kedaluwarsa,
     * jumlah perpanjangan, dan tanggal jatuh tempo baru (Poin 2).
     */
    public function up(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->enum('status_pembayaran', [
                'Menunggu Pembayaran',
                'Menunggu Verifikasi',
                'Terverifikasi',
                'Ditolak',
                'Kadaluwarsa',
                'Kedaluwarsa',
            ])->default('Menunggu Pembayaran')->change();

            $table->unsignedInteger('jumlah_perpanjangan')
                ->default(0)
                ->after('status_pembayaran');

            $table->date('tanggal_jatuh_tempo_baru')
                ->nullable()
                ->after('jumlah_perpanjangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_perpanjangan',
                'tanggal_jatuh_tempo_baru',
            ]);

            $table->enum('status_pembayaran', [
                'Menunggu Pembayaran',
                'Menunggu Verifikasi',
                'Terverifikasi',
                'Ditolak',
                'Kadaluwarsa',
            ])->default('Menunggu Pembayaran')->change();
        });
    }
};

