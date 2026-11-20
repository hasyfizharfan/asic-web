<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class ForbiddenPageTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_browser_request_to_other_role_page_shows_friendly_forbidden_page(): void
    {
        $this->as(Role::ROLE_PESERTA, '1')->withSession(['locale' => 'id'])->get('/treasurer/payments')
            ->assertForbidden()
            ->assertSee('Akses ditolak')
            ->assertDontSee('"success":false', false);
    }

    public function test_api_request_still_gets_json_forbidden(): void
    {
        $this->as(Role::ROLE_PESERTA, '1')->getJson('/treasurer/payments')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
