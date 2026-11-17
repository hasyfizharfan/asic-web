<?php

namespace Tests\Feature;

use App\Models\AbstractReview;
use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\Role;
use App\Models\UserAccount;
use App\Notifications\StatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Task 1 (seleksi abstrak) + Task 2 (LOA & surat penolakan) + otorisasi download. */
class AbstractSelectionAndDocumentsTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    private function review(int $idAbstract, string $hasil = 'Diterima', string $as = Role::ROLE_TIM_ABSTRAK)
    {
        return $this->as($as)->postJson("/api/tim-abstrak/submissions/{$idAbstract}/review", ['hasil_review' => $hasil, 'catatan_review' => 'Catatan reviewer']);
    }

    // ------------------------------------------------------------- Task 1

    public function test_queue_lists_and_filters_by_status_event_and_paginates(): void
    {
        $a = $this->abstractFor($this->registration('1'));
        $this->abstractFor($this->registration('2'), ['status_abstrak' => 'Diterima']);

        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions')->assertOk()->assertJsonPath('data.total', 2);
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?status_abstrak=Diajukan&id_event='.$this->event->id_event)
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id_abstract', $a->id_abstract);
        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/tim-abstrak/submissions?id_event=9999')->assertOk()->assertJsonPath('data.total', 0);
    }

    public function test_detail_includes_review_history(): void
    {
        $a = $this->abstractFor($this->registration('1'));
        $this->review($a->id_abstract)->assertOk();

        $this->as(Role::ROLE_TIM_ABSTRAK)->getJson("/api/tim-abstrak/submissions/{$a->id_abstract}")
            ->assertOk()->assertJsonCount(1, 'data.reviews')->assertJsonPath('data.reviews.0.hasil_review', 'Diterima');
    }

    public function test_review_is_validated(): void
    {
        $a = $this->abstractFor($this->registration('1'));
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$a->id_abstract}/review", [])->assertUnprocessable()->assertJsonValidationErrors('hasil_review');
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$a->id_abstract}/review", ['hasil_review' => 'Revisi'])->assertUnprocessable();
        $this->assertSame('Diajukan', $a->fresh()->status_abstrak);
    }

    public function test_only_accept_reject_permission_can_review(): void
    {
        $a = $this->abstractFor($this->registration('1'));
        foreach ([Role::ROLE_PESERTA, Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA] as $role) {
            $this->review($a->id_abstract, 'Diterima', $role)->assertForbidden();
        }
        $this->assertSame('Diajukan', $a->fresh()->status_abstrak);
        $this->review($a->id_abstract, 'Diterima', Role::ROLE_ADMIN)->assertOk();
    }

    public function test_decision_is_final_and_cannot_be_overwritten(): void
    {
        $a = $this->abstractFor($this->registration('1'));
        $this->fee();
        $this->review($a->id_abstract, 'Diterima')->assertOk();
        $this->review($a->id_abstract, 'Ditolak')->assertStatus(409);

        $this->assertSame('Diterima', $a->fresh()->status_abstrak);
        $this->assertSame(1, AbstractReview::where('id_abstract', $a->id_abstract)->count());
        $this->assertSame(0, IssuedDocument::where('jenis_dokumen', 'Penolakan')->count());
    }

    public function test_cancelled_registration_cannot_be_reviewed(): void
    {
        $a = $this->abstractFor($this->registration('1', ['registration_status' => 'Dibatalkan']));
        $this->review($a->id_abstract)->assertUnprocessable();
        $this->assertSame('Diajukan', $a->fresh()->status_abstrak);
    }

    public function test_unknown_abstract_returns_404(): void
    {
        $this->review(9999)->assertNotFound();
    }

    // ------------------------------------------------------------- Task 2

    public function test_acceptance_issues_loa_and_invoice_and_emails_participant(): void
    {
        Notification::fake();
        $reg = $this->registration('1');
        $a = $this->abstractFor($reg);
        $this->fee();

        $res = $this->review($a->id_abstract)->assertOk();
        $res->assertJsonPath('data.documents.loa.jenis_dokumen', 'LOA')->assertJsonPath('data.invoice.status', 'issued');

        $loa = IssuedDocument::where('id_registration', $reg->id_registration)->where('jenis_dokumen', 'LOA')->firstOrFail();
        $this->assertTrue(Storage::disk('local')->exists($loa->file_document));
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($loa->file_document));
        $this->assertStringContainsString('Smart Irrigation', $this->pdfText($loa));
        $this->assertSame(1, Invoice::where('id_registration', $reg->id_registration)->count());
        $this->assertNotNull(IssuedDocument::where('jenis_dokumen', 'Invoice')->first());

        // Email ke peserta: LoA dan invoice, masing-masing dengan lampiran PDF (tidak ada email ganda).
        $user = UserAccount::where('email', 'peserta1@example.test')->firstOrFail();
        foreach (['loa_issued', 'invoice_issued'] as $kind) {
            Notification::assertSentTo($user, StatusChanged::class, function ($n, $channels) use ($kind, $user) {
                if ($n->kind !== $kind || ! in_array('mail', $channels, true)) {
                    return false;
                }
                $mail = $n->toMail($user);

                return count($mail->rawAttachments) === 1 && str_ends_with($mail->rawAttachments[0]['name'], '.pdf');
            });
        }
        Notification::assertSentTo($user, StatusChanged::class, fn ($n, $channels) => $n->kind === 'abstract_accepted' && ! in_array('mail', $channels, true));
    }

    public function test_rejection_issues_rejection_letter_without_invoice(): void
    {
        Notification::fake();
        $reg = $this->registration('1');
        $a = $this->abstractFor($reg);
        $this->fee();

        $this->review($a->id_abstract, 'Ditolak')->assertOk()->assertJsonPath('data.documents.penolakan.jenis_dokumen', 'Penolakan');

        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'Penolakan')->count());
        $this->assertSame(0, IssuedDocument::where('jenis_dokumen', 'LOA')->count());
        $this->assertSame(0, Invoice::count());
        $user = UserAccount::where('email', 'peserta1@example.test')->firstOrFail();
        Notification::assertSentToTimes($user, StatusChanged::class, 2); // keputusan (situs) + surat penolakan (situs+email)
        Notification::assertSentTo($user, StatusChanged::class, fn ($n, $channels) => $n->kind === 'rejection_issued' && in_array('mail', $channels, true));
    }

    public function test_acceptance_without_configured_fee_still_saves_decision_and_loa_with_warning(): void
    {
        $a = $this->abstractFor($this->registration('1'));

        $this->review($a->id_abstract)->assertOk()
            ->assertJsonPath('data.status_abstrak', 'Diterima')
            ->assertJsonPath('data.invoice.status', 'skipped')
            ->assertJsonCount(1, 'data.warnings');
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'LOA')->count());
        $this->assertSame(0, Invoice::count());

        // setelah Bendahara membuat tarif, penerbitan susulan membuat invoice tanpa LOA ganda
        $this->fee();
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$a->id_abstract}/issue-documents")
            ->assertOk()->assertJsonPath('data.invoice.status', 'issued');
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'LOA')->count());
        $this->assertSame(1, Invoice::count());
    }

    public function test_issue_documents_is_idempotent_and_never_duplicates(): void
    {
        $a = $this->abstractFor($this->registration('1'));
        $this->fee();
        $this->review($a->id_abstract)->assertOk();

        foreach ([1, 2, 3] as $_) {
            $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$a->id_abstract}/issue-documents")->assertOk();
        }
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'LOA')->count());
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'Invoice')->count());
        $this->assertSame(1, Invoice::count());
    }

    public function test_issue_documents_rejected_for_undecided_abstract_and_unauthorized_role(): void
    {
        $a = $this->abstractFor($this->registration('1'));
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$a->id_abstract}/issue-documents")->assertStatus(409);
        $this->as(Role::ROLE_PESERTA)->postJson("/api/tim-abstrak/submissions/{$a->id_abstract}/issue-documents")->assertForbidden();
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson('/api/tim-abstrak/submissions/9999/issue-documents')->assertNotFound();
    }

    public function test_document_numbers_are_unique_per_type(): void
    {
        $this->fee();
        foreach (['1', '2', '3'] as $s) {
            $this->review($this->abstractFor($this->registration($s))->id_abstract)->assertOk();
        }
        $numbers = IssuedDocument::where('jenis_dokumen', 'LOA')->pluck('nomor_dokumen');
        $this->assertCount(3, $numbers);
        $this->assertCount(3, $numbers->unique());
        $this->assertEqualsCanonicalizing(['001/CP/4th ASIC/2027', '002/CP/4th ASIC/2027', '003/CP/4th ASIC/2027'], $numbers->all());
    }

    public function test_document_numbers_follow_committee_format(): void
    {
        $this->fee();
        $this->review($this->abstractFor($this->registration('1'), ['scope' => 'SM'])->id_abstract)->assertOk();
        $this->review($this->abstractFor($this->registration('2'))->id_abstract, 'Ditolak')->assertOk();

        $this->assertSame('001/SM/4th ASIC/2027', IssuedDocument::where('jenis_dokumen', 'LOA')->value('nomor_dokumen'));
        $this->assertSame('001/4th ASIC/2027', IssuedDocument::where('jenis_dokumen', 'Penolakan')->value('nomor_dokumen'));
        $this->assertSame('001/INV/4th ASIC/2027', IssuedDocument::where('jenis_dokumen', 'Invoice')->value('nomor_dokumen'));
    }

    // ------------------------------------------------------ download & otorisasi

    private function issuedLoa(): IssuedDocument
    {
        $reg = $this->registration('1');
        $this->fee();
        $this->review($this->abstractFor($reg)->id_abstract)->assertOk();

        return IssuedDocument::where('jenis_dokumen', 'LOA')->firstOrFail();
    }

    public function test_owner_can_list_and_download_own_documents(): void
    {
        $loa = $this->issuedLoa();

        $this->as(Role::ROLE_PESERTA, '1')->getJson("/api/registrations/{$loa->id_registration}/documents")
            ->assertOk()->assertJsonCount(2, 'data'); // LOA + Invoice
        $res = $this->as(Role::ROLE_PESERTA, '1')->get("/api/documents/{$loa->id_document}/download");
        $res->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_other_participant_cannot_access_documents(): void
    {
        $loa = $this->issuedLoa();
        $this->registration('2');

        $this->as(Role::ROLE_PESERTA, '2')->get("/api/documents/{$loa->id_document}/download")->assertForbidden();
        $this->as(Role::ROLE_PESERTA, '2')->getJson("/api/registrations/{$loa->id_registration}/documents")->assertForbidden();
    }

    public function test_staff_access_depends_on_document_type(): void
    {
        $loa = $this->issuedLoa();
        $invoiceDoc = IssuedDocument::where('jenis_dokumen', 'Invoice')->firstOrFail();

        // Tim Abstrak: LOA ya, Invoice tidak. Bendahara: Invoice ya, LOA tidak.
        $this->as(Role::ROLE_TIM_ABSTRAK)->get("/api/documents/{$loa->id_document}/download")->assertOk();
        $this->as(Role::ROLE_TIM_ABSTRAK)->get("/api/documents/{$invoiceDoc->id_document}/download")->assertForbidden();
        $this->as(Role::ROLE_BENDAHARA)->get("/api/documents/{$invoiceDoc->id_document}/download")->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->get("/api/documents/{$loa->id_document}/download")->assertForbidden();
        $this->as(Role::ROLE_ADMIN)->get("/api/documents/{$loa->id_document}/download")->assertOk();
    }

    public function test_guest_and_missing_document_and_missing_file(): void
    {
        $loa = $this->issuedLoa();
        $this->app['auth']->forgetGuards(); // lepaskan user dari langkah persiapan
        $this->getJson("/api/documents/{$loa->id_document}/download")->assertUnauthorized();
        $this->as(Role::ROLE_ADMIN)->getJson('/api/documents/9999/download')->assertNotFound();
        $this->as(Role::ROLE_ADMIN)->getJson('/api/registrations/9999/documents')->assertNotFound();

        Storage::disk('local')->delete($loa->file_document);
        $this->as(Role::ROLE_ADMIN)->getJson("/api/documents/{$loa->id_document}/download")->assertNotFound();
    }

    private function pdfText(IssuedDocument $d): string
    {
        return (new Parser)->parseContent(Storage::disk('local')->get($d->file_document))->getText();
    }
}
