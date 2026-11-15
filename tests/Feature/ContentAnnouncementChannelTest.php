<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\CommunicationChannel;
use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\Role;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Task 6 (konten), Task 7 (pengumuman), Task 8 (kanal komunikasi). */
class ContentAnnouncementChannelTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    // ------------------------------------------------------------- Task 6: event

    public function test_event_crud(): void
    {
        $payload = ['nama_event' => 'ASIC 2028', 'tanggal_mulai' => '2028-08-01', 'tanggal_selesai' => '2028-08-03', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Draft'];

        $id = $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/events', $payload)->assertCreated()->json('data.id_event');
        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/content/events')->assertOk()->assertJsonPath('pagination.total', 2);
        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/content/events/{$id}")->assertOk()->assertJsonPath('data.nama_event', 'ASIC 2028');
        $this->as(Role::ROLE_SEKRETARIAT)->patchJson("/api/content/events/{$id}", ['lokasi' => 'Bukittinggi', 'status_event' => 'Aktif'])->assertOk()->assertJsonPath('data.lokasi', 'Bukittinggi');
        $this->as(Role::ROLE_SEKRETARIAT)->deleteJson("/api/content/events/{$id}")->assertOk();
        $this->assertDatabaseMissing('event', ['id_event' => $id]);
    }

    public function test_event_validation(): void
    {
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/events', [])->assertUnprocessable()->assertJsonValidationErrors(['nama_event', 'tanggal_mulai', 'tanggal_selesai', 'mode_kegiatan', 'status_event']);
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/events', ['nama_event' => 'X', 'tanggal_mulai' => '2028-08-03', 'tanggal_selesai' => '2028-08-01', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Draft'])
            ->assertUnprocessable()->assertJsonValidationErrors('tanggal_selesai');
        $this->as(Role::ROLE_ADMIN)->patchJson("/api/content/events/{$this->event->id_event}", ['tanggal_selesai' => '2027-07-01'])->assertUnprocessable();
        $this->as(Role::ROLE_ADMIN)->patchJson("/api/content/events/{$this->event->id_event}", ['status_event' => 'Bogus'])->assertUnprocessable();
        $this->as(Role::ROLE_ADMIN)->getJson('/api/content/events/9999')->assertNotFound();
    }

    public function test_event_with_related_data_cannot_be_deleted(): void
    {
        $this->as(Role::ROLE_ADMIN)->deleteJson("/api/content/events/{$this->event->id_event}")->assertStatus(409);
        $this->assertDatabaseHas('event', ['id_event' => $this->event->id_event]);
    }

    // ------------------------------------------------------------- Task 6: speaker & jadwal

    public function test_speaker_crud_with_photo_upload(): void
    {
        $res = $this->as(Role::ROLE_SEKRETARIAT)->post('/api/content/speakers', [
            'id_event' => $this->event->id_event, 'nama_speaker' => 'Prof. Rina', 'institusi' => 'IPB', 'negara' => 'Indonesia',
            'foto' => UploadedFile::fake()->image('rina.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $speaker = Speaker::firstOrFail();
        Storage::disk('public')->assertExists($speaker->foto);
        $this->assertNotNull($res->json('data.foto_url'));

        $oldFoto = $speaker->foto;
        $this->as(Role::ROLE_SEKRETARIAT)->post("/api/content/speakers/{$speaker->id_speaker}", [
            'nama_speaker' => 'Prof. Rina S.', 'foto' => UploadedFile::fake()->image('baru.png'),
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.nama_speaker', 'Prof. Rina S.');
        Storage::disk('public')->assertMissing($oldFoto);
        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/content/speakers?id_event='.$this->event->id_event)->assertOk()->assertJsonPath('pagination.total', 1);

        $foto = $speaker->fresh()->foto;
        $this->as(Role::ROLE_SEKRETARIAT)->deleteJson("/api/content/speakers/{$speaker->id_speaker}")->assertOk();
        Storage::disk('public')->assertMissing($foto);
        $this->assertSame(0, Speaker::count());
    }

    public function test_speaker_validation(): void
    {
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/speakers', [])->assertUnprocessable()->assertJsonValidationErrors(['id_event', 'nama_speaker']);
        $this->as(Role::ROLE_ADMIN)->post('/api/content/speakers', [
            'id_event' => $this->event->id_event, 'nama_speaker' => 'A', 'foto' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('foto');
        $this->as(Role::ROLE_ADMIN)->getJson('/api/content/speakers/9999')->assertNotFound();
    }

    public function test_schedule_crud_and_time_validation(): void
    {
        $base = ['id_event' => $this->event->id_event, 'nama_kegiatan' => 'Opening', 'tanggal' => '2027-08-01', 'waktu_mulai' => '09:00', 'waktu_selesai' => '10:00'];
        $id = $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/schedules', $base)->assertCreated()->json('data.id_schedule');

        $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/schedules', ['waktu_selesai' => '08:00'] + $base)->assertUnprocessable()->assertJsonValidationErrors('waktu_selesai');
        $this->as(Role::ROLE_SEKRETARIAT)->patchJson("/api/content/schedules/{$id}", ['waktu_selesai' => '08:30'])->assertUnprocessable();
        $this->as(Role::ROLE_SEKRETARIAT)->patchJson("/api/content/schedules/{$id}", ['waktu_selesai' => '11:00', 'lokasi' => 'Aula'])->assertOk()->assertJsonPath('data.lokasi', 'Aula');
        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/content/schedules/{$id}")->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->deleteJson("/api/content/schedules/{$id}")->assertOk();
        $this->assertSame(0, EventSchedule::count());
    }

    public function test_content_management_requires_permission(): void
    {
        foreach ([Role::ROLE_PESERTA, Role::ROLE_BENDAHARA, Role::ROLE_TIM_ABSTRAK] as $role) {
            $this->as($role)->getJson('/api/content/events')->assertForbidden();
            $this->as($role)->postJson('/api/content/events', [])->assertForbidden();
            $this->as($role)->postJson('/api/content/speakers', [])->assertForbidden();
            $this->as($role)->postJson('/api/content/schedules', [])->assertForbidden();
            $this->as($role)->postJson('/api/content/announcements', [])->assertForbidden();
            $this->as($role)->postJson('/api/content/communication-channels', [])->assertForbidden();
        }
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/content/events')->assertUnauthorized();
    }

    public function test_public_content_is_read_only_and_hides_draft_events(): void
    {
        Speaker::create(['id_event' => $this->event->id_event, 'nama_speaker' => 'Dr. Ali']);
        EventSchedule::create(['id_event' => $this->event->id_event, 'nama_kegiatan' => 'Opening', 'tanggal' => '2027-08-01', 'waktu_mulai' => '09:00']);
        $draft = Event::create(['nama_event' => 'Draft', 'tanggal_mulai' => '2028-01-01', 'tanggal_selesai' => '2028-01-02', 'status_event' => 'Draft']);

        $this->getJson('/api/public/events')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/public/events/{$this->event->id_event}")->assertOk()->assertJsonPath('data.nama_event', 'ASIC 2027');
        $this->getJson("/api/public/events/{$this->event->id_event}/speakers")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/public/events/{$this->event->id_event}/schedules")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/public/events/{$draft->id_event}")->assertNotFound();
        $this->getJson("/api/public/events/{$draft->id_event}/speakers")->assertNotFound();
        $this->postJson('/api/public/events', [])->assertStatus(405);
    }

    // ------------------------------------------------------------- Task 7: pengumuman

    private function ann(array $o = []): array
    {
        return $o + ['id_event' => $this->event->id_event, 'judul' => 'Pengumuman', 'isi_pengumuman' => 'Isi', 'status' => 'Draft'];
    }

    public function test_announcement_crud_sets_author_and_publication_date(): void
    {
        $id = $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/announcements', $this->ann())->assertCreated()
            ->assertJsonPath('data.status', 'Draft')->assertJsonPath('data.tanggal_publikasi', null)->json('data.id_announcement');
        $this->assertSame($this->user(Role::ROLE_SEKRETARIAT)->id_user, Announcement::find($id)->id_author);

        $this->as(Role::ROLE_SEKRETARIAT)->patchJson("/api/content/announcements/{$id}", ['status' => 'Dipublikasikan', 'judul' => 'Baru'])->assertOk()->assertJsonPath('data.judul', 'Baru');
        $this->assertNotNull(Announcement::find($id)->tanggal_publikasi); // otomatis terisi saat dipublikasikan

        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/content/announcements?status=Dipublikasikan')->assertOk()->assertJsonPath('pagination.total', 1);
        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/content/announcements/{$id}")->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->deleteJson("/api/content/announcements/{$id}")->assertOk();
        $this->assertSame(0, Announcement::count());
    }

    public function test_announcement_validation(): void
    {
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/announcements', [])->assertUnprocessable()->assertJsonValidationErrors(['id_event', 'judul', 'isi_pengumuman', 'status']);
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/announcements', $this->ann(['status' => 'Terbit']))->assertUnprocessable();
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/announcements', $this->ann(['id_event' => 9999]))->assertUnprocessable();
        $this->as(Role::ROLE_ADMIN)->patchJson('/api/content/announcements/9999', ['judul' => 'x'])->assertNotFound();
    }

    public function test_participants_only_see_published_and_due_announcements(): void
    {
        $this->as(Role::ROLE_ADMIN);
        $this->postJson('/api/content/announcements', $this->ann(['judul' => 'Draft rahasia']))->assertCreated();
        $this->postJson('/api/content/announcements', $this->ann(['judul' => 'Terbit', 'status' => 'Dipublikasikan']))->assertCreated();
        $this->postJson('/api/content/announcements', $this->ann(['judul' => 'Terjadwal', 'status' => 'Dipublikasikan', 'tanggal_publikasi' => now()->addDay()->toDateTimeString()]))->assertCreated();
        $this->postJson('/api/content/announcements', $this->ann(['judul' => 'Lampau', 'status' => 'Dipublikasikan', 'tanggal_publikasi' => now()->subDay()->toDateTimeString()]))->assertCreated();

        $res = $this->as(Role::ROLE_PESERTA)->getJson('/api/announcements')->assertOk();
        $this->assertEqualsCanonicalizing(['Terbit', 'Lampau'], collect($res->json('data'))->pluck('judul')->all());
        $this->assertArrayNotHasKey('id_author', $res->json('data.0'));

        $this->travel(2)->days();
        $this->as(Role::ROLE_PESERTA)->getJson('/api/announcements')->assertJsonCount(3, 'data');
        $this->as(Role::ROLE_PESERTA)->getJson('/api/announcements?id_event=9999')->assertJsonCount(0, 'data');
    }

    // ------------------------------------------------------------- Task 8: kanal

    private function chan(array $o = []): array
    {
        return $o + ['id_event' => $this->event->id_event, 'nama_kanal' => 'Grup WhatsApp ASIC', 'jenis_kanal' => 'WhatsApp', 'url' => 'https://chat.whatsapp.com/abc123', 'status' => 'Aktif'];
    }

    public function test_channel_crud(): void
    {
        $id = $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/content/communication-channels', $this->chan())->assertCreated()->json('data.id_channel');
        $this->as(Role::ROLE_SEKRETARIAT)->patchJson("/api/content/communication-channels/{$id}", ['status' => 'Nonaktif'])->assertOk()->assertJsonPath('data.status', 'Nonaktif');
        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/content/communication-channels?status=Nonaktif')->assertOk()->assertJsonPath('pagination.total', 1);
        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/content/communication-channels/{$id}")->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->deleteJson("/api/content/communication-channels/{$id}")->assertOk();
        $this->assertSame(0, CommunicationChannel::count());
    }

    public function test_channel_url_is_validated_and_dangerous_schemes_rejected(): void
    {
        foreach (['bukan-url', 'javascript:alert(1)', 'ftp://x.com/a', 'data:text/html;base64,AAAA'] as $bad) {
            $this->as(Role::ROLE_ADMIN)->postJson('/api/content/communication-channels', $this->chan(['url' => $bad]))->assertUnprocessable()->assertJsonValidationErrors('url');
        }
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/communication-channels', $this->chan(['jenis_kanal' => 'Fax']))->assertUnprocessable()->assertJsonValidationErrors('jenis_kanal');
        $this->as(Role::ROLE_ADMIN)->postJson('/api/content/communication-channels', $this->chan(['status' => 'x']))->assertUnprocessable();
        $this->as(Role::ROLE_ADMIN)->patchJson('/api/content/communication-channels/9999', ['status' => 'Aktif'])->assertNotFound();
        $this->assertSame(0, CommunicationChannel::count());
    }

    public function test_users_only_see_active_channels(): void
    {
        CommunicationChannel::create($this->chan(['nama_kanal' => 'Aktif']));
        CommunicationChannel::create($this->chan(['nama_kanal' => 'Mati', 'status' => 'Nonaktif']));

        $this->as(Role::ROLE_PESERTA)->getJson('/api/communication-channels')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama_kanal', 'Aktif');
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/communication-channels')->assertUnauthorized();
    }
}
