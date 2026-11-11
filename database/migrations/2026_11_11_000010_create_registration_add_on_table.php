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
        Schema::create('registration_add_on', function (Blueprint $table) {
            $table->unsignedInteger('id_registration');
            $table->unsignedInteger('id_add_on');
            $table->integer('jumlah')->default(1);
            $table->decimal('subtotal', 12, 2)->default(0.00);

            $table->primary(['id_registration', 'id_add_on']);

            $table->foreign('id_registration')
                ->references('id_registration')
                ->on('registration')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_add_on')
                ->references('id_add_on')
                ->on('add_on')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_add_on');
    }
};

