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
        Schema::create('invoice', function (Blueprint $table) {
            $table->increments('id_invoice');
            $table->unsignedInteger('id_registration')->unique();
            $table->unsignedInteger('id_fee');
            $table->string('nomor_invoice', 50)->unique();
            $table->dateTime('tanggal_invoice')->useCurrent();
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('total_tagihan', 12, 2)->default(0.00);
            $table->string('payment_qr_info', 255)->nullable();
            $table->enum('status_pembayaran', [
                'Menunggu Pembayaran',
                'Menunggu Verifikasi',
                'Terverifikasi',
                'Ditolak',
                'Kadaluwarsa',
            ])->default('Menunggu Pembayaran');

            $table->foreign('id_registration')
                ->references('id_registration')
                ->on('registration')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_fee')
                ->references('id_fee')
                ->on('registration_fee')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};

