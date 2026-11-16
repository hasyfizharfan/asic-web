<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\Faq;
use App\Models\Role;
use App\Models\SponsorshipInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Fase 1: API pengelolaan & publik konten (KF-01, 04, 05, 07, 28, 33, 34). */
class PublicContentManagementTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    private function faq(array $o = []): array
    {
        return $o + ['id_event' => $this->event->id_event, 'pertanyaan_id' => 'Kapan batas abstrak?', 'jawaban_id' => 'Lihat tanggal penting.'];
    }

    public function test_roles_without_permission_are_forbidden(): void
    {
        foreach ([Role::ROLE_PESERTA, Role::ROLE_BENDAHARA, Role::ROLE_TIM_ABSTRAK] as $role) {
            $this->as($role, 'x')->postJson('/api/content/faqs', $this->faq())->assertForbidden();
            $this->as($role, 'x')->getJson('/api/content/faqs')->assertForbidden();
        }
        $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/faqs', $this->faq())->assertCreated()->assertJsonPath('data.status', 'Draft');
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/faqs', $this->faq())->assertCreated();
    }

    public function test_draft_is_hidden_and_published_follows_order_and_locale_fallback(): void
    {
        $first = $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/faqs', $this->faq(['urutan' => 2]))->json('data.id_faq');
        $second = $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/faqs', $this->faq(['pertanyaan_id' => null, 'jawaban_id' => null, 'pertanyaan_en' => 'Only English?', 'jawaban_en' => 'Yes.', 'urutan' => 1]))->json('data.id_faq');
        $url = "/api/public/events/{$this->event->id_event}/faqs";

        $this->getJson($url)->assertOk()->assertJsonCount(0, 'data');

        foreach ([$first, $second] as $id) {
            $this->as(Role::ROLE_SEKRETARIAT)->patchJson("/api/content/faqs/{$id}", ['status' => 'Dipublikasikan'])->assertOk();
        }
        Auth::forgetGuards();
        app()->setLocale('id');
        $rows = $this->withSession(['locale' => 'id'])->getJson($url)->assertOk()->json('data');
        $this->assertSame([$second, $first], array_column($rows, 'id'));
        $this->assertSame('Only English?', $rows[0]['pertanyaan']); // bahasa Indonesia kosong → bahasa Inggris

        $this->as(Role::ROLE_SEKRETARIAT)->patchJson('/api/content/faqs/reorder', ['items' => [['id' => $first, 'urutan' => 0], ['id' => $second, 'urutan' => 5]]])->assertOk();
        Auth::forgetGuards();
        $this->assertSame([$first, $second], array_column($this->getJson($url)->json('data'), 'id'));
    }

    public function test_bilingual_text_requires_one_language_and_links_must_be_https_or_mailto(): void
    {
        $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/faqs', ['id_event' => $this->event->id_event])->assertUnprocessable()->assertJsonValidationErrors('pertanyaan_id');
        $block = ['id_event' => $this->event->id_event, 'section' => 'visa', 'judul_id' => 'Visa', 'isi_id' => 'Info'];
        foreach (['javascript:alert(1)', 'http://contoh.test', 'data:text/html,hi'] as $bad) {
            $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/event-content', $block + ['tautan' => $bad])->assertUnprocessable()->assertJsonValidationErrors('tautan');
        }
        $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/event-content', $block + ['tautan' => 'https://imigrasi.go.id'])->assertCreated();
        $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/event-content', $block + ['tautan' => 'mailto:panitia@contoh.test'])->assertCreated();
    }

    public function test_script_in_content_is_escaped_on_public_page(): void
    {
        Faq::create($this->faq(['pertanyaan_id' => '<script>alert(1)</script>Tanya', 'jawaban_id' => "<b>tebal</b>\nbaris dua", 'status' => 'Dipublikasikan']));

        $html = $this->withSession(['locale' => 'id'])->get('/faq')->assertOk()->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;Tanya', $html);
        $this->assertStringContainsString('&lt;b&gt;tebal&lt;/b&gt;<br />', $html);
    }

    public function test_photo_upload_validation_storage_and_deletion(): void
    {
        $this->as(Role::ROLE_SEKRETARIAT)->post('/api/content/photos', ['id_event' => $this->event->id_event, 'caption_id' => 'Pembukaan', 'file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->as(Role::ROLE_SEKRETARIAT)->post('/api/content/photos', ['id_event' => $this->event->id_event, 'caption_id' => 'Besar', 'file' => UploadedFile::fake()->image('big.jpg')->size(6000)], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->as(Role::ROLE_SEKRETARIAT)->post('/api/content/photos', ['id_event' => $this->event->id_event, 'file' => UploadedFile::fake()->image('nocap.jpg')], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('caption_id');

        $id = $this->as(Role::ROLE_SEKRETARIAT)->post('/api/content/photos', ['id_event' => $this->event->id_event, 'caption_id' => 'Pembukaan', 'file' => UploadedFile::fake()->image('ok.jpg', 2400, 1200)], ['Accept' => 'application/json'])->assertCreated()->json('data.id_photo');
        $photo = EventPhoto::findOrFail($id);
        $this->assertMatchesRegularExpression('#^gallery/'.$this->event->id_event.'/[0-9a-f-]{36}\.jpg$#', $photo->file_path);
        Storage::disk('public')->assertExists($photo->file_path);
        [$w] = getimagesizefromstring(Storage::disk('public')->get($photo->file_path));
        $this->assertLessThanOrEqual(1920, $w);
        $this->assertTrue(AuditLog::where('action', 'content.photo_uploaded')->exists());

        $this->as(Role::ROLE_SEKRETARIAT)->deleteJson("/api/content/photos/{$id}")->assertOk();
        Storage::disk('public')->assertMissing($photo->file_path);
        $this->assertTrue(AuditLog::where('action', 'content.photo_deleted')->exists());
    }

    public function test_deleting_event_removes_photo_files(): void
    {
        $event = Event::create(['nama_event' => 'Event Hapus', 'tanggal_mulai' => '2027-01-01', 'tanggal_selesai' => '2027-01-02', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Draft']);
        Storage::disk('public')->put('gallery/'.$event->id_event.'/x.jpg', 'img');
        EventPhoto::create(['id_event' => $event->id_event, 'file_path' => 'gallery/'.$event->id_event.'/x.jpg', 'caption_id' => 'x']);

        $event->delete();

        Storage::disk('public')->assertMissing('gallery/'.$event->id_event.'/x.jpg');
        $this->assertSame(0, EventPhoto::count());
    }

    public function test_sponsorship_inquiry_honeypot_throttle_and_status_audit(): void
    {
        $payload = ['id_event' => $this->event->id_event, 'jenis' => 'Sponsorship', 'nama_organisasi' => 'PT Contoh', 'nama_kontak' => 'Rina', 'email' => 'rina@contoh.test', 'pesan' => 'Kami tertarik menjadi sponsor.'];

        $this->postJson('/api/public/sponsorship-inquiries', $payload + ['website' => 'http://spam.test'])->assertUnprocessable();
        $id = $this->postJson('/api/public/sponsorship-inquiries', $payload)->assertCreated()->json('data.id_inquiry');
        $this->assertSame('Baru', SponsorshipInquiry::findOrFail($id)->status);
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/public/sponsorship-inquiries', $payload);
        }
        $this->postJson('/api/public/sponsorship-inquiries', $payload)->assertStatus(429);

        $this->as(Role::ROLE_SEKRETARIAT)->patchJson("/api/content/sponsorship-inquiries/{$id}", ['status' => 'Dihubungi', 'catatan_internal' => 'Sudah ditelepon', 'email' => 'ubah@x.test'])->assertOk()
            ->assertJsonPath('data.status', 'Dihubungi')->assertJsonPath('data.email', 'rina@contoh.test');
        $this->assertTrue(AuditLog::where('action', 'content.inquiry_status')->exists());
        $this->as(Role::ROLE_SEKRETARIAT)->deleteJson("/api/content/sponsorship-inquiries/{$id}")->assertStatus(405);
    }

    public function test_draft_event_content_does_not_leak(): void
    {
        $draft = Event::create(['nama_event' => 'Draft', 'tanggal_mulai' => '2028-01-01', 'tanggal_selesai' => '2028-01-02', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Draft']);
        Faq::create(['id_event' => $draft->id_event, 'pertanyaan_id' => 'Rahasia', 'jawaban_id' => 'x', 'status' => 'Dipublikasikan']);

        $this->getJson("/api/public/events/{$draft->id_event}/faqs")->assertNotFound();
        $this->get('/faq')->assertOk()->assertDontSee('Rahasia');
    }

    public function test_manager_pages_and_admin_content_route(): void
    {
        $this->as(Role::ROLE_SEKRETARIAT)->get('/secretariat/content')->assertOk()->assertSee(__('asic-content.manager.heading'));
        $this->as(Role::ROLE_ADMIN)->get('/admin/content')->assertOk()->assertSee(__('asic-content.manager.heading'));
        $this->as(Role::ROLE_PESERTA, 'm')->get('/admin/content')->assertForbidden();
    }
}
