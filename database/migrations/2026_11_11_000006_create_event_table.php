<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event', function (Blueprint $table) {
            $table->increments('id_event');
            $table->string('nama_event', 150);
            $table->string('tema', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('lokasi', 150)->nullable();
            $table->enum('mode_kegiatan', ['Hybrid', 'Daring', 'Luring'])->default('Hybrid');
            $table->enum('status_event', ['Draft', 'Aktif', 'Selesai', 'Arsip'])->default('Draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};

