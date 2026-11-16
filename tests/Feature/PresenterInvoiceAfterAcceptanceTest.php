<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\Role;
use App\Services\AbstractDecisionProcessor;
use App\Services\InvoiceIssuer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** KF-20: invoice presenter terbit saat abstrak Diterima; Listener saat selesai mendaftar. */
class PresenterInvoiceAfterAcceptanceTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
        $this->fee();
        $this->fee(['tujuan_publikasi' => 'Tidak']);
    }

    public function test_new_presenter_registration_has_no_invoice(): void
    {
        $registration = $this->registration('new');

        $this->assertFalse(Invoice::where('id_registration', $registration->id_registration)->exists());
    }

    public function test_accepted_abstract_issues_exactly_one_invoice(): void
    {
        $registration = $this->registration('accepted');
        $abstract = $this->abstractFor($registration, ['status_abstrak' => 'Diterima']);
        $processor = app(AbstractDecisionProcessor::class);

        $processor->process($abstract);
        $processor->process($abstract->fresh());
        app(InvoiceIssuer::class)->issueFor($registration);

        $this->assertSame(1, Invoice::where('id_registration', $registration->id_registration)->count());
        $this->assertSame(1, IssuedDocument::where('id_registration', $registration->id_registration)->where('jenis_dokumen', 'LOA')->count());
    }

    public function test_rejected_abstract_gets_rejection_letter_but_no_invoice(): void
    {
        $registration = $this->registration('rejected');
        $abstract = $this->abstractFor($registration, ['status_abstrak' => 'Ditolak']);

        app(AbstractDecisionProcessor::class)->process($abstract);

        $this->assertFalse(Invoice::where('id_registration', $registration->id_registration)->exists());
        $this->assertTrue(IssuedDocument::where('id_registration', $registration->id_registration)->where('jenis_dokumen', 'Penolakan')->exists());
    }

    public function test_listener_registration_gets_invoice(): void
    {
        $registration = $this->registration('listener', ['participation_type' => 'Listener', 'publication_option' => 'Tidak']);

        $this->assertTrue(Invoice::where('id_registration', $registration->id_registration)->exists());
    }

    public function test_presenter_cannot_upload_proof_before_abstract_accepted(): void
    {
        // Simulasi data lama: invoice presenter yang sudah terbit sebelum aturan KF-20.
        $registration = $this->registration('early');
        $this->abstractFor($registration);
        app(InvoiceIssuer::class)->issueFor($registration);
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();

        $this->as(Role::ROLE_PESERTA, 'early')->postJson("/api/invoices/{$invoice->id_invoice}/payment-proofs", [
            'nominal_transfer' => 1400000, 'file_bukti' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertUnprocessable()->assertJsonPath('message', __('asic-api.payment.presenter_abstract_not_accepted'));
    }

    public function test_presenter_can_submit_abstract_without_invoice(): void
    {
        $registration = $this->registration('submit');

        $this->as(Role::ROLE_PESERTA, 'submit')->post("/api/registrations/{$registration->id_registration}/abstract", [
            'judul_abstrak' => 'Smart Irrigation for Paddy', 'scope' => 'CP', 'metode_presentasi' => 'Oral',
            'file_abstrak' => UploadedFile::fake()->create('abstract.pdf', 100, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertSuccessful();
    }

    public function test_early_invoice_report_command_counts_without_changing_data(): void
    {
        $registration = $this->registration('report');
        $this->abstractFor($registration);
        app(InvoiceIssuer::class)->issueFor($registration);

        $this->artisan('asic:report-early-presenter-invoices')
            ->expectsOutputToContain('Invoice presenter dengan abstrak belum Diterima: 1')
            ->assertSuccessful();
        $this->assertSame(1, Invoice::count());
    }
}
