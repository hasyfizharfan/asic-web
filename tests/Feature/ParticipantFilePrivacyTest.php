<?php

namespace Tests\Feature;

use App\Models\ParticipantFile;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class ParticipantFilePrivacyTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_participant_manuscripts_are_private_and_download_checks_ownership(): void
    {
        $registration = $this->registration('private-file');
        $response = $this->as(Role::ROLE_PESERTA, 'private-file')->postJson("/api/registrations/{$registration->id_registration}/files", [
            'jenis_file' => 'Artikel',
            'file' => UploadedFile::fake()->create('full-paper.pdf', 30, 'application/pdf'),
        ])->assertCreated();

        $file = ParticipantFile::findOrFail($response->json('data.id_file'));
        Storage::disk('local')->assertExists($file->file_path);
        Storage::disk('public')->assertMissing($file->file_path);
        $this->assertStringContainsString("/api/participant-files/{$file->id_file}/download", $response->json('data.file_url'));

        $this->as(Role::ROLE_PESERTA, 'private-file')->get("/api/participant-files/{$file->id_file}/download")->assertOk();
        $other = $this->user(Role::ROLE_PESERTA, 'other-owner');
        $this->actingAs($other, 'sanctum')->get("/api/participant-files/{$file->id_file}/download")->assertForbidden();
    }
}
