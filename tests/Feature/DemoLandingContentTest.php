<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\RegistrationFee;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Perintah asic:demo-landing mengisi landing dengan data contoh berlabel dan bisa dihapus bersih. */
class DemoLandingContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_content_fills_every_landing_section_and_can_be_removed(): void
    {
        Storage::fake('public');
        $this->artisan('asic:demo-landing')->assertSuccessful();
        $this->assertSame(6, Speaker::whereNotNull('foto')->count());
        Storage::disk('public')->assertExists('speakers/demo-speaker-1.jpg');

        $html = $this->withSession(['locale' => 'id'])->get('/')->assertOk()->getContent();
        foreach (['id="speakers"', 'id="dates"', 'id="program"', 'id="fees"', 'id="publication"', 'id="venue"', 'id="committee"', 'id="sponsorship"', 'id="faq"'] as $section) {
            $this->assertStringContainsString($section, $html, $section);
        }
        $this->assertStringContainsString('Keynote Speaker 1 (Contoh)', $html);
        $this->assertStringContainsString(__('asic-landing.sec.demo_banner'), $html);
        $this->assertStringContainsString('IOP Conference Series', $html);
        $this->assertStringNotContainsString('TBA', $html);
        $this->withSession(['locale' => 'en'])->get('/')->assertOk()->assertSee('Keynote speakers')->assertSee('Abstract submission');

        // Dijalankan ulang tidak menggandakan data.
        $this->artisan('asic:demo-landing')->assertSuccessful();
        $this->assertSame(6, Speaker::count());

        $this->artisan('asic:demo-landing --remove')->assertSuccessful();
        $this->assertSame(0, Speaker::count());
        Storage::disk('public')->assertMissing('speakers/demo-speaker-1.jpg');
        $this->assertSame(0, RegistrationFee::count());
        $this->assertSame(0, Event::count());
        $this->assertFalse(DB::table('app_setting')->where('key', 'demo_landing')->exists());
        $this->get('/')->assertOk()->assertDontSee(__('asic-landing.sec.demo_banner'));
    }

    public function test_rerun_adds_photos_to_existing_demo_speakers(): void
    {
        Storage::fake('public');
        $this->artisan('asic:demo-landing')->assertSuccessful();
        Speaker::query()->update(['foto' => null]);
        $this->artisan('asic:demo-landing')->assertSuccessful();
        $this->assertSame(6, Speaker::whereNotNull('foto')->count());
        $this->get('/')->assertOk()->assertSee('/storage/speakers/demo-speaker-1.jpg', false);
    }

    public function test_remove_keeps_real_committee_data(): void
    {
        Storage::fake('public');
        $event = Event::create(['nama_event' => 'Real Event', 'tanggal_mulai' => '2027-06-09', 'tanggal_selesai' => '2027-06-10', 'lokasi' => 'Hall', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Aktif']);
        Speaker::create(['id_event' => $event->id_event, 'nama_speaker' => 'Real Speaker']);

        $this->artisan('asic:demo-landing')->assertSuccessful();
        $this->artisan('asic:demo-landing --remove')->assertSuccessful();

        $this->assertTrue(Event::whereKey($event->id_event)->exists());
        $this->assertSame(['Real Speaker'], Speaker::pluck('nama_speaker')->all());
    }
}
