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
        Schema::create('registration', function (Blueprint $table) {
            $table->increments('id_registration');
            $table->unsignedInteger('id_participant');
            $table->unsignedInteger('id_event');
            $table->unsignedInteger('id_period');
            $table->enum('registration_type', ['Student', 'Regular', 'Committee']);
            $table->enum('attendance_mode', ['Daring', 'Luring']);
            $table->enum('participation_type', ['Oral Presenter', 'Poster Presenter', 'Listener']);
            $table->enum('publication_option', ['Prosiding', 'Jurnal', 'Tidak'])->default('Tidak');
            $table->dateTime('registration_date')->useCurrent();
            $table->enum('registration_status', ['Terdaftar', 'Dibatalkan'])->default('Terdaftar');

            $table->foreign('id_participant')
                ->references('id_participant')
                ->on('participant')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_event')
                ->references('id_event')
                ->on('event')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_period')
                ->references('id_period')
                ->on('registration_period')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration');
    }
};

