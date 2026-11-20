<?php

namespace App\Support;

/** Aturan bersama: kapan email sistem boleh dikirim ke sebuah alamat. */
class MailGuard
{
    public static function allows(?string $email): bool
    {
        $email = trim((string) $email);
        if (! config('asic.notify_email', true) || $email === '') {
            return false;
        }

        // Domain cadangan untuk uji (RFC 2606/6761) tidak ada di internet: jangan dikirimi email.
        return ! (config('asic.skip_reserved_email_domains', true)
            && preg_match('/\.(test|example|invalid|localhost)$/i', $email));
    }

    /** Bahasa email peserta: Indonesia = id, negara lain = en. */
    public static function localeForCountry(?string $country): string
    {
        return strcasecmp(trim((string) $country), 'Indonesia') === 0 ? 'id' : 'en';
    }
}
