<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_session', function (Blueprint $table) {
            $table->dateTime('waktu_selesai')->nullable()->after('waktu');
            $table->string('qr_token', 100)->nullable()->unique()->after('waktu_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('event_session', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn(['waktu_selesai', 'qr_token']);
        });
    }
};
