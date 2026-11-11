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
        Schema::create('abstract_review', function (Blueprint $table) {
            $table->increments('id_review');
            $table->unsignedInteger('id_abstract');
            $table->unsignedInteger('id_reviewer');
            $table->dateTime('tanggal_review')->useCurrent();
            $table->enum('hasil_review', ['Diterima', 'Ditolak']);
            $table->text('catatan_review')->nullable();

            $table->foreign('id_abstract')
                ->references('id_abstract')
                ->on('abstract_submission')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_reviewer')
                ->references('id_user')
                ->on('user_account')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abstract_review');
    }
};

