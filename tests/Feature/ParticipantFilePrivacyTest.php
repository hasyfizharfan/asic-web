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
        $this->abstractFor($registration, ['status_abstrak' => 'Diterima']);
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

    public function test_listener_and_unaccepted_presenter_cannot_upload_full_paper(): void
    {
        $listener = $this->registration('file-listener', ['participation_type' => 'Listener']);
        $this->as(Role::ROLE_PESERTA, 'file-listener')->postJson("/api/registrations/{$listener->id_registration}/files", [
            'jenis_file' => 'Artikel', 'file' => UploadedFile::fake()->create('paper.pdf', 30, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonPath('message', __('asic-api.messages.files_presenter_only'));

        $presenter = $this->registration('file-pending');
        $this->abstractFor($presenter);
        $this->as(Role::ROLE_PESERTA, 'file-pending')->postJson("/api/registrations/{$presenter->id_registration}/files", [
            'jenis_file' => 'Presentasi', 'file' => UploadedFile::fake()->create('slides.pdf', 30, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonPath('message', __('asic-api.messages.files_abstract_not_accepted'));

        $this->assertSame(0, ParticipantFile::count());
    }

    public function test_listener_menu_hides_abstract_and_full_paper_and_checkout_accepts_exact_amount(): void
    {
        $this->registration('menu-listener', ['participation_type' => 'Listener']);
        $html = $this->as(Role::ROLE_PESERTA, 'menu-listener')->get(route('portal.participant.dashboard'))->assertOk()->getContent();
        $this->assertStringNotContainsString(route('portal.participant.full-paper'), $html);
        $this->assertStringNotContainsString('href="'.route('portal.participant.abstract').'"', $html);

        $checkout = $this->as(Role::ROLE_PESERTA, 'menu-listener')->get(route('portal.participant.payment.checkout'))->assertOk()->getContent();
        // Nominal hanya ditampilkan (tidak bisa diubah peserta).
        $this->assertStringNotContainsString('name="nominal_transfer"', $checkout);
        $this->assertStringContainsString(__('asic-participant.portal.checkout.amount_note'), $checkout);
    }
}
