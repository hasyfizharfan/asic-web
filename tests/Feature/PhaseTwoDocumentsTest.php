<?php

namespace Tests\Feature;

use App\Http\Controllers\DocumentTemplateController;
use App\Models\Attendance;
use App\Models\DocumentTemplate;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\Registration;
use App\Models\Role;
use App\Services\DocumentRenderer;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Fase 2: QR kehadiran luring (KF-13), template dokumen (KF-26/06), sertifikat (KF-27), arsip (KF-29). */
class PhaseTwoDocumentsTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
        $this->fee(['tujuan_publikasi' => 'Tidak']);
        $this->fee(['attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak']);
    }

    private function listener(string $suffix, string $mode = 'Luring', string $payment = 'Terverifikasi', array $o = []): Registration
    {
        $registration = $this->registration($suffix, $o + ['attendance_mode' => $mode, 'participation_type' => 'Listener', 'publication_option' => 'Tidak']);
        Invoice::where('id_registration', $registration->id_registration)->update(['status_pembayaran' => $payment]);

        return $registration;
    }

    private function attend(Registration $registration, string $presented = 'Belum'): void
    {
        $session = EventSession::create(['id_event' => $this->event->id_event, 'nama_sesi' => 'Sesi '.uniqid(), 'waktu' => now(), 'qr_token' => 'S-'.uniqid()]);
        Attendance::create(['id_registration' => $registration->id_registration, 'id_session' => $session->id_session, 'status_kehadiran' => 'Hadir', 'status_presentasi' => $presented, 'tanggal_generate' => now(), 'tanggal_check_in' => now()]);
    }

    // ---------------------------------------------------------------- KF-13

    public function test_attendance_qr_is_only_for_registered_offline_verified_owner(): void
    {
        $ok = $this->listener('qr-ok');
        $this->as(Role::ROLE_PESERTA, 'qr-ok')->getJson("/api/registrations/{$ok->id_registration}/attendance-qr")->assertOk()->assertJsonStructure(['data' => ['qr_token']]);
        $this->as(Role::ROLE_PESERTA, 'qr-other')->getJson("/api/registrations/{$ok->id_registration}/attendance-qr")->assertForbidden();

        $online = $this->listener('qr-online', 'Daring');
        $this->as(Role::ROLE_PESERTA, 'qr-online')->getJson("/api/registrations/{$online->id_registration}/attendance-qr")->assertForbidden();
        $unpaid = $this->listener('qr-unpaid', 'Luring', 'Menunggu Pembayaran');
        $this->as(Role::ROLE_PESERTA, 'qr-unpaid')->getJson("/api/registrations/{$unpaid->id_registration}/attendance-qr")->assertForbidden();
        $cancelled = $this->listener('qr-cancel', 'Luring', 'Terverifikasi', ['registration_status' => 'Dibatalkan']);
        $this->as(Role::ROLE_PESERTA, 'qr-cancel')->getJson("/api/registrations/{$cancelled->id_registration}/attendance-qr")->assertForbidden();

        // Token lama milik peserta Daring ditolak pada pemindaian.
        $online->forceFill(['qr_attendance_token' => 'OLD-ONLINE'])->save();
        $session = EventSession::create(['id_event' => $this->event->id_event, 'nama_sesi' => 'Sesi', 'waktu' => now()->subMinutes(5), 'waktu_selesai' => now()->addHour(), 'qr_token' => 'S-1']);
        $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/sessions/{$session->id_session}/attendance/scan", ['qr_token' => 'OLD-ONLINE'])->assertForbidden();
        $this->assertArrayNotHasKey('qr_attendance_token', $online->fresh()->toArray());
    }

    // ---------------------------------------------------------------- KF-26/06

    public function test_template_permissions_format_validation_and_number_format(): void
    {
        $this->as(Role::ROLE_PESERTA, 't')->getJson('/api/document-templates')->assertForbidden();
        $this->as(Role::ROLE_SEKRETARIAT)->putJson('/api/document-templates/issued/LOA', ['id_event' => $this->event->id_event, 'format_nomor' => '{SEQ}/X'])->assertForbidden();

        $list = $this->as(Role::ROLE_ADMIN)->getJson('/api/document-templates?kelompok=issued&id_event='.$this->event->id_event)->assertOk()->json('data');
        $this->assertCount(5, $list);
        $this->assertSame(0, DocumentTemplate::count()); // GET tidak membuat baris bawaan

        foreach (['{SEQ}/{FOO}/2027', 'TANPA-SEQ/{YYYY}', '{SEQ}/{'] as $bad) {
            $this->as(Role::ROLE_ADMIN)->putJson('/api/document-templates/issued/LOA', ['id_event' => $this->event->id_event, 'format_nomor' => $bad])->assertUnprocessable()->assertJsonValidationErrors('format_nomor');
        }
        $this->as(Role::ROLE_ADMIN)->putJson('/api/document-templates/issued/LOA', ['id_event' => $this->event->id_event, 'format_nomor' => 'LOA-{SEQ}/{SCOPE}/{YYYY}'])->assertOk();

        $registration = $this->registration('num');
        $this->abstractFor($registration, ['scope' => 'CP']);
        $doc = app(DocumentService::class)->issue($registration->fresh(), 'LOA');
        $this->assertSame('LOA-001/CP/2027', $doc->nomor_dokumen);
    }

    public function test_download_upload_versioning_privacy_and_public_listing(): void
    {
        $upload = fn (array $o = []) => $this->as(Role::ROLE_ADMIN)->post('/api/document-templates', $o + ['id_event' => $this->event->id_event, 'jenis_dokumen' => 'Abstrak', 'nama_template' => 'Template Abstrak', 'bahasa' => 'en', 'file' => UploadedFile::fake()->create('abs.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')], ['Accept' => 'application/json']);

        $upload(['file' => UploadedFile::fake()->create('x.exe', 10, 'application/octet-stream')])->assertUnprocessable()->assertJsonValidationErrors('file');
        $upload(['file' => UploadedFile::fake()->create('big.pdf', 11000, 'application/pdf')])->assertUnprocessable()->assertJsonValidationErrors('file');
        $upload(['jenis_dokumen' => 'LOA'])->assertUnprocessable()->assertJsonValidationErrors('jenis_dokumen');

        $first = $upload()->assertCreated()->json('data.id_template');
        $second = $upload(['versi' => '2'])->assertCreated()->json('data.id_template');
        $this->assertSame('Tidak Aktif', DocumentTemplate::find($first)->status_template);
        $this->assertSame('Aktif', DocumentTemplate::find($second)->status_template);

        $path = DocumentTemplate::find($second)->file_template;
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);

        Auth::forgetGuards();
        $this->getJson('/api/public/downloads?id_event='.$this->event->id_event)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id_template', $second);
        $this->get('/downloads/'.$second)->assertOk();
        $this->get('/downloads/'.$first)->assertNotFound();
        $this->get('/downloads')->assertOk()->assertSee('Template Abstrak');

        $this->as(Role::ROLE_ADMIN)->patchJson('/api/document-templates/'.$first, ['status_template' => 'Aktif'])->assertOk();
        $this->assertSame('Tidak Aktif', DocumentTemplate::find($second)->status_template);
    }

    public function test_preview_uses_sample_data_and_creates_no_document(): void
    {
        $pdf = $this->as(Role::ROLE_ADMIN)->get('/api/document-templates/preview/Sertifikat')->assertOk()->assertHeader('Content-Type', 'application/pdf')->getContent();
        $this->assertStringStartsWith('%PDF', $pdf);
        $html = app(DocumentRenderer::class)->html('Sertifikat', DocumentTemplateController::sampleContext('001/CERT'));
        $this->assertStringContainsString('Pratinjau format dokumen', $html);
        $this->as(Role::ROLE_ADMIN)->get('/api/document-templates/preview/Invoice')->assertOk();
        $this->assertSame(0, IssuedDocument::count());
    }

    // ---------------------------------------------------------------- KF-27

    public function test_certificate_eligibility_rules(): void
    {
        $listener = $this->listener('c-l');
        $this->as(Role::ROLE_PESERTA, 'c-l')->postJson("/api/registrations/{$listener->id_registration}/certificate")->assertUnprocessable()->assertJsonPath('data.eligible', false);
        $this->attend($listener);
        $this->as(Role::ROLE_PESERTA, 'c-l')->getJson("/api/registrations/{$listener->id_registration}/certificate")->assertOk()->assertJsonPath('data.eligible', true);

        $presenter = $this->registration('c-p', ['attendance_mode' => 'Luring']);
        $this->attend($presenter, 'Belum');
        $this->as(Role::ROLE_PESERTA, 'c-p')->getJson("/api/registrations/{$presenter->id_registration}/certificate")->assertOk()->assertJsonPath('data.eligible', false);
        Attendance::where('id_registration', $presenter->id_registration)->update(['status_presentasi' => 'Sudah']);
        $this->as(Role::ROLE_PESERTA, 'c-p')->getJson("/api/registrations/{$presenter->id_registration}/certificate")->assertOk()->assertJsonPath('data.eligible', true);
    }

    public function test_certificate_issue_is_idempotent_owner_only_and_follows_template_format(): void
    {
        $this->as(Role::ROLE_ADMIN)->putJson('/api/document-templates/issued/Sertifikat', ['id_event' => $this->event->id_event, 'format_nomor' => '{SEQ}/SERT/{YYYY}'])->assertOk();
        $listener = $this->listener('cert');
        $this->attend($listener);

        $this->as(Role::ROLE_PESERTA, 'intruder')->postJson("/api/registrations/{$listener->id_registration}/certificate")->assertForbidden();
        $first = $this->as(Role::ROLE_PESERTA, 'cert')->postJson("/api/registrations/{$listener->id_registration}/certificate")->assertOk()->json('data.document');
        $second = $this->as(Role::ROLE_PESERTA, 'cert')->postJson("/api/registrations/{$listener->id_registration}/certificate")->assertOk()->json('data.document');
        $this->assertSame($first['id_document'], $second['id_document']);
        $this->assertSame('001/SERT/2027', $first['nomor_dokumen']);
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'Sertifikat')->count());

        $this->as(Role::ROLE_PESERTA, 'cert')->get($first['download_url'])->assertOk();
        $this->as(Role::ROLE_PESERTA, 'intruder')->get($first['download_url'])->assertForbidden();
        $this->as(Role::ROLE_PESERTA, 'cert')->getJson('/api/registrations/'.$listener->id_registration.'/documents')->assertOk()->assertSee('Sertifikat');
        $this->as(Role::ROLE_PESERTA, 'cert')->get('/participant/certificate')->assertOk()->assertDontSee('Belum tersedia');
    }

    // ---------------------------------------------------------------- KF-29

    public function test_archive_transitions_and_public_listing(): void
    {
        $this->as(Role::ROLE_ADMIN)->patchJson("/api/content/events/{$this->event->id_event}", ['status_event' => 'Arsip'])->assertUnprocessable();
        $old = Event::create(['nama_event' => 'ASIC 2025', 'tema' => 'Ketahanan Pangan', 'tanggal_mulai' => '2025-08-01', 'tanggal_selesai' => '2025-08-02', 'lokasi' => 'Padang', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Selesai']);
        Event::create(['nama_event' => 'ASIC 2024', 'tema' => 'Lain', 'tanggal_mulai' => '2024-08-01', 'tanggal_selesai' => '2024-08-02', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Arsip']);
        $this->as(Role::ROLE_PESERTA, 'a')->patchJson("/api/content/events/{$old->id_event}", ['status_event' => 'Arsip'])->assertForbidden();
        $this->as(Role::ROLE_ADMIN)->get('/admin/archive')->assertOk()->assertSee('ASIC 2025');
        $this->as(Role::ROLE_ADMIN)->patchJson("/api/content/events/{$old->id_event}", ['status_event' => 'Arsip'])->assertOk();

        Auth::forgetGuards();
        $this->getJson('/api/public/archive')->assertOk()->assertJsonPath('pagination.total', 2);
        $this->getJson('/api/public/archive?q=Ketahanan')->assertOk()->assertJsonPath('pagination.total', 1);
        $this->getJson('/api/public/archive?year=2024')->assertOk()->assertJsonPath('data.0.nama_event', 'ASIC 2024');
        $detail = $this->getJson("/api/public/archive/{$old->id_event}")->assertOk()->json('data');
        $this->assertArrayNotHasKey('participants', $detail);
        $this->assertStringNotContainsString('@', json_encode($detail));
        $this->getJson("/api/public/archive/{$this->event->id_event}")->assertNotFound();
        $this->get('/archive')->assertOk()->assertSee('ASIC 2025');
        $this->get("/archive/{$old->id_event}")->assertOk()->assertSee('Ketahanan Pangan');
    }
}
