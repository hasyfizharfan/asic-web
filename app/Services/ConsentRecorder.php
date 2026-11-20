<?php

namespace App\Services;

use App\Models\LegalDocument;
use App\Models\UserAccount;
use App\Models\UserConsent;
use Illuminate\Support\Facades\Log;

/**
 * KF-35: mencatat persetujuan syarat, privasi, dan etika saat registrasi akun.
 * Dipanggil di dalam transaksi pembuatan akun; kegagalan membatalkan registrasi.
 */
class ConsentRecorder
{
    public function record(UserAccount $user, ?string $ip): void
    {
        $published = LegalDocument::where('status', 'Dipublikasikan')->get()->keyBy('jenis');
        $anonymized = self::anonymizeIp($ip);

        foreach (LegalDocument::TYPES as $jenis) {
            $doc = $published->get($jenis);
            if (! $doc) {
                Log::warning('consent.legal_document_unpublished', ['jenis' => $jenis, 'id_user' => $user->id_user]);
            }
            UserConsent::create([
                'id_user' => $user->id_user,
                'id_legal_document' => $doc?->id_legal,
                'jenis' => $jenis,
                'versi' => $doc?->versi,
                'accepted_at' => now(),
                'ip_address' => $anonymized,
            ]);
        }
    }

    /** IPv4: oktet terakhir dibuang (x.y.z.0); IPv6: 80 bit terakhir dibuang. Null bila tidak valid. */
    public static function anonymizeIp(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = inet_pton($ip);

            return inet_ntop(substr($packed, 0, 6).str_repeat("\0", 10)) ?: null;
        }

        return null;
    }
}
