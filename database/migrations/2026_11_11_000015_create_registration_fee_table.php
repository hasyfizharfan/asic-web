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
        Schema::create('registration_fee', function (Blueprint $table) {
            $table->increments('id_fee');
            $table->unsignedInteger('id_event');
            $table->unsignedInteger('id_period');
            $table->enum('registration_type', ['Student', 'Regular', 'Committee']);
            $table->enum('attendance_mode', ['Daring', 'Luring']);
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->decimal('discount', 12, 2)->default(0.00);

            $table->foreign('id_event')
                ->references('id_event')
                ->on('event')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_period')
                ->references('id_period')
                ->on('registration_period')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_fee');
    }
};

