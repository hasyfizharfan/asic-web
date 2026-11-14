<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration', function (Blueprint $table) {
            $table->string('qr_attendance_token', 64)->nullable()->unique()->after('registration_status');
        });
    }

    public function down(): void
    {
        Schema::table('registration', function (Blueprint $table) {
            $table->dropUnique(['qr_attendance_token']);
            $table->dropColumn('qr_attendance_token');
        });
    }
};
