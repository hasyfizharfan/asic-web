<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABEL BARU (additif, tidak mengubah tabel lama) untuk KF-32: tautan kanal komunikasi
 * (grup WhatsApp / Google Group). ERD saat ini belum memiliki tabel ini.
 * Memerlukan persetujuan tim sebelum di-merge/migrate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_channel', function (Blueprint $table) {
            $table->increments('id_channel');
            $table->unsignedInteger('id_event');
            $table->string('nama_kanal', 100);
            $table->enum('jenis_kanal', ['WhatsApp', 'Google Group', 'Telegram', 'Lainnya'])->default('WhatsApp');
            $table->string('url', 500);
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');

            $table->foreign('id_event')
                ->references('id_event')
                ->on('event')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_channel');
    }
};
