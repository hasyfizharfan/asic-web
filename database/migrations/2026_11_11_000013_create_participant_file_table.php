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
        Schema::create('participant_file', function (Blueprint $table) {
            $table->increments('id_file');
            $table->unsignedInteger('id_registration');
            $table->enum('jenis_file', ['Presentasi', 'Artikel']);
            $table->string('nama_file', 255);
            $table->string('file_path', 255);
            $table->dateTime('tanggal_upload')->useCurrent();
            $table->enum('status_file', ['Diupload', 'Valid', 'Tidak Valid'])->default('Diupload');

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
        Schema::dropIfExists('participant_file');
    }
};

