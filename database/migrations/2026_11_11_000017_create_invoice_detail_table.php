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
        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->increments('id_invoice_detail');
            $table->unsignedInteger('id_invoice');
            $table->string('jenis_biaya', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);

            $table->foreign('id_invoice')
                ->references('id_invoice')
                ->on('invoice')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_detail');
    }
};

