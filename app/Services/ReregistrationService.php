<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Models\UserAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * KF-12: registrasi ulang peserta Luring berpembayaran Terverifikasi, hanya pada hari pertama kegiatan.
 * Berbeda dengan kehadiran sesi (KF-14) yang dicatat per sesi di AttendanceController.
 */
class ReregistrationService
{
    /**
     * @return array{status: int, message: string, registration: Registration|null}
     */
    public function register(Event $event, Registration $registration, UserAccount $actor): array
    {
        $registration->loadMissing('invoice');

        $reject = fn (int $status, string $key) => ['status' => $status, 'message' => __('asic-api.reregistration.'.$key), 'registration' => $registration];

        if ((int) $registration->id_event !== (int) $event->id_event) {
            return $reject(Response::HTTP_UNPROCESSABLE_ENTITY, 'event_mismatch');
        }
        if ($registration->registration_status !== 'Terdaftar') {
            return $reject(Response::HTTP_FORBIDDEN, 'not_registered');
        }
        if ($registration->attendance_mode !== 'Luring') {
            return $reject(Response::HTTP_UNPROCESSABLE_ENTITY, 'online_rejected');
        }
        if ($registration->invoice?->status_pembayaran !== 'Terverifikasi') {
            return $reject(Response::HTTP_FORBIDDEN, 'payment_unverified');
        }
        if ($registration->reregistered_at !== null) {
            return $reject(Response::HTTP_CONFLICT, 'already');
        }
        if (! $this->isFirstDay($event)) {
            return $reject(Response::HTTP_FORBIDDEN, 'first_day_only');
        }

        $updated = DB::transaction(function () use ($registration, $actor): bool {
            $locked = Registration::whereKey($registration->id_registration)->lockForUpdate()->firstOrFail();
            if ($locked->reregistered_at !== null) {
                return false;
            }
            $locked->forceFill(['reregistered_at' => now(), 'reregistered_by' => $actor->id_user])->save();

            return true;
        });

        $registration->refresh();
        if ($updated) {
            app(StatusNotifier::class)->notify($registration, 'reregistered', 'reregistered:'.$registration->id_registration);
        }

        return $updated
            ? ['status' => Response::HTTP_OK, 'message' => __('asic-api.reregistration.success'), 'registration' => $registration]
            : $reject(Response::HTTP_CONFLICT, 'already');
    }

    public function isFirstDay(Event $event): bool
    {
        return $event->tanggal_mulai !== null
            && now()->toDateString() === Carbon::parse($event->tanggal_mulai)->toDateString();
    }
}
