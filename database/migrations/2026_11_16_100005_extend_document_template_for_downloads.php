<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2 (KF-26, KF-06): jenis 'Panduan' dan metadata berkas unduhan pada document_template.
 * Perluasan enum memakai cara yang sama dengan migration 2026_11_12_000004 (->change(),
 * didukung SQLite dan MySQL di Laravel 12). Semua kolom baru nullable sehingga data lama aman.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_template', function (Blueprint $table) {
            $table->enum('jenis_dokumen', ['Abstrak', 'Presentasi', 'Artikel', 'Panduan', 'LOA', 'Penolakan', 'Invoice', 'Receipt', 'Sertifikat'])->change();
        });

        Schema::table('document_template', function (Blueprint $table) {
            $table->string('subjenis', 30)->nullable();
            $table->string('bahasa', 2)->nullable();
            $table->string('versi', 20)->nullable();
            $table->string('nama_file_asli', 255)->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('ukuran_file')->nullable();
            $table->unsignedInteger('id_user_pengunggah')->nullable();
            $table->foreign('id_user_pengunggah')->references('id_user')->on('user_account')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_template', function (Blueprint $table) {
            $table->dropForeign(['id_user_pengunggah']);
            $table->dropColumn(['subjenis', 'bahasa', 'versi', 'nama_file_asli', 'mime', 'ukuran_file', 'id_user_pengunggah']);
        });
    }
};
