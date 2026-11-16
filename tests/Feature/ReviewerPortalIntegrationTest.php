<?php

namespace Tests\Feature;

use App\Models\AbstractReview;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class ReviewerPortalIntegrationTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_reviewer_portal_routes_are_controller_backed_and_role_protected(): void
    {
        $routes = [
            'portal.reviewer.dashboard', 'portal.reviewer.queue', 'portal.reviewer.abstracts.show', 'portal.reviewer.profile',
        ];
        foreach ($routes as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertNotNull($route);
            $this->assertIsString($route->getActionName());
            $this->assertStringContainsString('ReviewerController@', $route->getActionName());
        }
        foreach (['/reviewer/dashboard', '/reviewer/queue', '/reviewer/abstracts/1', '/reviewer/profile'] as $uri) {
            $this->as(Role::ROLE_TIM_ABSTRAK)->get($uri)->assertOk();
            $this->as(Role::ROLE_PESERTA)->get($uri)->assertForbidden();
            $this->as(Role::ROLE_BENDAHARA)->get($uri)->assertForbidden();
        }
    }

    public function test_dashboard_contains_reviewer_counts_without_author_or_invoice_data(): void
    {
        $registration = $this->registration('dashboard', [], ['first_name' => 'PrivateAuthor', 'last_name' => 'HiddenName']);
        $this->abstractFor($registration, ['judul_abstrak' => 'Dashboard blinded title']);
        $this->fee();

        $response = $this->as(Role::ROLE_TIM_ABSTRAK)->get('/reviewer/dashboard')->assertOk();
        $response->assertSee('Dashboard blinded title')->assertDontSee('PrivateAuthor')->assertDontSee('HiddenName');
        $response->assertDontSee('pesertadashboard@example.test')->assertDontSee('1500000');
    }

    public function test_api_redacts_author_and_reviewer_identity_only_for_blind_review_role(): void
    {
        $registration = $this->registration('blind', [], ['first_name' => 'BlindFirst', 'last_name' => 'BlindLast', 'institution' => 'Private University', 'country' => 'Private Country']);
        $abstract = $this->abstractFor($registration);
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/review", ['hasil_review' => 'Diterima', 'catatan_review' => 'Catatan keputusan yang cukup panjang.'])->assertOk();

        $blindIndex = $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions')->assertOk();
        $blindShow = $this->as(Role::ROLE_TIM_ABSTRAK)->getJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}")->assertOk();
        foreach (['BlindFirst', 'BlindLast', 'Private University', 'Private Country', 'pesertablind@example.test', 'timabstrak@example.test'] as $identity) {
            $blindIndex->assertDontSee($identity);
            $blindShow->assertDontSee($identity);
        }
        $blindShow->assertJsonPath('data.reviewed_by_me', true)->assertJsonPath('data.reviews.0.hasil_review', 'Diterima');

        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}")
            ->assertOk()->assertSee('BlindFirst')->assertSee('Private University');
    }

    public function test_decision_validation_and_reviewer_file_download_authorization(): void
    {
        $abstract = $this->abstractFor($this->registration('download', [], ['first_name' => 'FileOwnerName']));
        Storage::disk('public')->put($abstract->file_abstrak = 'abstracts/private-original-name.pdf', '%PDF-test');
        $abstract->save();

        $this->as(Role::ROLE_TIM_ABSTRAK)->get("/api/tim-abstrak/submissions/{$abstract->id_abstract}/file")
            ->assertOk()->assertHeader('content-disposition', 'attachment; filename=abstract-'.$abstract->id_abstract.'.pdf')
            ->assertDontSee('FileOwnerName');
        $this->as(Role::ROLE_PESERTA, 'download')->get("/api/tim-abstrak/submissions/{$abstract->id_abstract}/file")->assertForbidden();

        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/review", ['hasil_review' => 'Ditolak'])
            ->assertUnprocessable()->assertJsonValidationErrors('catatan_review');
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/review", ['hasil_review' => 'Ditolak', 'catatan_review' => 'Tidak sesuai.'])
            ->assertUnprocessable()->assertJsonValidationErrors('catatan_review');
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/review", ['hasil_review' => 'Revisi'])
            ->assertUnprocessable();
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/review", ['hasil_review' => 'Ditolak', 'catatan_review' => 'Catatan penolakan memenuhi batas minimum.'])
            ->assertOk();
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/review", ['hasil_review' => 'Diterima'])
            ->assertStatus(409);
        $this->assertSame(1, AbstractReview::where('id_abstract', $abstract->id_abstract)->count());
    }

    public function test_queue_search_pagination_and_stats_cover_all_rows(): void
    {
        $match = null;
        for ($i = 1; $i <= 17; $i++) {
            $abstract = $this->abstractFor($this->registration('row'.$i), ['judul_abstrak' => $i === 17 ? 'Needle title' : 'Queue title '.$i, 'scope' => 'CP']);
            if ($i === 1) {
                $match = $abstract;
                $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/review", ['hasil_review' => 'Diterima'])->assertOk();
            }
        }

        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?per_page=15&page=1')->assertOk()
            ->assertJsonPath('data.total', 17)->assertJsonPath('data.last_page', 2)->assertJsonPath('stats.total', 17)
            ->assertJsonPath('stats.pending', 16)->assertJsonPath('stats.reviewed_by_me', 1);
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?per_page=15&page=2')->assertOk()
            ->assertJsonPath('data.current_page', 2)->assertJsonCount(2, 'data.data');
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?search=Needle')->assertOk()
            ->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id_abstract', $abstract->id_abstract);
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?reviewed_by_me=true')->assertOk()
            ->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.reviewed_by_me', true);
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?reviewed_by_me=false')->assertOk()->assertJsonPath('data.total', 16);
    }
}
