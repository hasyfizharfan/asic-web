<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom scope pada abstract_submission,
     * serta format_nomor dan opsi enum jenis_dokumen ('Artikel', 'Presentasi')
     * pada document_template (Poin 3 & Poin 5).
     */
    public function up(): void
    {
        Schema::table('abstract_submission', function (Blueprint $table) {
            $table->string('scope', 150)
                ->nullable()
                ->after('judul_abstrak');
        });

        Schema::table('document_template', function (Blueprint $table) {
            $table->string('format_nomor', 100)
                ->nullable()
                ->after('jenis_dokumen');

            $table->enum('jenis_dokumen', [
                'Abstrak',
                'Presentasi',
                'Artikel',
                'LOA',
                'Penolakan',
                'Invoice',
                'Receipt',
                'Sertifikat',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_template', function (Blueprint $table) {
            $table->dropColumn('format_nomor');
        });

        Schema::table('abstract_submission', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};

