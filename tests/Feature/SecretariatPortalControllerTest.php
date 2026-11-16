<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\EventSession;
use App\Models\IssuedDocument;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
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

    public function test_secretariat_can_search_abstracts_by_author_but_reviewer_search_remains_blind(): void
    {
        $registration = $this->registration('search-author', [], ['first_name' => 'AuthorNeedle', 'last_name' => 'Writer']);
        $this->abstractFor($registration, ['judul_abstrak' => 'Unrelated title']);

        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/tim-abstrak/submissions?search=AuthorNeedle')->assertOk()
            ->assertJsonPath('data.total', 1)->assertSee('AuthorNeedle');
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?search=AuthorNeedle')->assertOk()
            ->assertJsonPath('data.total', 0)->assertDontSee('AuthorNeedle');

        $this->as(Role::ROLE_SEKRETARIAT)->get('/api/exports/abstracts?status=Diajukan&scope=CP&search=AuthorNeedle')->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_secretariat_can_issue_only_decided_abstract_documents_idempotently(): void
    {
        Mail::fake();
        $pending = $this->abstractFor($this->registration('pending-doc'));
        $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/tim-abstrak/submissions/{$pending->id_abstract}/issue-documents")
            ->assertStatus(409);

        $decided = $this->abstractFor($this->registration('decided-doc'), ['status_abstrak' => 'Diterima']);
        $path = "/api/tim-abstrak/submissions/{$decided->id_abstract}/issue-documents";
        $this->as(Role::ROLE_SEKRETARIAT)->postJson($path)->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->postJson($path)->assertOk();
        $this->assertSame(1, IssuedDocument::where('id_registration', $decided->id_registration)->where('jenis_dokumen', 'LOA')->count());
    }

    public function test_secretariat_views_do_not_contain_known_fixture_content(): void
    {
        foreach (File::allFiles(resource_path('views/secretariat')) as $file) {
            $path = $file->getPathname();
            $contents = file_get_contents($path);
            $this->assertStringNotContainsString('Farhan Harahap', $contents, $path);
            $this->assertStringNotContainsString('ABS-2027-01', $contents, $path);
            $this->assertStringNotContainsString('342 records', $contents, $path);
        }
    }

    public function test_registration_search_returns_status_details_and_requires_manage_registrations(): void
    {
        $registration = $this->registration('lookup-001', [], ['first_name' => 'Lookup', 'last_name' => 'Person']);
        $abstract = $this->abstractFor($registration, ['judul_abstrak' => 'Lookup abstract']);

        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/registrations/search?q=Lookup')->assertOk()
            ->assertJsonPath('data.0.id_registration', $registration->id_registration)
            ->assertJsonPath('data.0.participant.full_name', 'Lookup Person')
            ->assertJsonPath('data.0.abstract.judul_abstrak', $abstract->judul_abstrak)
            ->assertJsonPath('data.0.abstract.status_abstrak', 'Diajukan');
        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/registrations/search?q='.$registration->id_registration)->assertOk()
            ->assertJsonPath('data.0.id_registration', $registration->id_registration);
        $this->as(Role::ROLE_PESERTA)->getJson('/api/registrations/search?q=Lookup')->assertForbidden();
        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/registrations/search?q=Lookup')->assertForbidden();
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/registrations/search?q=Lookup')->assertForbidden();
    }

    public function test_attendance_recap_aggregates_real_check_ins_per_session_and_export_permissions_apply(): void
    {
        $registration = $this->registration('session-recap');
        $session = EventSession::create([
            'id_event' => $this->event->id_event, 'nama_sesi' => 'Recap Session', 'waktu' => now()->subHour(),
            'waktu_selesai' => now()->addHour(), 'qr_token' => 'session-recap-token',
        ]);
        Attendance::create([
            'id_registration' => $registration->id_registration, 'id_session' => $session->id_session,
            'tanggal_generate' => now(), 'tanggal_check_in' => now(), 'status_kehadiran' => 'Hadir', 'status_presentasi' => 'Sudah',
        ]);

        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/recap/attendance?id_event='.$this->event->id_event)->assertOk()
            ->assertJsonPath('data.total_check_ins', 1)
            ->assertJsonPath('data.sessions.0.nama_sesi', 'Recap Session')
            ->assertJsonPath('data.sessions.0.presentations_completed', 1);
        $this->as(Role::ROLE_SEKRETARIAT)->get('/api/exports/recap')->assertOk();
        $this->as(Role::ROLE_TIM_ABSTRAK)->get('/api/exports/abstracts')->assertOk();
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/exports/payments')->assertForbidden();
        $this->as(Role::ROLE_PESERTA)->getJson('/api/exports/participants')->assertForbidden();
    }

    public function test_each_secretariat_portal_route_is_limited_to_authorized_staff(): void
    {
        $names = ['dashboard', 'abstracts.index', 'abstracts.show', 'reregistration', 'attendance', 'recap', 'announcements', 'content'];
        $urls = ['/secretariat/dashboard', '/secretariat/abstracts', '/secretariat/abstracts/1', '/secretariat/reregistration', '/secretariat/attendance', '/secretariat/recap', '/secretariat/announcements', '/secretariat/content'];
        foreach ($names as $index => $name) {
            $route = app('router')->getRoutes()->getByName('portal.secretariat.'.$name);
            $this->assertNotNull($route, $name);
            $this->assertStringContainsString('SecretariatController@', $route->getActionName(), $name);
            $this->as(Role::ROLE_SEKRETARIAT)->get($urls[$index])->assertOk();
            foreach ([Role::ROLE_PESERTA, Role::ROLE_BENDAHARA, Role::ROLE_TIM_ABSTRAK, Role::ROLE_ADMIN] as $role) {
                $this->as($role)->get($urls[$index])->assertForbidden();
            }
        }
    }
}
