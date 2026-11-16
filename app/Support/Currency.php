<?php

namespace App\Support;

use App\Models\Participant;
use App\Models\Registration;

/**
 * Satu-satunya aturan mata uang peserta: negara Indonesia (atau kosong, karena registrasi akun
 * mengisi 'Indonesia' sebagai bawaan) memakai tarif IDR; negara lain memakai tarif USD.
 */
final class Currency
{
    public const IDR = 'IDR';

    public const USD = 'USD';

    public const ALL = [self::IDR, self::USD];

    private const INDONESIA_ALIASES = ['indonesia', 'id', 'idn', 'republic of indonesia', 'republik indonesia'];

    public static function forCountry(?string $country): string
    {
        $normalized = mb_strtolower(trim((string) $country));

        return $normalized === '' || in_array($normalized, self::INDONESIA_ALIASES, true) ? self::IDR : self::USD;
    }

    public static function forParticipant(?Participant $participant): string
    {
        return self::forCountry($participant?->country);
    }

    public static function forRegistration(Registration $registration): string
    {
        $registration->loadMissing('participant');

        return self::forParticipant($registration->participant);
    }

    public static function normalize(?string $currency): string
    {
        return in_array($currency, self::ALL, true) ? $currency : self::IDR;
    }

    public static function decimals(string $currency): int
    {
        return $currency === self::USD ? 2 : 0;
    }

    /** Format dokumen panitia: 1,000,000 (IDR) atau 1,000.00 (USD). */
    public static function plain(mixed $amount, ?string $currency = self::IDR): string
    {
        return number_format((float) $amount, self::decimals(self::normalize($currency)), '.', ',');
    }

    /** Format tampilan mengikuti locale aktif (id: Rp 1.000.000 / USD 1.000,00; en: IDR 1,000,000 / USD 1,000.00). */
    public static function format(mixed $amount, ?string $currency = self::IDR): string
    {
        $currency = self::normalize($currency);
        $id = app()->getLocale() === 'id';
        $number = number_format((float) $amount, self::decimals($currency), $id ? ',' : '.', $id ? '.' : ',');

        return ($currency === self::IDR && $id ? 'Rp ' : $currency.' ').$number;
    }

    /** Terbilang bahasa Inggris (receipt): "Five million rupiah" / "One hundred US dollars and fifty cents". */
    public static function words(mixed $amount, ?string $currency = self::IDR): string
    {
        $currency = self::normalize($currency);
        $value = round((float) $amount, 2);
        $whole = (int) floor($value);
        $text = ucfirst(self::integerWords($whole)).' '.($currency === self::USD ? 'US dollars' : 'rupiah');
        if ($currency === self::USD) {
            $cents = (int) round(($value - $whole) * 100);
            if ($cents > 0) {
                $text .= ' and '.self::integerWords($cents).' cents';
            }
        }

        return $text;
    }

    private static function integerWords(int $n): string
    {
        if ($n === 0) {
            return 'zero';
        }
        $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve',
            'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        $chunk = function (int $x) use ($ones, $tens): string {
            $w = [];
            if ($x >= 100) {
                $w[] = $ones[intdiv($x, 100)].' hundred';
                $x %= 100;
            }
            if ($x >= 20) {
                $w[] = $tens[intdiv($x, 10)].($x % 10 ? '-'.$ones[$x % 10] : '');
            } elseif ($x > 0) {
                $w[] = $ones[$x];
            }

            return implode(' ', $w);
        };
        $parts = [];
        foreach ([1_000_000_000 => 'billion', 1_000_000 => 'million', 1_000 => 'thousand', 1 => ''] as $size => $label) {
            if ($n >= $size) {
                $parts[] = trim($chunk(intdiv($n, $size)).' '.$label);
                $n %= $size;
            }
        }

        return implode(' ', $parts);
    }
}
