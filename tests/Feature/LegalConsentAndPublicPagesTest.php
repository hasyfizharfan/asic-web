<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\EventContent;
use App\Models\LegalDocument;
use App\Models\RegistrationFee;
use App\Models\Role;
use App\Models\UserAccount;
use App\Models\UserConsent;
use App\Services\ConsentRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** KF-35 (syarat, privasi, persetujuan) dan halaman publik dinamis Fase 1. */
class LegalConsentAndPublicPagesTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    private function registerPayload(array $o = []): array
    {
        return $o + ['full_name' => 'Calon Peserta', 'email' => 'calon@example.test', 'password' => 'QaTest#2026', 'password_confirmation' => 'QaTest#2026', 'country' => 'Indonesia'];
    }

    private function legal(string $jenis, string $versi, string $status = 'Draft'): LegalDocument
    {
        return LegalDocument::create(['jenis' => $jenis, 'versi' => $versi, 'judul_id' => 'Judul '.$jenis, 'isi_id' => 'Isi '.$jenis.' '.$versi, 'berlaku_mulai' => '2026-11-01', 'status' => $status]);
    }

    public function test_publishing_new_version_archives_old_and_published_is_locked(): void
    {
        $v1 = $this->legal('terms', '1.0');
        $this->as(Role::ROLE_ADMIN)->postJson("/api/content/legal-documents/{$v1->id_legal}/publish")->assertOk();
        $v2 = $this->as(Role::ROLE_ADMIN)->postJson('/api/content/legal-documents', ['jenis' => 'terms', 'versi' => '1.1', 'judul_id' => 'Syarat', 'isi_id' => 'Baru', 'berlaku_mulai' => '2026-12-01'])->assertCreated()->json('data.id_legal');
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/legal-documents', ['jenis' => 'terms', 'versi' => '1.1', 'judul_id' => 'X', 'berlaku_mulai' => '2026-12-01'])->assertUnprocessable();

        $this->as(Role::ROLE_ADMIN)->postJson("/api/content/legal-documents/{$v2}/publish")->assertOk();
        $this->assertSame('Diarsipkan', $v1->fresh()->status);
        $this->assertSame(1, LegalDocument::where('jenis', 'terms')->where('status', 'Dipublikasikan')->count());
        $this->as(Role::ROLE_ADMIN)->patchJson("/api/content/legal-documents/{$v2}", ['isi_id' => 'diubah'])->assertStatus(409);
        $this->assertTrue(AuditLog::where('action', 'content.legal_published')->exists());

        Auth::forgetGuards();
        $this->getJson('/api/public/legal-documents/terms')->assertOk()->assertJsonPath('data.versi', '1.1');
        $this->getJson('/api/public/legal-documents/privacy')->assertNotFound();
    }

    public function test_registration_requires_agree_terms_and_records_consent(): void
    {
        $this->postJson('/api/register', $this->registerPayload())->assertUnprocessable()->assertJsonValidationErrors('agree_terms');
        $this->postJson('/api/register', $this->registerPayload(['agree_terms' => false]))->assertUnprocessable();
        $this->get(route('auth.participant.register'))->assertOk()->assertSee('name="agree_terms"', false)->assertSee(route('public.terms'));

        $terms = $this->legal('terms', '2.0', 'Dipublikasikan');
        $this->legal('privacy', '1.0', 'Dipublikasikan');

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.45'])
            ->postJson('/api/register', $this->registerPayload(['agree_terms' => true]))->assertCreated();

        $user = UserAccount::where('email', 'calon@example.test')->firstOrFail();
        $consents = UserConsent::where('id_user', $user->id_user)->get()->keyBy('jenis');
        $this->assertSame(['2.0', '1.0', null], [$consents['terms']->versi, $consents['privacy']->versi, $consents['ethics']->versi]);
        $this->assertSame('203.0.113.0', $consents['terms']->ip_address);

        // Menerbitkan versi baru tidak mengubah persetujuan lama.
        $new = $this->legal('terms', '3.0');
        $this->as(Role::ROLE_ADMIN)->postJson("/api/content/legal-documents/{$new->id_legal}/publish")->assertOk();
        $this->assertSame('2.0', $consents['terms']->fresh()->versi);
        $this->assertSame($terms->id_legal, $consents['terms']->fresh()->id_legal_document);
    }

    public function test_ipv6_is_anonymized(): void
    {
        $this->assertSame('2001:db8:85a3::', ConsentRecorder::anonymizeIp('2001:db8:85a3:8d3:1319:8a2e:370:7348'));
        $this->assertNull(ConsentRecorder::anonymizeIp('bukan-ip'));
    }

    public function test_every_public_page_renders_in_both_locales_without_placeholders(): void
    {
        foreach (['/', '/venue', '/guidelines', '/faq', '/contact', '/sponsorship', '/travel', '/gallery', '/terms', '/privacy', '/ethics'] as $url) {
            foreach (['id', 'en'] as $locale) {
                $html = $this->withSession(['locale' => $locale])->get($url)->assertOk()->getContent();
                $this->assertStringNotContainsString('TBA', $html, $url.' '.$locale);
                $this->assertStringContainsString('<html lang="'.$locale.'"', $html, $url.' '.$locale);
            }
        }
        $this->get('/terms')->assertSee(__('asic-public.legal_unpublished'));
    }

    public function test_landing_hides_empty_sections_and_shows_published_content_and_fees(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="cfa"', false)->assertSee(__('asic-public.fees_not_announced'));

        EventContent::create(['id_event' => $this->event->id_event, 'section' => 'scope', 'judul_id' => 'Proteksi Tanaman', 'status' => 'Dipublikasikan']);
        EventContent::create(['id_event' => $this->event->id_event, 'section' => 'scope', 'judul_id' => 'Draf Tersembunyi', 'status' => 'Draft']);
        RegistrationFee::create(['id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => 'Student', 'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 30, 'discount' => 0, 'mata_uang' => 'USD']);

        $this->withSession(['locale' => 'en'])->get('/')->assertOk()->assertSee('id="cfa"', false)->assertSee('Proteksi Tanaman')
            ->assertDontSee('Draf Tersembunyi')->assertSee('USD 30.00')->assertDontSee(__('asic-public.fees_not_announced'));
    }

    public function test_public_views_do_not_contain_tba_placeholder(): void
    {
        foreach (File::allFiles(resource_path('views/public')) as $file) {
            $this->assertStringNotContainsString('TBA', $file->getContents(), $file->getRelativePathname());
        }
    }
}
