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
            // Lepas foreign key sebelum mengubah unique index yang menopangnya.
            $table->dropForeign(['id_registration']);
            $table->dropUnique('attendance_id_registration_unique');

            // Pertahankan relasi registration tanpa unique constraint tunggal.
            $table->foreign('id_registration')
                ->references('id_registration')
                ->on('registration')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            // Hapus index dan kolom QR lama.
            $table->dropUnique('attendance_qr_code_unique');
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

            // Lepas foreign key sebelum mengembalikan unique index satu registration.
            $table->dropForeign(['id_registration']);
            $table->dropColumn(['id_session', 'status_presentasi']);

            $table->string('qr_code', 255)->unique()->after('id_registration');
            $table->unique('id_registration', 'attendance_id_registration_unique');

            $table->foreign('id_registration')
                ->references('id_registration')
                ->on('registration')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }
};

