<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\Participant;
use App\Models\RegistrationFee;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/**
 * Uji regresi dari black box testing QA 15 Nov 2026 (Log Bug ASIC 2027).
 */
class QaRegressionTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    /** BUG-016: registrasi akun peserta error 500 karena import model Participant hilang. */
    public function test_participant_can_register_account(): void
    {
        $this->postJson('/api/register', [
            'full_name' => 'Peserta Uji', 'email' => 'peserta.baru@example.test',
            'password' => 'QaTest#2026', 'password_confirmation' => 'QaTest#2026',
            'institution' => 'Universitas Contoh', 'country' => 'Indonesia', 'terms' => true,
        ])->assertCreated();

        $user = UserAccount::where('email', 'peserta.baru@example.test')->firstOrFail();
        $this->assertTrue(Participant::where('id_user', $user->id_user)->exists());
    }

    /** BUG-017: Listener wajib mendapat LoA otomatis setelah registrasi (kesepakatan mitra). */
    public function test_listener_gets_loa_and_invoice_automatically(): void
    {
        RegistrationFee::create([
            'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => 'Student',
            'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0,
        ]);
        $reg = $this->registration('1', ['participation_type' => 'Listener', 'registration_type' => 'Student', 'attendance_mode' => 'Daring', 'publication_option' => 'Tidak']);

        $invoice = Invoice::where('id_registration', $reg->id_registration)->firstOrFail();
        $loa = IssuedDocument::where('id_registration', $reg->id_registration)->where('jenis_dokumen', 'LOA')->firstOrFail();

        $this->assertSame('001/L/4th ASIC/2027', $loa->nomor_dokumen);
        $text = (new \Smalot\PdfParser\Parser)->parseContent(Storage::disk('local')->get($loa->file_document))->getText();
        $this->assertStringContainsString('Listener', $text);
        $this->assertStringContainsString($invoice->nomor_invoice, $text);
    }

    /** Presenter tidak boleh mendapat LoA sebelum abstrak diterima. */
    public function test_presenter_gets_no_loa_on_registration(): void
    {
        $reg = $this->registration('2');

        $this->assertFalse(IssuedDocument::where('id_registration', $reg->id_registration)->exists());
    }
}
