<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Registration;
use App\Services\CertificateEligibility;

/**
 * Tahapan peserta untuk stepper dashboard (desain Figma "Participant Portal").
 * Presenter: akun → abstrak → seleksi → LoA & invoice → bayar → verifikasi → registrasi ulang → seminar → sertifikat.
 * Listener tanpa abstrak/seleksi; registrasi ulang hanya untuk peserta luring.
 */
class ParticipantJourney
{
    public function __construct(private CertificateEligibility $eligibility) {}

    /**
     * @return array{steps: list<array{key: string, state: string}>, current: ?string, cancelled: bool}
     *                                                                                                  state: done | current | failed | upcoming
     */
    public function evaluate(Registration $registration): array
    {
        $invoice = $registration->invoice;
        $payment = $invoice?->status_pembayaran;
        $abstract = $registration->abstractSubmission;

        $done = ['account' => true];
        if ($registration->isPresenter()) {
            $done['abstract'] = $abstract !== null;
            $done['selection'] = $abstract?->status_abstrak === 'Diterima';
        } else {
            $done['registration'] = true;
        }
        $done['loa_invoice'] = $invoice !== null;
        $done['payment'] = in_array($payment, ['Menunggu Verifikasi', 'Terverifikasi'], true);
        $done['verification'] = $payment === 'Terverifikasi';
        $attended = Attendance::where('id_registration', $registration->id_registration)->where('status_kehadiran', 'Hadir')->exists();
        if ($registration->isLuring()) {
            // Sudah tercatat hadir di sesi berarti sudah masuk lokasi, walau registrasi ulang tidak dipindai.
            $done['reregistration'] = $registration->reregistered_at !== null || $attended;
        }
        $done['seminar'] = $attended;
        $done['certificate'] = $done['verification'] && $this->eligibility->evaluate($registration)['eligible'];

        $failed = [
            'selection' => $abstract?->status_abstrak === 'Ditolak',
            'payment' => in_array($payment, ['Kedaluwarsa', 'Kadaluwarsa'], true),
        ];

        $steps = [];
        $current = null;
        foreach ($done as $key => $ok) {
            if ($ok) {
                $state = 'done';
            } elseif ($current === null) {
                $current = $key;
                $state = ($failed[$key] ?? false) ? 'failed' : 'current';
            } else {
                $state = 'upcoming';
            }
            $steps[] = ['key' => $key, 'state' => $state];
        }

        return ['steps' => $steps, 'current' => $current, 'cancelled' => $registration->registration_status === 'Dibatalkan'];
    }
}
