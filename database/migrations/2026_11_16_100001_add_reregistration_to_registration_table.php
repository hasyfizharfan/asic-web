<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KF-12: registrasi ulang peserta luring pada hari pertama kegiatan.
 * Kolom baru nullable sehingga aman untuk data lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration', function (Blueprint $table) {
            $table->dateTime('reregistered_at')->nullable();
            $table->unsignedInteger('reregistered_by')->nullable();
            $table->foreign('reregistered_by')->references('id_user')->on('user_account')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registration', function (Blueprint $table) {
            $table->dropForeign(['reregistered_by']);
            $table->dropColumn(['reregistered_at', 'reregistered_by']);
        });
    }
};
