<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\Participant;
use App\Models\RegistrationFee;
use App\Models\UserAccount;
use App\Notifications\WelcomeParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
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

    /** Masukan uji: setelah daftar ada email konfirmasi sungguhan (dulu pesannya menyebut tautan aktivasi padahal tidak ada email). */
    public function test_registration_sends_confirmation_email_and_login_page_confirms_it(): void
    {
        Notification::fake();
        $response = $this->withHeaders(['Origin' => 'http://127.0.0.1:8000', 'Referer' => 'http://127.0.0.1:8000/participant/register'])->postJson('/api/register', [
            'full_name' => 'Peserta Email', 'email' => 'peserta.email@example.test',
            'password' => 'QaTest#2026', 'password_confirmation' => 'QaTest#2026',
            'institution' => 'Universitas Contoh', 'country' => 'Indonesia', 'agree_terms' => true,
        ])->assertCreated()
            ->assertJsonPath('confirmation_email_sent', true)
            ->assertJsonPath('activation_link_required', false)
            ->assertJsonPath('data.redirect_url', route('auth.participant.login', ['registered' => 1]));
        $this->assertStringContainsString('peserta.email@example.test', $response->json('message'));

        $user = UserAccount::where('email', 'peserta.email@example.test')->firstOrFail();
        Notification::assertSentTo($user, WelcomeParticipant::class, function ($n, $channels) use ($user) {
            $mail = $n->toMail($user);

            return $channels === ['mail'] && str_contains($mail->subject, 'ASIC') && $mail->actionUrl === route('auth.participant.login');
        });

        $this->get('/participant/login?registered=1')->assertOk()
            ->assertSee(__('asic-auth.registered_title'))
            ->assertSee('value="peserta.email@example.test"', false);
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
        $text = (new Parser)->parseContent(Storage::disk('local')->get($loa->file_document))->getText();
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
