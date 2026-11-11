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
        Schema::create('payment_proof', function (Blueprint $table) {
            $table->increments('id_payment_proof');
            $table->unsignedInteger('id_invoice');
            $table->string('file_bukti', 255);
            $table->dateTime('tanggal_upload')->useCurrent();
            $table->decimal('nominal_transfer', 12, 2)->nullable();
            $table->text('catatan_peserta')->nullable();

            $table->foreign('id_invoice')
                ->references('id_invoice')
                ->on('invoice')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_proof');
    }
};

