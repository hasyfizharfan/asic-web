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
        Schema::create('attendance', function (Blueprint $table) {
            $table->increments('id_attendance');
            $table->unsignedInteger('id_registration')->unique();
            $table->string('qr_code', 255)->unique();
            $table->dateTime('tanggal_generate')->useCurrent();
            $table->dateTime('tanggal_check_in')->nullable();
            $table->enum('status_kehadiran', ['Tidak Hadir', 'Hadir'])->default('Tidak Hadir');

            $table->foreign('id_registration')
                ->references('id_registration')
                ->on('registration')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};

