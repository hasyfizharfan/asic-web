<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Logo penyelenggara, pendukung, sponsor, dan mitra yang tampil di website publik (diisi panitia). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_sponsor', function (Blueprint $table) {
            $table->increments('id_sponsor');
            $table->unsignedInteger('id_event');
            $table->string('nama', 150);
            $table->string('kategori', 30)->default('Sponsor');
            $table->string('tingkat', 50)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('status', 20)->default('Draft');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->foreign('id_event')->references('id_event')->on('event')->onUpdate('cascade')->onDelete('cascade');
            $table->index(['id_event', 'status', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_sponsor');
    }
};
