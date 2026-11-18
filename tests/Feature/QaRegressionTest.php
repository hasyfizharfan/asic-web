<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\Participant;
use App\Models\RegistrationFee;
use App\Models\Role;
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

    /** Masukan uji: setelah daftar ada email verifikasi sungguhan; akun baru bisa masuk setelah tautan diklik. */
    public function test_self_registration_requires_email_verification_before_login(): void
    {
        Notification::fake();
        $headers = ['Origin' => 'http://127.0.0.1:8000', 'Referer' => 'http://127.0.0.1:8000/participant/register'];
        $response = $this->withHeaders($headers)->postJson('/api/register', [
            'full_name' => 'Peserta Email', 'email' => 'peserta.email@example.test',
            'password' => 'QaTest#2026', 'password_confirmation' => 'QaTest#2026',
            'institution' => 'Universitas Contoh', 'country' => 'Indonesia', 'agree_terms' => true,
        ])->assertCreated()
            ->assertJsonPath('confirmation_email_sent', true)
            ->assertJsonPath('activation_link_required', true)
            ->assertJsonPath('data.redirect_url', route('auth.participant.login', ['registered' => 1]));
        $this->assertStringContainsString('peserta.email@example.test', $response->json('message'));

        $user = UserAccount::where('email', 'peserta.email@example.test')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $verifyUrl = null;
        Notification::assertSentTo($user, WelcomeParticipant::class, function ($n, $channels) use ($user, &$verifyUrl) {
            $mail = $n->toMail($user);
            $verifyUrl = $mail->actionUrl;

            return $channels === ['mail'] && str_contains($mail->subject, 'ASIC') && str_contains($mail->actionUrl, '/email/verify/');
        });

        // Halaman login menjelaskan langkah verifikasi, email terisi.
        $this->get('/participant/login?registered=1')->assertOk()
            ->assertSee(__('asic-auth.verify.title'))
            ->assertSee('value="peserta.email@example.test"', false);

        // Belum verifikasi: tidak bisa masuk.
        $this->withHeaders($headers)->postJson('/api/login', ['email' => $user->email, 'password' => 'QaTest#2026'])
            ->assertForbidden()->assertJsonPath('needs_verification', true);

        // Tautan rusak ditolak; tautan asli memverifikasi.
        $this->get(str_replace(sha1('peserta.email@example.test'), sha1('lain@example.test'), $verifyUrl))->assertForbidden();
        $this->get($verifyUrl)->assertRedirect(route('auth.participant.login'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->get('/participant/login')->assertSee(__('asic-auth.verify.verified_title'));

        $this->withHeaders($headers)->postJson('/api/login', ['email' => $user->email, 'password' => 'QaTest#2026'])->assertOk();
    }

    public function test_verification_link_can_be_resent_without_revealing_accounts(): void
    {
        Notification::fake();
        $user = UserAccount::create(['id_role' => Role::where('nama_role', Role::ROLE_PESERTA)->value('id_role'), 'email' => 'belum.verif@example.test', 'password' => 'QaTest#2026', 'status_akun' => 'Aktif', 'email_verified_at' => null]);
        $user->forceFill(['email_verified_at' => null])->save();

        $ok = $this->postJson('/api/email/verification-notification', ['email' => 'belum.verif@example.test'])->assertOk()->json('message');
        $this->assertSame($ok, $this->postJson('/api/email/verification-notification', ['email' => 'tidak.ada@example.test'])->assertOk()->json('message'));
        Notification::assertSentToTimes($user, WelcomeParticipant::class, 1);
    }

    /** Akun buatan Admin/seeder/data contoh tidak perlu verifikasi (tidak terkunci). */
    public function test_accounts_created_outside_self_registration_are_verified(): void
    {
        $user = $this->user(Role::ROLE_PESERTA, '.lama');
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->fresh()->needsEmailVerification());
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
