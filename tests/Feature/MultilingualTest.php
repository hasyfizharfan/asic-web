<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** KF-31: paritas lang, locale persisten, pesan API mengikuti locale, view tanpa teks tetap yang sudah dipindahkan. */
class MultilingualTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    /** @return list<string> */
    private function keys(array $array, string $prefix = ''): array
    {
        $keys = [];
        foreach ($array as $key => $value) {
            $keys[] = $prefix.$key;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->keys($value, $prefix.$key.'.'));
            }
        }

        return $keys;
    }

    public function test_every_indonesian_lang_file_has_identical_english_keys(): void
    {
        foreach (File::files(lang_path('id')) as $file) {
            $english = lang_path('en/'.$file->getFilename());
            $this->assertFileExists($english);
            $id = $this->keys(require $file->getPathname());
            $en = $this->keys(require $english);
            sort($id);
            sort($en);
            $this->assertSame($id, $en, $file->getFilename());
        }
        $this->assertSame(count(File::files(lang_path('id'))), count(File::files(lang_path('en'))));
    }

    public function test_default_locale_is_indonesian(): void
    {
        $this->assertSame('id', config('app.locale'));
        $this->get('/login')->assertOk()->assertSee('<html lang="id"', false);
    }

    public function test_language_choice_persists_across_requests(): void
    {
        $this->get('/lang/en')->assertRedirect();
        $this->get('/login')->assertOk()->assertSee('<html lang="en"', false)->assertSee(__('asic-auth.split.title', [], 'en'));

        $this->get('/lang/id')->assertRedirect();
        $this->get('/login')->assertOk()->assertSee('<html lang="id"', false)->assertSee(__('asic-auth.split.title', [], 'id'));
    }

    public function test_invalid_locale_is_not_found(): void
    {
        $this->get('/lang/fr')->assertNotFound();
    }

    public function test_api_validation_and_messages_follow_locale(): void
    {
        $id = $this->withHeader('Referer', rtrim(config('app.url'), '/').'/')->withSession(['locale' => 'id'])->postJson('/api/public/sponsorship-inquiries', [])->assertUnprocessable()->json('errors');
        $en = $this->withHeader('Referer', rtrim(config('app.url'), '/').'/')->withSession(['locale' => 'en'])->postJson('/api/public/sponsorship-inquiries', [])->assertUnprocessable()->json('errors');
        $this->assertNotSame($id, $en);
        $this->assertStringContainsString('wajib', strtolower(json_encode($id)));
        $this->assertStringContainsString('required', strtolower(json_encode($en)));

        $this->withHeader('Referer', rtrim(config('app.url'), '/').'/')->withSession(['locale' => 'en'])->getJson('/api/public/events/999999')->assertNotFound();
        $this->withHeader('Referer', rtrim(config('app.url'), '/').'/')->withSession(['locale' => 'en'])->postJson('/api/login', ['email' => 'nobody@example.test', 'password' => 'x'])
            ->assertJsonPath('message', __('asic-api.messages.email_username_atau_kata_sandi_yang_anda_masukkan', [], 'en'));
    }

    public function test_converted_views_do_not_contain_fixed_ui_text(): void
    {
        $phrases = [
            'secretariat/reregistration.blade.php' => ['Registrasi ulang peserta', 'Pencarian peserta', 'Catat check-in sesi aktif'],
            'treasurer/tariffs.blade.php' => ['Tarif (IDR)', 'Memuat tarif', 'Belum ada tarif.'],
            'participant/notifications.blade.php' => ['Belum tersedia', 'Tandai semua dibaca'],
            'participant/attendance-qr.blade.php' => ['QR kehadiran dan sesi', 'Salin token QR'],
            'participant/certificate.blade.php' => ['Belum tersedia', 'Unduh sertifikat'],
            'admin/templates.blade.php' => ['Belum tersedia', 'Template dokumen'],
            'admin/archive.blade.php' => ['Belum tersedia', 'Pengelolaan arsip'],
            'content/manager.blade.php' => ['Tambah konten', 'Hapus data ini'],
        ];
        foreach ($phrases as $view => $list) {
            $source = File::get(resource_path('views/'.$view));
            foreach ($list as $phrase) {
                $this->assertStringNotContainsString($phrase, $source, $view);
            }
        }
        $this->assertStringNotContainsString("'Anda tidak memiliki akses ke halaman ini.'", File::get(resource_path('js/asic.js')));
    }
}
