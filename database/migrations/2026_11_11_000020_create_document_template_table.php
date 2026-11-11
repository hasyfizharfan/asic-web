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
        Schema::create('document_template', function (Blueprint $table) {
            $table->increments('id_template');
            $table->unsignedInteger('id_event');
            $table->string('nama_template', 100);
            $table->enum('jenis_dokumen', [
                'Abstrak',
                'Presentasi',
                'Artikel',
                'LOA',
                'Penolakan',
                'Invoice',
                'Receipt',
                'Sertifikat',
            ]);
            $table->string('file_template', 255);
            $table->dateTime('tanggal_upload')->useCurrent();
            $table->enum('status_template', ['Aktif', 'Tidak Aktif'])->default('Aktif');

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
        Schema::dropIfExists('document_template');
    }
};

