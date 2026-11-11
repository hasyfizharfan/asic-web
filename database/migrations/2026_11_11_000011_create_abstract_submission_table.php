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
        Schema::create('abstract_submission', function (Blueprint $table) {
            $table->increments('id_abstract');
            $table->unsignedInteger('id_registration')->unique();
            $table->string('judul_abstrak', 255);
            $table->enum('metode_presentasi', ['Oral', 'Poster']);
            $table->string('file_abstrak', 255)->nullable();
            $table->dateTime('tanggal_submit')->useCurrent();
            $table->enum('status_abstrak', ['Diajukan', 'Diterima', 'Ditolak'])->default('Diajukan');

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
        Schema::dropIfExists('abstract_submission');
    }
};

