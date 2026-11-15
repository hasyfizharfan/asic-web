<?php

namespace Tests\Feature;

use App\Models\DigitalSignature;
use App\Models\Role;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class DigitalSignaturePrivacyTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    public function test_signature_upload_is_private_and_file_endpoint_requires_authentication(): void
    {
        $role = Role::where('nama_role', Role::ROLE_ADMIN)->firstOrFail();
        $response = $this->as(Role::ROLE_ADMIN)->post('/api/digital-signatures', [
            'id_role' => $role->id_role,
            'file_ttd' => UploadedFile::fake()->image('signature.png'),
        ], ['Accept' => 'application/json']);

        $response->assertOk();
        $signature = DigitalSignature::where('id_role', $role->id_role)->firstOrFail();
        Storage::disk('local')->assertExists($signature->file_ttd);
        Storage::disk('public')->assertMissing($signature->file_ttd);

        Auth::forgetGuards();
        $this->getJson($response->json('ttd_url'))->assertUnauthorized();
        $this->as(Role::ROLE_ADMIN)->get($response->json('ttd_url'))->assertOk();
    }

    public function test_legacy_public_signature_is_moved_to_private_disk_when_served(): void
    {
        $role = Role::where('nama_role', Role::ROLE_ADMIN)->firstOrFail();
        $legacyPath = 'signatures/ttd/legacy.png';
        Storage::disk('public')->put($legacyPath, 'legacy-signature-bytes');
        $signature = DigitalSignature::create([
            'id_role' => $role->id_role,
            'file_ttd' => $legacyPath,
            'tanggal_upload' => now(),
        ]);

        $this->as(Role::ROLE_ADMIN)->getJson("/api/digital-signatures/role/{$role->id_role}")
            ->assertOk()->assertJsonPath('ttd_url', route('api.digital-signatures.files', ['id_role' => $role->id_role, 'file_type' => 'ttd']));

        Storage::disk('local')->assertExists($signature->file_ttd);
        Storage::disk('public')->assertMissing($signature->file_ttd);
    }

    public function test_admin_seeder_requires_an_explicit_default_password(): void
    {
        config()->set('asic.admin_default_password', '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_DEFAULT_PASSWORD wajib diatur');

        app(AdminUserSeeder::class)->run();
    }
}
