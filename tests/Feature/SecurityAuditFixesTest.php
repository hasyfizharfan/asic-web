<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Support\StatusLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Temuan audit QA 18 Nov 2026 (BUG-028 s.d. BUG-034 di Log Bug). */
class SecurityAuditFixesTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    public function test_login_is_rate_limited_per_email(): void
    {
        $this->user(Role::ROLE_PESERTA, '.target');
        for ($i = 0; $i < 5; $i++) {
            $this->assertNotSame(429, $this->postJson('/api/login', ['email' => 'peserta.target@example.test', 'password' => 'salah-'.$i])->status());
        }
        $this->postJson('/api/login', ['email' => 'peserta.target@example.test', 'password' => 'salah-lagi'])
            ->assertStatus(429)->assertJsonPath('success', false)->assertHeader('Retry-After');
    }

    public function test_forgot_username_does_not_reveal_whether_accounts_exist(): void
    {
        $this->user(Role::ROLE_PESERTA, '.ada');
        $found = $this->postJson('/api/password/forgot-username', ['query' => 'peserta.ada'])->assertOk()->json();
        $none = $this->postJson('/api/password/forgot-username', ['query' => 'tidak-ada-sama-sekali'])->assertOk()->json();
        $this->assertSame($found, $none);
        $this->assertArrayNotHasKey('count', $found);
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=(self)', $this->get('/')->headers->get('Permissions-Policy'));
    }

    public function test_participant_cannot_read_attendance_recap(): void
    {
        $this->as(Role::ROLE_PESERTA, '1')->getJson('/api/recap/attendance')->assertForbidden();
        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/recap/attendance')->assertOk();
    }

    public function test_api_docs_hidden_in_production(): void
    {
        $this->app['env'] = 'production';
        $this->get('/api/documentation')->assertNotFound();
        $this->get('/docs')->assertNotFound();
    }

    public function test_database_statuses_are_translated_for_english(): void
    {
        app()->setLocale('en');
        $this->assertSame('Awaiting payment', StatusLabel::of('Menunggu Pembayaran'));
        $this->assertSame('Accepted', StatusLabel::of('Diterima'));
        $this->assertSame('Sesuatu Lain', StatusLabel::of('Sesuatu Lain'));
        app()->setLocale('id');
        $this->assertSame('Menunggu Pembayaran', StatusLabel::of('Menunggu Pembayaran'));
    }
}
