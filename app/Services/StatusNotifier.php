<?php

namespace App\Services;

use App\Models\Registration;
use App\Notifications\StatusChanged;
use App\Support\MailGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Satu pintu pengiriman notifikasi status (KF-30).
 * - Dikirim setelah transaksi utama commit (DB::afterCommit). Di situs langsung; email lewat antrean.
 * - Idempoten lewat kunci dedupe per peristiwa: pemicu yang dipanggil ulang tidak menggandakan.
 * - Kegagalan hanya dicatat ke log dan TIDAK menggagalkan proses utama.
 */
class StatusNotifier
{
    private const DOCUMENT_KINDS = [
        'LOA' => 'loa_issued',
        'Invoice' => 'invoice_issued',
        'Receipt' => 'receipt_issued',
        'Penolakan' => 'rejection_issued',
        'Sertifikat' => 'certificate_issued',
    ];

    public static function documentKind(string $jenis): ?string
    {
        return self::DOCUMENT_KINDS[$jenis] ?? null;
    }

    /** @param array<string, scalar|null> $params */
    public function notify(Registration $registration, string $kind, string $dedupe, array $params = []): void
    {
        $id = $registration->id_registration;

        DB::afterCommit(function () use ($id, $kind, $dedupe, $params): void {
            try {
                $registration = Registration::with('participant.userAccount')->find($id);
                $user = $registration?->participant?->userAccount;
                if (! $user) {
                    return;
                }
                if ($user->notifications()->where('data->dedupe', $dedupe)->exists()) {
                    return;
                }
                $locale = MailGuard::localeForCountry($registration->participant?->country);
                $user->notify((new StatusChanged($kind, $id, $this->urlFor($kind, $id), $params, $dedupe))->locale($locale));
            } catch (Throwable $e) {
                Log::warning('notification.failed', ['kind' => $kind, 'id_registration' => $id, 'error' => $e->getMessage()]);
            }
        });
    }

    private function urlFor(string $kind, int $idRegistration): ?string
    {
        $route = match ($kind) {
            'invoice_issued', 'invoice_extended', 'invoice_expired', 'invoice_repriced', 'registration_cancelled', 'payment_invalid' => 'portal.participant.payment.checkout',
            'abstract_accepted', 'abstract_rejected' => 'portal.participant.abstract',
            'reregistered' => 'portal.participant.attendance',
            default => 'portal.participant.documents',
        };

        try {
            return route($route, ['id_registration' => $idRegistration], false);
        } catch (Throwable) {
            return null;
        }
    }
}
