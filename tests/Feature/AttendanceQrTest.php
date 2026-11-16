<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\Role;
use App\Services\InvoiceIssuer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class AttendanceQrTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        $this->travelTo(now()->setDate(2027, 8, 2)->setTime(9, 30));
    }

    public function test_online_participant_can_submit_active_session_qr_on_second_event_day_once(): void
    {
        $registration = $this->verifiedOnlineRegistration();
        $session = $this->createSession('2027-08-02 09:00:00', '2027-08-02 10:00:00');

        $this->as(Role::ROLE_PESERTA, '1')->postJson('/api/attendance/scan', [
            'qr_token' => $session->qr_token,
        ])->assertOk()->assertJsonPath('data.id_session', $session->id_session);

        $this->assertSame(1, Attendance::where('id_registration', $registration->id_registration)
            ->where('id_session', $session->id_session)->count());

        $this->as(Role::ROLE_PESERTA, '1')->postJson('/api/attendance/scan', [
            'qr_token' => $session->qr_token,
        ])->assertConflict();
    }

    /**
     * KF-13 (Fase 2): QR kehadiran pribadi hanya untuk peserta Luring. Peserta Daring yang
     * Terverifikasi tetap dapat hadir lewat QR sesi (BUG-013 tetap teratasi).
     */
    public function test_online_participant_gets_no_personal_qr_but_can_attend_with_session_qr(): void
    {
        $registration = $this->verifiedOnlineRegistration();

        $this->as(Role::ROLE_PESERTA, '1')
            ->getJson("/api/registrations/{$registration->id_registration}/attendance-qr")
            ->assertForbidden();

        $session = $this->createSession(now()->subMinutes(10)->toDateTimeString(), now()->addHour()->toDateTimeString());
        $this->as(Role::ROLE_PESERTA, '1')->postJson('/api/attendance/scan', ['qr_token' => $session->qr_token])->assertOk();
    }

    public function test_session_qr_scan_rejects_a_token_for_another_session(): void
    {
        $this->verifiedOnlineRegistration();
        $session = $this->createSession('2027-08-02 09:00:00', '2027-08-02 10:00:00');
        $otherSession = $this->createSession('2027-08-02 09:00:00', '2027-08-02 10:00:00');

        $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/sessions/{$session->id_session}/attendance", [
            'id_registration' => Registration::firstOrFail()->id_registration,
            'qr_token' => $otherSession->qr_token,
        ])->assertForbidden();
    }

    private function verifiedOnlineRegistration(): Registration
    {
        $this->fee(['attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0]);
        $registration = $this->registration('1', [
            'attendance_mode' => 'Daring',
            'participation_type' => 'Listener',
            'publication_option' => 'Tidak',
        ]);

        $result = app(InvoiceIssuer::class)->issueFor($registration);
        $this->assertNotNull($result['invoice'], $result['reason'] ?? 'Invoice was not issued.');
        $invoice = $result['invoice'];
        $invoice->update(['status_pembayaran' => 'Terverifikasi']);

        return $registration;
    }

    private function createSession(string $start, string $end): EventSession
    {
        return EventSession::create([
            'id_event' => $this->event->id_event,
            'nama_sesi' => 'Day 2 Session',
            'waktu' => $start,
            'waktu_selesai' => $end,
            'qr_token' => 'ASIC-SESSION-'.$this->event->id_event.'-'.uniqid(),
        ]);
    }
}
