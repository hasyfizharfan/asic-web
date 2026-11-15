<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class PortalAuthenticationTest extends TestCase
{
    use BuildsAsicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_login_returns_the_real_portal_url_for_each_role(): void
    {
        $portals = [
            Role::ROLE_PESERTA => route('portal.participant.dashboard'),
            Role::ROLE_SEKRETARIAT => route('portal.secretariat.dashboard'),
            Role::ROLE_BENDAHARA => route('portal.treasurer.dashboard'),
            Role::ROLE_TIM_ABSTRAK => route('portal.reviewer.dashboard'),
            Role::ROLE_ADMIN => route('portal.admin.dashboard'),
        ];

        foreach ($portals as $role => $redirect) {
            $user = $this->user($role, '.portal-login');

            $this->withHeaders([
                'Origin' => 'http://127.0.0.1:8000',
                'Referer' => 'http://127.0.0.1:8000/login',
            ])->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'secret-pass-123',
            ])->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.redirect_url', $redirect);
        }
    }

    public function test_each_portal_requires_authentication_and_its_permission(): void
    {
        $portals = [
            Role::ROLE_PESERTA => '/participant/dashboard',
            Role::ROLE_SEKRETARIAT => '/secretariat/dashboard',
            Role::ROLE_BENDAHARA => '/treasurer/dashboard',
            Role::ROLE_TIM_ABSTRAK => '/reviewer/dashboard',
            Role::ROLE_ADMIN => '/admin/dashboard',
        ];

        foreach ($portals as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }

        foreach ($portals as $role => $uri) {
            $this->actingAs($this->user($role, '.portal-guard'), 'sanctum')
                ->get($uri)
                ->assertOk();

            foreach ($portals as $otherRole => $otherUri) {
                if ($otherRole === $role) {
                    continue;
                }

                $this->get($otherUri)->assertForbidden();
            }
        }
    }

    public function test_logout_clears_the_session_and_portal_access(): void
    {
        $user = $this->user(Role::ROLE_ADMIN, '.logout');
        $origin = [
            'Origin' => 'http://127.0.0.1:8000',
            'Referer' => 'http://127.0.0.1:8000/admin/login',
        ];

        $this->withHeaders($origin)->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-pass-123',
        ])->assertOk();

        $this->withHeaders($origin)->postJson('/api/logout')->assertOk()->assertJsonPath('success', true);
        $this->app['auth']->forgetGuards();
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_password_reset_url_renders_the_reset_form(): void
    {
        $this->get('/password/reset/sample-token?email=demo%40example.test')
            ->assertOk()
            ->assertSee('name="token"', false)
            ->assertSee('demo@example.test')
            ->assertSee(route('api.password.update'), false);
    }

    public function test_public_landing_and_role_login_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee($this->event->nama_event);

        foreach (['participant', 'secretariat', 'treasurer', 'reviewer', 'admin'] as $portal) {
            $this->get('/'.$portal.'/login')
                ->assertOk()
                ->assertSee(route('api.login'), false);
        }
    }
}
