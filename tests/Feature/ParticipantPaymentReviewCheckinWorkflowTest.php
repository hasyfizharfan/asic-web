<?php

namespace Tests\Feature;

use App\Models\EventSession;
use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\PaymentProof;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class ParticipantPaymentReviewCheckinWorkflowTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        $this->travelTo(now()->setDate(2027, 8, 2)->setTime(9, 30));
        Mail::fake();
    }

    public function test_participant_can_register_pay_submit_abstract_receive_loa_and_check_in(): void
    {
        $this->fee(['registration_type' => 'Regular', 'attendance_mode' => 'Luring', 'tujuan_publikasi' => 'Prosiding', 'amount' => 500000, 'discount' => 0]);

        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/registration')->assertOk();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/documents')->assertOk();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/attendance')->assertOk();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/files/full-paper')->assertOk();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/profile')->assertOk();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/certificate')->assertOk();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/notifications')->assertOk();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/refund')->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->get('/secretariat/abstracts')->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->get('/secretariat/reregistration')->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->get('/secretariat/recap')->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->get('/secretariat/attendance')->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->get('/treasurer/tariffs')->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->get('/treasurer/payments')->assertOk();
        $this->as(Role::ROLE_TIM_ABSTRAK)->get('/reviewer/queue')->assertOk();
        $this->as(Role::ROLE_ADMIN)->get('/admin/settings')->assertOk();
        $this->as(Role::ROLE_ADMIN)->get('/admin/signatures')->assertOk();

        $created = $this->as(Role::ROLE_PESERTA, 'workflow')->postJson('/api/registrations', [
            'id_event' => $this->event->id_event,
            'id_period' => $this->period->id_period,
            'registration_type' => 'Regular',
            'participation_type' => 'Oral Presenter',
            'attendance_mode' => 'Luring',
            'publication_option' => 'Prosiding',
        ])->assertCreated();

        $registrationId = $created->json('data.id_registration');
        $invoice = Invoice::where('id_registration', $registrationId)->firstOrFail();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/payment/checkout?id_registration='.$registrationId)->assertOk();

        $proofResponse = $this->as(Role::ROLE_PESERTA, 'workflow')->postJson("/api/invoices/{$invoice->id_invoice}/payment-proofs", [
            'file_bukti' => UploadedFile::fake()->image('transfer.jpg'),
            'nominal_transfer' => 500000,
        ])->assertCreated();
        $proof = PaymentProof::findOrFail($proofResponse->json('data.id_payment_proof'));

        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$proof->id_payment_proof}/verify", [
            'hasil_verifikasi' => 'Valid',
        ])->assertOk()->assertJsonPath('data.status_pembayaran', 'Terverifikasi');

        $this->as(Role::ROLE_PESERTA, 'workflow')->get('/participant/abstract?id_registration='.$registrationId)->assertOk();

        $abstractResponse = $this->as(Role::ROLE_PESERTA, 'workflow')->postJson("/api/registrations/{$registrationId}/abstract", [
            'judul_abstrak' => 'Efficient irrigation for resilient rice production',
            'scope' => 'CP',
            'metode_presentasi' => 'Oral',
            'file_abstrak' => UploadedFile::fake()->create('abstract.pdf', 50, 'application/pdf'),
        ])->assertCreated();
        $abstractId = $abstractResponse->json('data.id_abstract');

        $this->as(Role::ROLE_TIM_ABSTRAK)->get("/reviewer/abstracts/{$abstractId}")->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->get("/secretariat/abstracts/{$abstractId}")->assertOk();

        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$abstractId}/review", [
            'hasil_review' => 'Diterima',
            'catatan_review' => 'Accepted for oral presentation.',
        ])->assertOk()->assertJsonPath('data.status_abstrak', 'Diterima');

        $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/tim-abstrak/submissions/{$abstractId}/issue-documents")->assertOk();
        $loa = IssuedDocument::where('id_registration', $registrationId)->where('jenis_dokumen', 'LOA')->firstOrFail();
        $this->as(Role::ROLE_PESERTA, 'workflow')->get("/api/documents/{$loa->id_document}/download")->assertOk();

        $qr = $this->as(Role::ROLE_PESERTA, 'workflow')->getJson("/api/registrations/{$registrationId}/attendance-qr")
            ->assertOk()->json('data.qr_token');
        $session = EventSession::create([
            'id_event' => $this->event->id_event,
            'nama_sesi' => 'Opening Session',
            'waktu' => '2027-08-02 09:00:00',
            'waktu_selesai' => '2027-08-02 10:00:00',
            'qr_token' => 'ASIC-SESSION-'.$this->event->id_event.'-workflow',
        ]);

        $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/sessions/{$session->id_session}/attendance/scan", [
            'qr_token' => $qr,
        ])->assertOk()->assertJsonPath('data.id_registration', $registrationId);
    }
}
