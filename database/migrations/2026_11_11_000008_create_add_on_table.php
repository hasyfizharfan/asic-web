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
        Schema::create('add_on', function (Blueprint $table) {
            $table->increments('id_add_on');
            $table->unsignedInteger('id_event');
            $table->string('nama_add_on', 100);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 12, 2)->default(0.00);
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

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
        Schema::dropIfExists('add_on');
    }
};

