<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/** Rentang tanggal ringkas sesuai locale: "9–10 Juni 2027", "30 Mei – 2 Juni 2027", "30 Des 2026 – 2 Jan 2027". */
class DateRange
{
    public static function format(mixed $start, mixed $end = null): string
    {
        if (! $start) {
            return '';
        }
        $a = Carbon::parse($start)->locale(app()->getLocale());
        $b = $end ? Carbon::parse($end)->locale(app()->getLocale()) : null;

        if (! $b || $a->isSameDay($b)) {
            return $a->translatedFormat('j F Y');
        }
        if ($a->isSameMonth($b)) {
            return $a->format('j').'–'.$b->translatedFormat('j F Y');
        }
        if ($a->isSameYear($b)) {
            return $a->translatedFormat('j F').' – '.$b->translatedFormat('j F Y');
        }

        return $a->translatedFormat('j F Y').' – '.$b->translatedFormat('j F Y');
    }
}
