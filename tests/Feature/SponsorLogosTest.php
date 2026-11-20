<?php

namespace Tests\Feature;

use App\Models\EventSponsor;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Logo penyelenggara/sponsor/mitra dikelola panitia dan tampil di beranda + halaman Sponsorship. */
class SponsorLogosTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    private function logo(): UploadedFile
    {
        return UploadedFile::fake()->image('logo.png', 1200, 400);
    }

    public function test_secretariat_uploads_logo_and_it_appears_on_public_pages_only_when_published(): void
    {
        $id = $this->as(Role::ROLE_SEKRETARIAT)->post('/api/content/sponsors', [
            'id_event' => $this->event->id_event, 'nama' => 'Mitra Uji', 'kategori' => 'Pendukung', 'url' => 'https://example.org', 'logo' => $this->logo(),
        ], ['Accept' => 'application/json'])->assertCreated()->json('data.id_sponsor');

        $sponsor = EventSponsor::findOrFail($id);
        Storage::disk('public')->assertExists($sponsor->logo_path);
        [$w] = getimagesizefromstring(Storage::disk('public')->get($sponsor->logo_path));
        $this->assertLessThanOrEqual(800, $w);
        $this->get('/')->assertDontSee('Mitra Uji');

        $this->as(Role::ROLE_SEKRETARIAT)->patchJson('/api/content/sponsors/'.$id, ['status' => 'Dipublikasikan'])->assertOk();
        $this->get('/')->assertOk()->assertSee('Mitra Uji')->assertSee(__('asic-public.partner_groups.Pendukung'))->assertSee('rel="noopener sponsored"', false);
        $this->get('/sponsorship')->assertOk()->assertSee('Mitra Uji');
    }

    public function test_logo_can_be_replaced_and_is_deleted_with_the_entry(): void
    {
        $s = $this->as(Role::ROLE_ADMIN);
        $id = $s->post('/api/content/sponsors', ['id_event' => $this->event->id_event, 'nama' => 'A', 'kategori' => 'Sponsor', 'logo' => $this->logo()], ['Accept' => 'application/json'])->json('data.id_sponsor');
        $old = EventSponsor::find($id)->logo_path;
        $s->post('/api/content/sponsors/'.$id, ['logo' => $this->logo(), 'tingkat' => 'Gold'], ['Accept' => 'application/json'])->assertOk();
        $new = EventSponsor::find($id)->logo_path;
        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        $s->deleteJson('/api/content/sponsors/'.$id)->assertOk();
        Storage::disk('public')->assertMissing($new);
    }

    public function test_svg_logos_and_unsafe_links_are_rejected_and_participants_cannot_manage(): void
    {
        $base = ['id_event' => $this->event->id_event, 'nama' => 'X', 'kategori' => 'Sponsor'];
        $this->as(Role::ROLE_ADMIN)->post('/api/content/sponsors', $base + ['logo' => UploadedFile::fake()->createWithContent('x.svg', '<svg onload="alert(1)"/>')], ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('logo');
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/sponsors', $base + ['url' => 'javascript:alert(1)'])->assertUnprocessable()->assertJsonValidationErrors('url');
        $this->as(Role::ROLE_PESERTA, '1')->postJson('/api/content/sponsors', $base)->assertForbidden();
    }

    public function test_entry_without_logo_shows_name_as_text(): void
    {
        EventSponsor::create(['id_event' => $this->event->id_event, 'nama' => 'Fakultas Pertanian Uji', 'kategori' => 'Penyelenggara', 'status' => 'Dipublikasikan']);
        $this->withSession(['locale' => 'en'])->get('/')->assertOk()->assertSee('Fakultas Pertanian Uji')->assertSee('Organized by');
    }
}
