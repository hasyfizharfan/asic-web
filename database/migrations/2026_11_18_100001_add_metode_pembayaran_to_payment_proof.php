<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Metode yang dipakai peserta: bank (Mandiri), paypal, wise, flip (kesepakatan mitra 18 Nov 2026). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proof', function (Blueprint $table) {
            $table->string('metode_pembayaran', 20)->nullable()->after('nominal_transfer');
        });
    }

    public function down(): void
    {
        Schema::table('payment_proof', function (Blueprint $table) {
            $table->dropColumn('metode_pembayaran');
        });
    }
};
