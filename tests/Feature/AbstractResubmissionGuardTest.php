<?php

namespace Tests\Feature;

use App\Models\AbstractSubmission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Abstrak yang sudah diputuskan Tim Abstrak tidak boleh dikirim ulang (status tidak boleh kembali ke Diajukan). */
class AbstractResubmissionGuardTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Storage::fake('local');
        Mail::fake();
    }

    private function submit(int $idRegistration)
    {
        return $this->as(Role::ROLE_PESERTA, '1')->postJson("/api/registrations/{$idRegistration}/abstract", [
            'judul_abstrak' => 'Judul revisi', 'scope' => 'CP', 'metode_presentasi' => 'Oral',
            'file_abstrak' => UploadedFile::fake()->create('abstrak.pdf', 50, 'application/pdf'),
        ]);
    }

    public function test_participant_can_replace_abstract_while_still_under_review(): void
    {
        $reg = $this->registration('1');
        $this->submit($reg->id_registration)->assertCreated();
        $this->submit($reg->id_registration)->assertCreated();
        $this->assertSame(1, AbstractSubmission::where('id_registration', $reg->id_registration)->count());
    }

    public function test_decided_abstract_cannot_be_resubmitted(): void
    {
        foreach (['Diterima', 'Ditolak'] as $i => $status) {
            $reg = $this->registration((string) ($i + 1));
            $abstract = $this->abstractFor($reg);
            $abstract->update(['status_abstrak' => $status]);
            $this->as(Role::ROLE_PESERTA, (string) ($i + 1))->postJson("/api/registrations/{$reg->id_registration}/abstract", [
                'judul_abstrak' => 'Coba kirim ulang', 'scope' => 'CP', 'metode_presentasi' => 'Oral',
                'file_abstrak' => UploadedFile::fake()->create('abstrak.pdf', 50, 'application/pdf'),
            ])->assertStatus(409);
            $this->assertSame($status, $abstract->fresh()->status_abstrak);
        }
    }
}
