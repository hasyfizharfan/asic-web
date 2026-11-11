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
        Schema::create('issued_document', function (Blueprint $table) {
            $table->increments('id_document');
            $table->unsignedInteger('id_registration');
            $table->unsignedInteger('id_template');
            $table->enum('jenis_dokumen', ['LOA', 'Penolakan', 'Invoice', 'Receipt', 'Sertifikat']);
            $table->string('nomor_dokumen', 50)->nullable();
            $table->string('file_document', 255);
            $table->dateTime('tanggal_terbit')->useCurrent();

            $table->foreign('id_registration')
                ->references('id_registration')
                ->on('registration')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_template')
                ->references('id_template')
                ->on('document_template')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issued_document');
    }
};

