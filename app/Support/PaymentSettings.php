<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * KF-23: informasi pembayaran panitia. Nilai yang diisi Bendahara/Admin (tabel app_setting)
 * menimpa nilai bawaan dari konfigurasi server (config/asic.php, .env).
 */
final class PaymentSettings
{
    public const FIELDS = ['bank_name', 'account_name', 'account_no', 'swift_code', 'branch', 'paypal_account', 'qr_info', 'international_note'];

    private const PREFIX = 'payment.';

    /** @return array<string, string|null> */
    public static function current(): array
    {
        $defaults = (array) config('asic.payment', []) + ['qr_info' => config('asic.payment_qr_info')];
        $stored = [];
        try {
            if (Schema::hasTable('app_setting')) {
                $stored = DB::table('app_setting')->where('key', 'like', self::PREFIX.'%')->pluck('value', 'key')->all();
            }
        } catch (Throwable) {
            $stored = [];
        }

        $out = [];
        foreach (self::FIELDS as $field) {
            $out[$field] = array_key_exists(self::PREFIX.$field, $stored) ? $stored[self::PREFIX.$field] : ($defaults[$field] ?? null);
        }

        return $out;
    }

    /** @param array<string, string|null> $values */
    public static function save(array $values): void
    {
        foreach (array_intersect_key($values, array_flip(self::FIELDS)) as $field => $value) {
            DB::table('app_setting')->updateOrInsert(['key' => self::PREFIX.$field], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
