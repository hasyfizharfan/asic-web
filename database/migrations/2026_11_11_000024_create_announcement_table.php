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
        Schema::create('announcement', function (Blueprint $table) {
            $table->increments('id_announcement');
            $table->unsignedInteger('id_event');
            $table->unsignedInteger('id_author');
            $table->string('judul', 150);
            $table->text('isi_pengumuman');
            $table->dateTime('tanggal_publikasi')->nullable();
            $table->enum('status', ['Draft', 'Dipublikasikan'])->default('Draft');

            $table->foreign('id_event')
                ->references('id_event')
                ->on('event')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_author')
                ->references('id_user')
                ->on('user_account')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement');
    }
};

