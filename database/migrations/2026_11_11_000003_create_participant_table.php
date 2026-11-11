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
        Schema::create('participant', function (Blueprint $table) {
            $table->increments('id_participant');
            $table->unsignedInteger('id_user')->unique();
            $table->string('prefix', 10)->nullable();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('passport_no', 30)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('institution', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postcode', 15)->nullable();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('user_account')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant');
    }
};

