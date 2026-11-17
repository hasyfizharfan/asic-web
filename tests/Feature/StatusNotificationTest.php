<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\PaymentProof;
use App\Models\Registration;
use App\Models\Role;
use App\Models\UserAccount;
use App\Services\AbstractDecisionProcessor;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** KF-30: notifikasi perubahan status di dalam situs. */
class StatusNotificationTest extends TestCase
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

    private function owner(Registration $registration): UserAccount
    {
        return $registration->fresh('participant.userAccount')->participant->userAccount;
    }

    /** @return array<string, int> */
    private function kinds(Registration $registration): array
    {
        return $this->owner($registration)->notifications()->get()->countBy(fn ($n) => $n->data['kind'])->all();
    }

    private function listener(string $suffix): Registration
    {
        return $this->registration($suffix, ['participation_type' => 'Listener', 'publication_option' => 'Tidak']);
    }

    public function test_listener_registration_notifies_loa_and_invoice_once(): void
    {
        $registration = $this->listener('listener');
        // Penerbitan ulang yang idempoten tidak menggandakan.
        app(DocumentService::class)->issue($registration, 'LOA');

        $this->assertSame(['invoice_issued' => 1, 'loa_issued' => 1], collect($this->kinds($registration))->sortKeys()->all());
    }

    public function test_abstract_decisions_notify_owner_once(): void
    {
        $accepted = $this->registration('acc');
        $abstract = $this->abstractFor($accepted, ['status_abstrak' => 'Diterima']);
        app(AbstractDecisionProcessor::class)->process($abstract);
        app(AbstractDecisionProcessor::class)->process($abstract->fresh());
        $this->assertSame(['abstract_accepted' => 1, 'invoice_issued' => 1, 'loa_issued' => 1], collect($this->kinds($accepted))->sortKeys()->all());

        $rejected = $this->registration('rej');
        app(AbstractDecisionProcessor::class)->process($this->abstractFor($rejected, ['status_abstrak' => 'Ditolak']));
        $this->assertSame(['abstract_rejected' => 1, 'rejection_issued' => 1], collect($this->kinds($rejected))->sortKeys()->all());
    }

    public function test_payment_verification_notifies_valid_and_invalid(): void
    {
        $registration = $this->listener('pay');
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();
        $upload = fn () => $this->as(Role::ROLE_PESERTA, 'pay')->postJson("/api/invoices/{$invoice->id_invoice}/payment-proofs", [
            'nominal_transfer' => 1500000, 'file_bukti' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertCreated();

        $upload();
        $proof = PaymentProof::where('id_invoice', $invoice->id_invoice)->latest('id_payment_proof')->firstOrFail();
        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$proof->id_payment_proof}/verify", ['hasil_verifikasi' => 'Tidak Valid', 'alasan_penolakan' => 'Nominal kurang'])->assertOk();

        $upload();
        $proof = PaymentProof::where('id_invoice', $invoice->id_invoice)->latest('id_payment_proof')->firstOrFail();
        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$proof->id_payment_proof}/verify", ['hasil_verifikasi' => 'Valid'])->assertOk();

        $kinds = $this->kinds($registration);
        $this->assertSame(1, $kinds['payment_invalid']);
        $this->assertSame(1, $kinds['payment_valid']);
        $this->assertSame(1, $kinds['receipt_issued']);

        $invalid = $this->as(Role::ROLE_PESERTA, 'pay')->getJson('/api/me/notifications')->assertOk()->collect('data')->firstWhere('kind', 'payment_invalid');
        $this->assertStringContainsString('Nominal kurang', $invalid['body']);
    }

    public function test_invoice_extension_and_expiry_notify_owner(): void
    {
        $registration = $this->listener('due');
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();

        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/invoices/{$invoice->id_invoice}/extend", ['tanggal_jatuh_tempo_baru' => now()->addDays(10)->toDateString()])->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->patchJson("/api/invoices/{$invoice->id_invoice}/expire")->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->patchJson("/api/invoices/{$invoice->id_invoice}/expire")->assertOk();

        $kinds = $this->kinds($registration);
        $this->assertSame(1, $kinds['invoice_extended']);
        $this->assertSame(1, $kinds['invoice_expired']);
    }

    public function test_reregistration_notifies_owner(): void
    {
        $registration = $this->listener('rereg');
        Invoice::where('id_registration', $registration->id_registration)->update(['status_pembayaran' => 'Terverifikasi']);
        $registration->forceFill(['qr_attendance_token' => 'TOKEN-rereg'])->save();
        Carbon::setTestNow(Carbon::parse($this->event->tanggal_mulai.' 09:00:00', config('app.timezone')));

        $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/events/{$this->event->id_event}/reregistration/scan", ['qr_token' => 'TOKEN-rereg'])->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->postJson("/api/events/{$this->event->id_event}/reregistration/scan", ['qr_token' => 'TOKEN-rereg'])->assertStatus(409);
        Carbon::setTestNow();

        $this->assertSame(1, $this->kinds($registration)['reregistered']);
    }

    public function test_notification_failure_does_not_cancel_document_issuance(): void
    {
        Schema::drop('notifications');

        $registration = $this->listener('fail');

        $this->assertTrue(IssuedDocument::where('id_registration', $registration->id_registration)->where('jenis_dokumen', 'LOA')->exists());
        $this->assertTrue(Invoice::where('id_registration', $registration->id_registration)->exists());
    }

    public function test_unread_count_mark_read_read_all_and_isolation(): void
    {
        $mine = $this->listener('me');
        $other = $this->listener('other');
        $otherNotification = $this->owner($other)->notifications()->firstOrFail();

        $this->as(Role::ROLE_PESERTA, 'me')->getJson('/api/me/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 2);
        $list = $this->as(Role::ROLE_PESERTA, 'me')->getJson('/api/me/notifications')->assertOk()->assertJsonPath('pagination.total', 2);
        $first = $list->json('data.0.id');

        $this->as(Role::ROLE_PESERTA, 'me')->patchJson("/api/me/notifications/{$first}/read")->assertOk();
        $this->as(Role::ROLE_PESERTA, 'me')->getJson('/api/me/notifications/unread-count')->assertJsonPath('data.unread_count', 1);
        $this->as(Role::ROLE_PESERTA, 'me')->getJson('/api/me/notifications?unread=1')->assertJsonPath('pagination.total', 1);

        // Pengguna A tidak dapat membaca/menandai notifikasi pengguna B.
        $this->as(Role::ROLE_PESERTA, 'me')->patchJson("/api/me/notifications/{$otherNotification->id}/read")->assertNotFound();
        $this->assertNull($otherNotification->fresh()->read_at);
        $this->assertNotContains($otherNotification->id, $this->as(Role::ROLE_PESERTA, 'me')->getJson('/api/me/notifications')->collect('data')->pluck('id'));

        $this->as(Role::ROLE_PESERTA, 'me')->postJson('/api/me/notifications/read-all')->assertOk()->assertJsonPath('data.updated', 1);
        $this->as(Role::ROLE_PESERTA, 'me')->getJson('/api/me/notifications/unread-count')->assertJsonPath('data.unread_count', 0);
        $this->assertNull($otherNotification->fresh()->read_at);
        $this->assertSame($mine->id_registration, $this->owner($mine)->notifications()->first()->data['id_registration']);

        Auth::forgetGuards();
        $this->getJson('/api/me/notifications')->assertUnauthorized();
    }

    public function test_notifications_page_renders_for_participant(): void
    {
        $this->listener('page');
        $this->as(Role::ROLE_PESERTA, 'page')->get('/participant/notifications')->assertOk()
            ->assertSee(__('asic-notifications.page.mark_all_read'))->assertDontSee('Belum tersedia');
    }

    /** Email notifikasi benar-benar terkirim (transport array) dengan lampiran PDF, bahasa sesuai negara peserta. */
    /** Mailer sungguhan dengan transport array (setUp memakai Mail::fake). */
    private function realArrayMailer(): TransportInterface
    {
        config(['mail.default' => 'array']);
        $manager = new MailManager(app());
        app()->instance('mail.manager', $manager);
        Mail::swap($manager);

        return $manager->mailer('array')->getSymfonyTransport();
    }

    public function test_listener_receives_loa_and_invoice_emails_with_pdf_attachments(): void
    {
        $transport = $this->realArrayMailer();
        $registration = $this->listener('mail');
        $owner = $this->owner($registration);

        $messages = collect($transport->messages())->map(fn ($m) => $m->getOriginalMessage());
        $toOwner = $messages->filter(fn ($m) => collect($m->getTo())->contains(fn ($a) => $a->getAddress() === $owner->email))->values();
        $this->assertCount(2, $toOwner, 'LoA + invoice, tanpa email ganda');
        foreach ($toOwner as $message) {
            $this->assertCount(1, $message->getAttachments());
            $this->assertStringContainsString('application/pdf', $message->getAttachments()[0]->getMediaType().'/'.$message->getAttachments()[0]->getMediaSubtype());
            $this->assertStringContainsString('ASIC', $message->getSubject());
        }
    }

    public function test_email_can_be_switched_off(): void
    {
        $transport = $this->realArrayMailer();
        config(['asic.notify_email' => false]);
        $this->listener('nomail');
        $this->assertCount(0, $transport->messages());
    }

    public function test_reserved_test_domains_get_no_email(): void
    {
        $transport = $this->realArrayMailer();
        config(['asic.skip_reserved_email_domains' => true]);
        $this->listener('reserved'); // email @example.test
        $this->assertCount(0, $transport->messages());
    }
}
