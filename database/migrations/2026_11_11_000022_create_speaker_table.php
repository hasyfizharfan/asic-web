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
        Schema::create('speaker', function (Blueprint $table) {
            $table->increments('id_speaker');
            $table->unsignedInteger('id_event');
            $table->string('nama_speaker', 100);
            $table->string('institusi', 150)->nullable();
            $table->string('negara', 100)->nullable();
            $table->string('foto', 255)->nullable();
            $table->text('deskripsi')->nullable();

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
        Schema::dropIfExists('speaker');
    }
};

