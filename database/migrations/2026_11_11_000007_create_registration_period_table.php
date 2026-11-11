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
        Schema::create('registration_period', function (Blueprint $table) {
            $table->increments('id_period');
            $table->unsignedInteger('id_event');
            $table->string('nama_periode', 50);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status_periode', ['Aktif', 'Tidak Aktif'])->default('Tidak Aktif');

            $table->foreign('id_event')
                ->references('id_event')
                ->on('event')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_period');
    }
};

