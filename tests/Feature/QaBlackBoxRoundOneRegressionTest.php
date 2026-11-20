<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PaymentProof;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/**
 * Regresi temuan black box QA putaran 1 (15 Nov 2026) yang belum tercakup perbaikan lain.
 */
class QaBlackBoxRoundOneRegressionTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    /** BUG-018: periode yang lewat ditutup tanpa melanggar enum, pendaftaran ke periode aktif tetap jalan. */
    public function test_expired_period_is_closed_without_breaking_registration(): void
    {
        $expired = RegistrationPeriod::create([
            'id_event' => $this->event->id_event, 'nama_periode' => 'Early Bird Lama',
            'tanggal_mulai' => now()->subMonths(2)->toDateString(), 'tanggal_selesai' => now()->subDay()->toDateString(), 'status_periode' => 'Aktif',
        ]);
        Participant::create([
            'id_user' => $this->user(Role::ROLE_PESERTA, 'bug018')->id_user,
            'first_name' => 'Budi', 'last_name' => 'Santoso', 'institution' => 'Universitas Andalas', 'country' => 'Indonesia',
        ]);

        $this->as(Role::ROLE_PESERTA, 'bug018')->postJson('/api/registrations', [
            'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => 'Regular',
            'attendance_mode' => 'Luring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak',
        ])->assertCreated();

        $this->assertSame('Tidak Aktif', $expired->fresh()->status_periode);
        $this->artisan('registration-periods:close-expired')->assertSuccessful();
    }

    /** BUG-021: Sekretariat tidak boleh membuka isi/berkas abstrak; Tim Abstrak boleh. */
    public function test_secretariat_cannot_open_abstract_files(): void
    {
        $registration = $this->registration('bug021');
        $abstract = $this->abstractFor($registration);

        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/registrations/{$registration->id_registration}/abstract")->assertForbidden();
        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/registrations/{$registration->id_registration}/abstract/download")->assertForbidden();
        $this->as(Role::ROLE_SEKRETARIAT)->getJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/file")->assertForbidden();
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson("/api/tim-abstrak/submissions/{$abstract->id_abstract}/file")->assertStatus(404); // izin lolos, berkas uji tidak ada
    }

    /** BUG-022 & BUG-012: Bendahara mengunggah tanda tangannya sendiri; peran lain tidak bisa mengganti tanda tangan Ketua. */
    public function test_signature_permissions_follow_signers(): void
    {
        $bendahara = Role::where('nama_role', Role::ROLE_BENDAHARA)->value('id_role');
        $admin = Role::where('nama_role', Role::ROLE_ADMIN)->value('id_role');
        $upload = fn (int $role) => ['id_role' => $role, 'file_ttd' => UploadedFile::fake()->image('ttd.png')];

        $this->as(Role::ROLE_BENDAHARA)->post('/api/digital-signatures', $upload($bendahara), ['Accept' => 'application/json'])->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->post('/api/digital-signatures', $upload($admin), ['Accept' => 'application/json'])->assertForbidden();
        $this->as(Role::ROLE_SEKRETARIAT)->post('/api/digital-signatures', $upload($admin), ['Accept' => 'application/json'])->assertForbidden();
        $this->as(Role::ROLE_PESERTA, 'bug022')->getJson('/api/digital-signatures')->assertForbidden();
    }

    /** BUG-023: alasan penolakan bukti bayar terlihat oleh peserta pemilik invoice. */
    public function test_payment_rejection_reason_is_visible_to_owner(): void
    {
        $this->fee(['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0]);
        $registration = $this->registration('bug023', [
            'registration_type' => 'Student', 'attendance_mode' => 'Daring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak',
        ]);
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();
        $this->as(Role::ROLE_PESERTA, 'bug023')->postJson("/api/invoices/{$invoice->id_invoice}/payment-proofs", [
            'nominal_transfer' => 500000, 'file_bukti' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertCreated();
        $proof = PaymentProof::where('id_invoice', $invoice->id_invoice)->firstOrFail();
        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$proof->id_payment_proof}/verify", [
            'hasil_verifikasi' => 'Tidak Valid', 'alasan_penolakan' => 'Nominal tidak sesuai',
        ])->assertOk();

        $this->as(Role::ROLE_PESERTA, 'bug023')->getJson("/api/invoices/{$invoice->id_invoice}")->assertOk()
            ->assertJsonPath('data.payment_proofs.0.verification.alasan_penolakan', 'Nominal tidak sesuai');
    }

    /** BUG-026: lupa kata sandi tidak membocorkan email mana yang terdaftar. */
    public function test_forgot_password_does_not_reveal_registered_emails(): void
    {
        $this->postJson('/api/password/forgot', ['email' => 'tidak.terdaftar@example.test'])
            ->assertOk()->assertJsonMissingValidationErrors('email');
    }
}
