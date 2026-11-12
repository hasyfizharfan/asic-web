<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel event_session untuk mendukung QR code sesi kegiatan (Poin 1).
     */
    public function up(): void
    {
        Schema::create('event_session', function (Blueprint $table) {
            $table->increments('id_session');
            $table->unsignedInteger('id_event');
            $table->string('nama_sesi', 150);
            $table->dateTime('waktu');
            $table->string('qr_code_path', 255)->nullable();

            $table->foreign('id_event')
                ->references('id_event')
                ->on('event')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_session');
    }
};

