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
        Schema::create('payment_verification', function (Blueprint $table) {
            $table->increments('id_verification');
            $table->unsignedInteger('id_payment_proof');
            $table->unsignedInteger('id_treasurer');
            $table->dateTime('tanggal_verifikasi')->useCurrent();
            $table->enum('hasil_verifikasi', ['Valid', 'Tidak Valid']);
            $table->text('alasan_penolakan')->nullable();

            $table->foreign('id_payment_proof')
                ->references('id_payment_proof')
                ->on('payment_proof')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_treasurer')
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
        Schema::dropIfExists('payment_verification');
    }
};

