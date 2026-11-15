<?php

namespace Tests\Feature;

use App\Http\Controllers\Portal\ParticipantController;
use App\Models\Participant;
use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class ParticipantPortalControllerTest extends TestCase
{
    use BuildsAsicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_participant_portal_pages_are_controller_routes_and_only_show_owned_records(): void
    {
        $own = $this->registration('own', [], ['first_name' => 'Peserta', 'last_name' => 'Milik Saya']);
        $this->registration('foreign', [], ['first_name' => 'PRIVATE', 'last_name' => 'FOREIGN']);
        $participant = $this->user(Role::ROLE_PESERTA, 'own');

        $pageNames = [
            'portal.participant.dashboard', 'portal.participant.registration', 'portal.participant.abstract',
            'portal.participant.payment.checkout', 'portal.participant.payment.status', 'portal.participant.documents',
            'portal.participant.attendance', 'portal.participant.full-paper', 'portal.participant.profile',
            'portal.participant.refund', 'portal.participant.notifications', 'portal.participant.certificate',
        ];
        $routes = collect(app('router')->getRoutes())->keyBy(fn (RoutingRoute $route) => $route->getName());
        foreach ($routes->filter(fn (RoutingRoute $route) => str_starts_with((string) $route->getName(), 'portal.participant.')) as $route) {
            $this->assertStringStartsWith(ParticipantController::class.'@', $route->getActionName(), (string) $route->getName());
        }
        foreach ($pageNames as $name) {
            $route = $routes->get($name);
            $this->assertNotNull($route, $name);
            $this->assertStringStartsWith(ParticipantController::class.'@', $route->getActionName(), $name);
            $response = $this->actingAs($participant, 'sanctum')->get(route($name));
            $response->assertOk()->assertDontSee('PRIVATE FOREIGN');
        }

        $this->actingAs($participant, 'sanctum')->getJson('/api/registrations')
            ->assertOk()->assertJsonFragment(['id_registration' => $own->id_registration])
            ->assertJsonMissing(['id_registration' => $own->id_registration + 1]);
    }

    public function test_other_roles_cannot_open_participant_portal_pages(): void
    {
        foreach ([Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA, Role::ROLE_TIM_ABSTRAK, Role::ROLE_ADMIN] as $role) {
            $this->actingAs($this->user($role), 'sanctum')
                ->get(route('portal.participant.dashboard'))->assertForbidden();
        }
    }

    public function test_profile_and_password_endpoints_update_only_the_authenticated_participant(): void
    {
        $own = $this->registration('profile-own');
        $this->registration('profile-other');
        $user = $this->user(Role::ROLE_PESERTA, 'profile-own');

        $this->actingAs($user, 'sanctum')->putJson('/api/me/profile', [
            'first_name' => 'Updated', 'last_name' => 'Participant', 'institution' => 'Updated Institute',
        ])->assertOk()->assertJsonPath('data.participant.first_name', 'Updated');
        $this->assertSame('Updated', Participant::where('id_user', $user->id_user)->firstOrFail()->first_name);
        $this->actingAs($user, 'sanctum')->putJson('/api/me/password', [
            'current_password' => 'secret-pass-123', 'password' => 'NewSecret123!', 'password_confirmation' => 'NewSecret123!',
        ])->assertOk();
        $this->assertTrue(Hash::check('NewSecret123!', $user->fresh()->password));
    }

    public function test_profile_requests_validate_input_and_require_participant_permission(): void
    {
        $this->registration('profile-guard');
        $participant = $this->user(Role::ROLE_PESERTA, 'profile-guard');
        $this->actingAs($participant, 'sanctum')->putJson('/api/me/profile', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name']);

        $this->actingAs($this->user(Role::ROLE_SEKRETARIAT), 'sanctum')
            ->putJson('/api/me/profile', ['first_name' => 'No Access'])->assertForbidden();
    }

    public function test_participant_can_open_account_registration_and_event_registration_uses_public_fee_data(): void
    {
        $fee = $this->fee();
        $this->get(route('auth.participant.register'))->assertOk()->assertSee(route('api.register'));
        $this->get(route('portal.landing'))->assertOk()->assertSee(route('auth.participant.register'));
        $this->getJson('/api/public/registration-fees')->assertOk()->assertJsonFragment(['id_fee' => $fee->id_fee]);

        $this->as(Role::ROLE_PESERTA)->get(route('portal.participant.registration'))
            ->assertOk()->assertSee('/api/public/registration-fees');
    }
}
