<?php

namespace App\Support;

/** Label tampilan untuk nilai status di database (enum berbahasa Indonesia) sesuai bahasa aktif. */
final class StatusLabel
{
    public static function of(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $key = 'asic-common.status_values.'.$value;

        return trans()->has($key) ? (string) __($key) : $value;
    }
}
