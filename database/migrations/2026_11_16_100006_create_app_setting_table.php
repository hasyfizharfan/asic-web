<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** KF-23: pengaturan aplikasi yang dapat diubah panitia (mis. informasi pembayaran). Kunci-nilai sederhana. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_setting', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_setting');
    }
};
