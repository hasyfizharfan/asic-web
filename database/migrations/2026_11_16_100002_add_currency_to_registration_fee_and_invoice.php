<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Dukungan mata uang IDR/USD. Data lama otomatis IDR (default), tidak ada baris yang diubah.
 * Unique index kombinasi tarif hanya dibuat bila data lama tidak memiliki duplikat.
 */
return new class extends Migration
{
    private const UNIQUE = 'registration_fee_combination_currency_unique';

    private const COLUMNS = ['id_event', 'id_period', 'registration_type', 'attendance_mode', 'tujuan_publikasi', 'mata_uang'];

    public function up(): void
    {
        Schema::table('registration_fee', function (Blueprint $table) {
            $table->enum('mata_uang', ['IDR', 'USD'])->default('IDR');
        });
        Schema::table('invoice', function (Blueprint $table) {
            $table->enum('mata_uang', ['IDR', 'USD'])->default('IDR');
        });

        $duplicates = DB::table('registration_fee')
            ->select(self::COLUMNS)
            ->groupBy(self::COLUMNS)
            ->havingRaw('count(*) > 1')
            ->get()
            ->count();

        if ($duplicates === 0) {
            Schema::table('registration_fee', function (Blueprint $table) {
                $table->unique(self::COLUMNS, self::UNIQUE);
            });
        } else {
            Log::warning('registration_fee.duplicate_combinations', ['count' => $duplicates]);
        }
    }

    public function down(): void
    {
        if (collect(Schema::getIndexes('registration_fee'))->contains(fn ($index) => $index['name'] === self::UNIQUE)) {
            Schema::table('registration_fee', function (Blueprint $table) {
                $table->dropUnique(self::UNIQUE);
            });
        }
        Schema::table('registration_fee', function (Blueprint $table) {
            $table->dropColumn('mata_uang');
        });
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropColumn('mata_uang');
        });
    }
};
