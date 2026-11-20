<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1: konten publik terkelola (KF-01, 04, 05, 07, 28, 33, 34) dan syarat/privasi (KF-35).
 * Semua tabel baru; tidak ada tabel lama yang diubah.
 */
return new class extends Migration
{
    private function publishable(Blueprint $table): void
    {
        $table->enum('status', ['Draft', 'Dipublikasikan'])->default('Draft');
        $table->unsignedInteger('urutan')->default(0);
        $table->timestamps();
    }

    private function eventFk(Blueprint $table, bool $nullable = false): void
    {
        $column = $table->unsignedInteger('id_event');
        if ($nullable) {
            $column->nullable();
        }
        $table->foreign('id_event')->references('id_event')->on('event')->cascadeOnDelete();
    }

    public function up(): void
    {
        Schema::create('event_content', function (Blueprint $table) {
            $table->increments('id_content');
            $this->eventFk($table);
            $table->string('section', 40);
            $table->string('judul_id', 200)->nullable();
            $table->string('judul_en', 200)->nullable();
            $table->text('isi_id')->nullable();
            $table->text('isi_en')->nullable();
            $table->string('tautan', 500)->nullable();
            $this->publishable($table);
            $table->index(['id_event', 'section', 'status']);
        });

        Schema::create('important_date', function (Blueprint $table) {
            $table->increments('id_date');
            $this->eventFk($table);
            $table->string('label_id', 200)->nullable();
            $table->string('label_en', 200)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $this->publishable($table);
        });

        Schema::create('faq', function (Blueprint $table) {
            $table->increments('id_faq');
            $this->eventFk($table, true);
            $table->string('pertanyaan_id', 300)->nullable();
            $table->string('pertanyaan_en', 300)->nullable();
            $table->text('jawaban_id')->nullable();
            $table->text('jawaban_en')->nullable();
            $table->string('kategori', 60)->nullable();
            $this->publishable($table);
        });

        Schema::create('contact_person', function (Blueprint $table) {
            $table->increments('id_contact');
            $this->eventFk($table, true);
            $table->string('nama', 150);
            $table->string('peran_id', 150)->nullable();
            $table->string('peran_en', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $this->publishable($table);
        });

        Schema::create('event_photo', function (Blueprint $table) {
            $table->increments('id_photo');
            $this->eventFk($table);
            $table->string('file_path', 255);
            $table->string('caption_id', 300)->nullable();
            $table->string('caption_en', 300)->nullable();
            $this->publishable($table);
        });

        Schema::create('sponsorship_package', function (Blueprint $table) {
            $table->increments('id_package');
            $this->eventFk($table);
            $table->string('nama', 150);
            $table->text('deskripsi_id')->nullable();
            $table->text('deskripsi_en')->nullable();
            $table->text('manfaat_id')->nullable();
            $table->text('manfaat_en')->nullable();
            $table->string('harga_teks', 150)->nullable();
            $table->unsignedInteger('kuota')->nullable();
            $this->publishable($table);
        });

        Schema::create('sponsorship_inquiry', function (Blueprint $table) {
            $table->increments('id_inquiry');
            $this->eventFk($table);
            $table->unsignedInteger('id_package')->nullable();
            $table->foreign('id_package')->references('id_package')->on('sponsorship_package')->nullOnDelete();
            $table->enum('jenis', ['Sponsorship', 'Exhibition']);
            $table->string('nama_organisasi', 150);
            $table->string('nama_kontak', 150);
            $table->string('email', 150);
            $table->string('telepon', 30)->nullable();
            $table->text('pesan');
            $table->text('catatan_internal')->nullable();
            $table->enum('status', ['Baru', 'Dihubungi', 'Selesai', 'Ditolak'])->default('Baru');
            $table->timestamps();
        });

        Schema::create('legal_document', function (Blueprint $table) {
            $table->increments('id_legal');
            $table->enum('jenis', ['terms', 'privacy', 'ethics']);
            $table->string('versi', 20);
            $table->string('judul_id', 200)->nullable();
            $table->string('judul_en', 200)->nullable();
            $table->longText('isi_id')->nullable();
            $table->longText('isi_en')->nullable();
            $table->date('berlaku_mulai');
            $table->enum('status', ['Draft', 'Dipublikasikan', 'Diarsipkan'])->default('Draft');
            $table->timestamps();
            $table->unique(['jenis', 'versi']);
        });

        Schema::create('user_consent', function (Blueprint $table) {
            $table->increments('id_consent');
            $table->unsignedInteger('id_user');
            $table->foreign('id_user')->references('id_user')->on('user_account')->cascadeOnDelete();
            $table->unsignedInteger('id_legal_document')->nullable();
            $table->foreign('id_legal_document')->references('id_legal')->on('legal_document')->nullOnDelete();
            $table->string('jenis', 20);
            $table->string('versi', 20)->nullable();
            $table->dateTime('accepted_at');
            $table->string('ip_address', 45)->nullable();
        });
    }

    public function down(): void
    {
        foreach (['user_consent', 'legal_document', 'sponsorship_inquiry', 'sponsorship_package', 'event_photo', 'contact_person', 'faq', 'important_date', 'event_content'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
