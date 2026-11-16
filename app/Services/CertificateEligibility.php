<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Registration;
use Illuminate\Support\Carbon;

/**
 * KF-27: satu-satunya sumber kebenaran syarat sertifikat (dipakai API dan UI).
 * Presenter: hadir (min. N sesi) DAN status presentasi Sudah. Listener: hadir (min. N sesi).
 * Semua: pendaftaran Terdaftar. Opsi: kegiatan harus sudah selesai.
 */
class CertificateEligibility
{
    /**
     * @return array{eligible: bool, reasons: list<string>, checks: list<array{key: string, ok: bool, label: string}>}
     */
    public function evaluate(Registration $registration): array
    {
        $min = max(1, (int) config('asic.certificate.min_attendance_sessions', 1));
        $attended = Attendance::where('id_registration', $registration->id_registration)->where('status_kehadiran', 'Hadir');

        $checks = [
            'registered' => $registration->registration_status === 'Terdaftar',
            'attendance' => $attended->count() >= $min,
        ];
        if ($registration->isPresenter()) {
            $checks['presented'] = (clone $attended)->where('status_presentasi', 'Sudah')->exists();
        }
        if (config('asic.certificate.require_event_ended', false)) {
            $end = $registration->event?->tanggal_selesai;
            $checks['event_ended'] = $end !== null && Carbon::parse($end)->endOfDay()->isPast();
        }

        $params = ['count' => $min];

        return [
            'eligible' => ! in_array(false, $checks, true),
            'reasons' => collect($checks)->filter(fn ($ok) => ! $ok)->keys()->map(fn ($key) => __('asic-documents.certificate.reasons.'.$key, $params))->values()->all(),
            'checks' => collect($checks)->map(fn ($ok, $key) => ['key' => $key, 'ok' => $ok, 'label' => __('asic-documents.certificate.checks.'.$key, $params)])->values()->all(),
        ];
    }
}
