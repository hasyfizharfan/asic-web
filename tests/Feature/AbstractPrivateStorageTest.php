<?php

namespace Tests\Feature;

use App\Models\AbstractSubmission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class AbstractPrivateStorageTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_abstract_upload_and_download_use_private_storage_and_public_url_is_unavailable(): void
    {
        $registration = $this->registration('private');
        $response = $this->as(Role::ROLE_PESERTA, 'private')->postJson("/api/registrations/{$registration->id_registration}/abstract", [
            'judul_abstrak' => 'Private uploaded abstract', 'scope' => 'CP', 'metode_presentasi' => 'Oral',
            'file_abstrak' => UploadedFile::fake()->create('author-name.pdf', 50, 'application/pdf'),
        ])->assertCreated();

        $abstract = AbstractSubmission::findOrFail($response->json('data.id_abstract'));
        Storage::disk('local')->assertExists($abstract->file_abstrak);
        Storage::disk('public')->assertMissing($abstract->file_abstrak);
        $response->assertJsonPath('file_url', route('api.registrations.abstract.download', $registration->id_registration));
        $this->get('/storage/'.$abstract->file_abstrak)->assertForbidden();

        $this->as(Role::ROLE_PESERTA, 'private')->get("/api/registrations/{$registration->id_registration}/abstract/download")
            ->assertOk()->assertHeader('content-disposition', 'attachment; filename=abstract-'.$abstract->id_abstract.'.pdf');
        $reviewerDownload = $this->as(Role::ROLE_TIM_ABSTRAK)->get("/api/tim-abstrak/submissions/{$abstract->id_abstract}/file");
        $this->assertSame(200, $reviewerDownload->getStatusCode(), $reviewerDownload->getContent());
        $reviewerDownload->assertHeader('content-disposition', 'attachment; filename=abstract-'.$abstract->id_abstract.'.pdf');
        $this->as(Role::ROLE_PESERTA, 'another')->get("/api/registrations/{$registration->id_registration}/abstract/download")->assertForbidden();
    }

    public function test_migration_command_moves_legacy_public_abstract_files(): void
    {
        $abstract = $this->abstractFor($this->registration('legacy'));
        $path = 'abstracts/legacy-abstract.pdf';
        Storage::disk('public')->put($path, '%PDF-legacy');
        $abstract->update(['file_abstrak' => $path]);

        $this->artisan('abstracts:migrate-public-files-to-private')->assertSuccessful();

        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $this->as(Role::ROLE_TIM_ABSTRAK)->get("/api/tim-abstrak/submissions/{$abstract->id_abstract}/file")->assertOk();
    }
}
