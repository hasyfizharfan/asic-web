<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel digital_signature untuk tanda tangan dan stempel berbasis peran (Poin 6).
     */
    public function up(): void
    {
        Schema::create('digital_signature', function (Blueprint $table) {
            $table->increments('id_signature');
            $table->unsignedInteger('id_role');
            $table->string('file_ttd', 255);
            $table->string('file_stempel', 255)->nullable();
            $table->dateTime('tanggal_upload')->useCurrent();

            $table->foreign('id_role')
                ->references('id_role')
                ->on('role')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signature');
    }
};

