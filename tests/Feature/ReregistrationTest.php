<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** KF-12: registrasi ulang luring hanya pada hari pertama kegiatan. */
class ReregistrationTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
        $this->fee(['attendance_mode' => 'Luring', 'tujuan_publikasi' => 'Tidak']);
        $this->fee(['attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak']);
        // Hari pertama kegiatan fixture: 2027-08-01 (zona waktu aplikasi).
        Carbon::setTestNow(Carbon::parse('2027-08-01 09:00:00', config('app.timezone')));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function participant(string $suffix, string $mode = 'Luring', string $payment = 'Terverifikasi'): Registration
    {
        $registration = $this->registration($suffix, [
            'attendance_mode' => $mode, 'participation_type' => 'Listener', 'publication_option' => 'Tidak',
        ]);
        Invoice::where('id_registration', $registration->id_registration)->update(['status_pembayaran' => $payment]);
        $registration->forceFill(['qr_attendance_token' => 'TOKEN-'.$suffix])->save();

        return $registration->fresh();
    }

    private function scan(string $token)
    {
        return $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/events/{$this->event->id_event}/reregistration/scan", ['qr_token' => $token]);
    }

    public function test_offline_verified_participant_reregisters_on_first_day(): void
    {
        $registration = $this->participant('ok');

        $this->scan('TOKEN-ok')->assertOk()->assertJsonPath('data.id_registration', $registration->id_registration);

        $fresh = $registration->fresh();
        $this->assertNotNull($fresh->reregistered_at);
        $this->assertSame($this->user(Role::ROLE_SEKRETARIAT)->id_user, $fresh->reregistered_by);
    }

    public function test_online_participant_is_rejected(): void
    {
        $this->participant('online', 'Daring');

        $this->scan('TOKEN-online')->assertUnprocessable()->assertJsonPath('message', __('asic-api.reregistration.online_rejected'));
    }

    public function test_unverified_payment_is_rejected(): void
    {
        $this->participant('unpaid', 'Luring', 'Menunggu Pembayaran');

        $this->scan('TOKEN-unpaid')->assertForbidden()->assertJsonPath('message', __('asic-api.reregistration.payment_unverified'));
    }

    public function test_second_day_is_rejected(): void
    {
        $registration = $this->participant('day2');
        Carbon::setTestNow(Carbon::parse('2027-08-02 09:00:00', config('app.timezone')));

        $this->scan('TOKEN-day2')->assertForbidden()->assertJsonPath('message', __('asic-api.reregistration.first_day_only'));
        $this->assertNull($registration->fresh()->reregistered_at);
    }

    public function test_double_scan_returns_conflict_without_changing_timestamp(): void
    {
        $registration = $this->participant('twice');
        $this->scan('TOKEN-twice')->assertOk();
        $first = $registration->fresh()->reregistered_at;

        Carbon::setTestNow(Carbon::parse('2027-08-01 10:30:00', config('app.timezone')));
        $this->scan('TOKEN-twice')->assertStatus(409)->assertJsonPath('data.id_registration', $registration->id_registration);

        $this->assertTrue($first->equalTo($registration->fresh()->reregistered_at));
    }

    public function test_manual_reregistration_uses_the_same_rules(): void
    {
        $online = $this->participant('manual-online', 'Daring');
        $ok = $this->participant('manual-ok');
        $url = "/api/events/{$this->event->id_event}/reregistration";

        $this->as(Role::ROLE_SEKRETARIAT)->postJson($url, ['id_registration' => $online->id_registration])->assertUnprocessable();
        $this->as(Role::ROLE_SEKRETARIAT)->postJson($url, ['id_registration' => $ok->id_registration])->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->postJson($url, ['id_registration' => $ok->id_registration])->assertStatus(409);
    }

    public function test_roles_without_permission_are_forbidden(): void
    {
        $this->participant('perm');

        foreach ([Role::ROLE_PESERTA, Role::ROLE_BENDAHARA, Role::ROLE_TIM_ABSTRAK] as $role) {
            $this->as($role, 'perm-'.$role)->postJson("/api/events/{$this->event->id_event}/reregistration/scan", ['qr_token' => 'TOKEN-perm'])->assertForbidden();
        }
    }

    public function test_unknown_token_is_rejected(): void
    {
        $this->scan('TIDAK-ADA')->assertUnprocessable()->assertJsonPath('message', __('asic-api.reregistration.token_invalid'));
    }
}
