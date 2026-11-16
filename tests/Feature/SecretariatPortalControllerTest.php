<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class SecretariatPortalControllerTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_secretariat_dashboard_uses_database_counts_and_generic_dashboard_redirects(): void
    {
        $registration = $this->registration('secretariat-dashboard');
        $this->abstractFor($registration, ['judul_abstrak' => 'Dashboard Secretariat Abstract']);

        $this->as(Role::ROLE_SEKRETARIAT)->get('/secretariat/dashboard')->assertOk()
            ->assertSee('Dashboard Secretariat Abstract')->assertSee('Terdaftar')->assertSee('Menunggu putusan Tim Abstrak');
        $this->as(Role::ROLE_SEKRETARIAT)->get('/portal')->assertRedirect(route('portal.secretariat.dashboard'));
    }

    public function test_secretariat_portal_routes_use_portal_controller_actions(): void
    {
        foreach (['dashboard', 'abstracts.index', 'abstracts.show', 'reregistration', 'attendance', 'recap', 'announcements', 'content'] as $name) {
            $route = app('router')->getRoutes()->getByName('portal.secretariat.'.$name);
            $this->assertNotNull($route, $name);
            $this->assertStringContainsString('SecretariatController@', $route->getActionName(), $name);
        }
    }
}
