<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah tabel attendance agar QR Code terhubung ke sesi (event_session),
     * bukan peserta secara global, serta menambahkan kolom status_presentasi (Poin 1).
     */
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Hapus index unique lama pada id_registration dan qr_code
            $table->dropUnique('attendance_id_registration_unique');
            $table->dropUnique('attendance_qr_code_unique');

            // Hapus kolom qr_code lama
            $table->dropColumn('qr_code');

            // Tambahkan relasi ke event_session
            $table->unsignedInteger('id_session')->after('id_registration');
            $table->foreign('id_session')
                ->references('id_session')
                ->on('event_session')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Tambahkan kolom status_presentasi
            $table->enum('status_presentasi', ['Belum', 'Sudah'])
                ->default('Belum')
                ->after('status_kehadiran');

            // Peserta unik per sesi kehadiran
            $table->unique(['id_registration', 'id_session'], 'attendance_reg_session_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['id_session']);
            $table->dropUnique('attendance_reg_session_unique');
            $table->dropColumn(['id_session', 'status_presentasi']);

            $table->string('qr_code', 255)->unique()->after('id_registration');
            $table->unique('id_registration', 'attendance_id_registration_unique');
        });
    }
};

