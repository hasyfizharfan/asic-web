<?php

namespace Tests\Feature;

use App\Models\ContactPerson;
use App\Models\ImportantDate;
use App\Models\Role;
use App\Support\ParticipantJourney;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Dashboard peserta versi desain Figma: stepper tahapan, langkah berikutnya, tanggal penting, helpdesk. */
class ParticipantDashboardJourneyTest extends TestCase
{
    use BuildsAsicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_presenter_journey_starts_at_abstract_then_selection(): void
    {
        $reg = $this->registration('journey');
        $journey = app(ParticipantJourney::class);

        $result = $journey->evaluate($reg->fresh());
        $this->assertSame('abstract', $result['current']);
        $this->assertSame(['account', 'abstract', 'selection', 'loa_invoice', 'payment', 'verification', 'reregistration', 'seminar', 'certificate'], array_column($result['steps'], 'key'));

        $this->abstractFor($reg);
        $this->assertSame('selection', $journey->evaluate($reg->fresh())['current']);

        $reg->abstractSubmission()->update(['status_abstrak' => 'Ditolak']);
        $steps = collect($journey->evaluate($reg->fresh())['steps'])->keyBy('key');
        $this->assertSame('failed', $steps['selection']['state']);
    }

    public function test_online_listener_has_no_abstract_or_reregistration_steps(): void
    {
        $reg = $this->registration('listener', ['participation_type' => 'Listener', 'attendance_mode' => 'Daring']);
        $keys = array_column(app(ParticipantJourney::class)->evaluate($reg->fresh())['steps'], 'key');

        $this->assertNotContains('abstract', $keys);
        $this->assertNotContains('reregistration', $keys);
        $this->assertContains('registration', $keys);
    }

    public function test_dashboard_shows_stepper_next_action_milestones_and_helpdesk_in_both_languages(): void
    {
        $reg = $this->registration('dash');
        $this->abstractFor($reg);
        ImportantDate::create(['id_event' => $this->event->id_event, 'label_id' => 'Batas kirim abstrak', 'label_en' => 'Abstract deadline', 'tanggal_mulai' => '2027-03-01', 'status' => 'Dipublikasikan', 'urutan' => 1]);
        ContactPerson::create(['id_event' => $this->event->id_event, 'nama' => 'Kontak Contoh', 'peran_id' => 'Sekretariat', 'peran_en' => 'Secretariat', 'email' => 'sekretariat@example.test', 'status' => 'Dipublikasikan', 'urutan' => 1]);
        $user = $this->user(Role::ROLE_PESERTA, 'dash');

        $this->actingAs($user, 'sanctum')->withSession(['locale' => 'id'])->get(route('portal.participant.dashboard'))
            ->assertOk()
            ->assertSee('Langkah berikutnya')
            ->assertSee('Abstrak sedang ditinjau')
            ->assertSee('Smart Irrigation for Paddy')
            ->assertSee('Batas kirim abstrak')
            ->assertSee('Kontak Contoh')
            ->assertSee('aria-current="step"', false);

        $this->actingAs($user, 'sanctum')->withSession(['locale' => 'en'])->get(route('portal.participant.dashboard'))
            ->assertOk()->assertSee('Next action')->assertSee('Abstract deadline');
    }

    public function test_dashboard_without_registration_invites_participant_to_register(): void
    {
        $user = $this->user(Role::ROLE_PESERTA, 'empty');

        $this->actingAs($user, 'sanctum')->get(route('portal.participant.dashboard'))
            ->assertOk()->assertSee(route('portal.participant.registration'), false);
    }
}
