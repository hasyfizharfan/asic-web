<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\IssuedDocument;
use App\Models\PaymentProof;
use App\Models\PaymentVerification;
use App\Models\Registration;
use App\Models\RegistrationAddOn;
use App\Models\Role;
use App\Services\InvoiceIssuer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Task 3 (invoice & receipt) + Task 5 (unggah & verifikasi pembayaran). */
class InvoiceAndPaymentTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    private function issue(Registration $r): array
    {
        return app(InvoiceIssuer::class)->issueFor($r);
    }

    private function listenerInvoice(string $suffix = '1'): Invoice
    {
        $this->fee(['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0]);
        $reg = $this->registration($suffix, ['participation_type' => 'Listener', 'registration_type' => 'Student', 'attendance_mode' => 'Daring', 'publication_option' => 'Tidak']);

        return Invoice::where('id_registration', $reg->id_registration)->firstOrFail();
    }

    // ------------------------------------------------------------ Task 3

    public function test_listener_gets_invoice_automatically_when_registration_is_created(): void
    {
        $inv = $this->listenerInvoice();

        $this->assertSame('Menunggu Pembayaran', $inv->status_pembayaran);
        $this->assertSame('500000.00', (string) $inv->total_tagihan);
        $this->assertSame(now()->addDays(7)->toDateString(), $inv->tanggal_jatuh_tempo->toDateString());
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'Invoice')->count());
    }

    public function test_presenter_does_not_get_invoice_before_acceptance(): void
    {
        $this->fee();
        $reg = $this->registration('1');
        $this->assertSame(0, Invoice::count());
        $this->assertSame('skipped', $this->issue($reg)['status']);
    }

    public function test_registration_without_fee_is_skipped_not_failed(): void
    {
        $reg = $this->registration('1', ['participation_type' => 'Listener']);
        $this->assertSame(0, Invoice::count());
        $this->assertSame('skipped', $this->issue($reg->fresh())['status']);
    }

    public function test_invoice_total_includes_discount_and_addons_as_snapshot_lines(): void
    {
        $this->fee(['amount' => 1500000, 'discount' => 200000]);
        $addOn = $this->addOn();
        // Urutan seperti RegistrationController::store: registrasi + add-on dalam satu transaksi.
        $reg = \Illuminate\Support\Facades\DB::transaction(function () use ($addOn) {
            $r = $this->registration('1', ['participation_type' => 'Listener']);
            RegistrationAddOn::create(['id_registration' => $r->id_registration, 'id_add_on' => $addOn->id_add_on, 'jumlah' => 2, 'subtotal' => 500000]);

            return $r;
        });

        $inv = Invoice::where('id_registration', $reg->id_registration)->firstOrFail();
        $this->assertSame('1800000.00', (string) $inv->total_tagihan); // 1,5jt - 0,2jt + 0,5jt
        $lines = InvoiceDetail::where('id_invoice', $inv->id_invoice)->orderBy('id_invoice_detail')->get();
        $this->assertCount(3, $lines);
        $this->assertSame(-200000.0, (float) $lines[1]->subtotal);
        $this->assertSame(2, (int) $lines[2]->jumlah);
        $this->assertSame(1800000.0, (float) $lines->sum('subtotal'));
    }

    public function test_invoice_issuance_is_idempotent(): void
    {
        $this->fee();
        $reg = $this->registration('1', ['participation_type' => 'Listener']);
        $this->assertSame('exists', $this->issue($reg->fresh())['status']); // observer sudah menerbitkan
        $this->issue($reg->fresh());
        $this->assertSame(1, Invoice::count());
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'Invoice')->count());
    }

    public function test_cancelled_registration_gets_no_invoice(): void
    {
        $this->fee();
        $reg = $this->registration('1', ['participation_type' => 'Listener', 'registration_status' => 'Dibatalkan']);
        $this->assertSame(0, Invoice::where('id_registration', $reg->id_registration)->count());
    }

    public function test_old_invoice_does_not_change_when_tariff_is_updated(): void
    {
        $fee = $this->fee(['amount' => 1000000, 'discount' => 0]);
        $reg = $this->registration('1', ['participation_type' => 'Listener']);
        $inv = Invoice::where('id_registration', $reg->id_registration)->firstOrFail();
        $pdfBefore = Storage::disk('local')->get(IssuedDocument::where('jenis_dokumen', 'Invoice')->value('file_document'));

        // tarif terpakai tidak dapat diubah (409), dan tarif baru untuk periode lain tidak menyentuh invoice lama
        $this->as(Role::ROLE_BENDAHARA)->patchJson("/api/registration-fees/{$fee->id_fee}", ['amount' => 9999999])->assertStatus(409);
        $fee->newQuery()->whereKey($fee->id_fee)->update(['amount' => 9999999]); // pelanggaran langsung di DB sekalipun

        $inv->refresh();
        $this->assertSame('1000000.00', (string) $inv->total_tagihan);
        $this->assertSame(1000000.0, (float) InvoiceDetail::where('id_invoice', $inv->id_invoice)->sum('subtotal'));
        $this->assertSame($pdfBefore, Storage::disk('local')->get(IssuedDocument::where('jenis_dokumen', 'Invoice')->value('file_document')));
    }

    public function test_registration_endpoint_triggers_listener_invoice_including_addons(): void
    {
        $this->fee(['registration_type' => 'Regular', 'attendance_mode' => 'Luring', 'tujuan_publikasi' => 'Tidak', 'amount' => 1000000, 'discount' => 0]);
        $addOn = $this->addOn(250000);
        $this->as(Role::ROLE_PESERTA, '9');

        $this->postJson('/api/registrations', [
            'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => 'Regular',
            'attendance_mode' => 'Luring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak',
            'add_ons' => [['id_add_on' => $addOn->id_add_on, 'jumlah' => 2]],
        ])->assertCreated();

        $this->assertSame('1500000.00', (string) Invoice::firstOrFail()->total_tagihan); // add-on ikut terhitung
    }

    // ------------------------------------------------------------ Task 5

    private function upload(Invoice $inv, string $suffix = '1', array $over = [])
    {
        return $this->as(Role::ROLE_PESERTA, $suffix)->postJson("/api/invoices/{$inv->id_invoice}/payment-proofs", $over + [
            'file_bukti' => UploadedFile::fake()->image('bukti.jpg'), 'nominal_transfer' => 500000, 'catatan_peserta' => 'Transfer BNI',
        ]);
    }

    private function pending(): array
    {
        $inv = $this->listenerInvoice();
        $this->upload($inv)->assertCreated();

        return [$inv->fresh(), PaymentProof::where('id_invoice', $inv->id_invoice)->latest('id_payment_proof')->firstOrFail()];
    }

    public function test_participant_uploads_proof_and_status_becomes_waiting_verification(): void
    {
        $inv = $this->listenerInvoice();
        $this->upload($inv)->assertCreated()->assertJsonPath('data.status_pembayaran', 'Menunggu Verifikasi');

        $proof = PaymentProof::firstOrFail();
        $this->assertTrue(Storage::disk('local')->exists($proof->file_bukti));
        $this->assertSame('Menunggu Verifikasi', $inv->fresh()->status_pembayaran);
    }

    public function test_upload_validates_file_type_size_and_ownership_and_status(): void
    {
        $inv = $this->listenerInvoice();
        $this->upload($inv, '1', ['file_bukti' => UploadedFile::fake()->create('x.exe', 10)])->assertUnprocessable()->assertJsonValidationErrors('file_bukti');
        $this->upload($inv, '1', ['file_bukti' => UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')])->assertUnprocessable();
        $this->upload($inv, '1', ['file_bukti' => null])->assertUnprocessable();

        $this->registration('2');
        $this->upload($inv, '2')->assertForbidden(); // bukan pemilik
        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/invoices/{$inv->id_invoice}/payment-proofs", [])->assertForbidden();

        $this->upload($inv)->assertCreated();
        $this->upload($inv)->assertStatus(409); // sudah Menunggu Verifikasi
        $this->as(Role::ROLE_PESERTA)->postJson('/api/invoices/9999/payment-proofs', ['file_bukti' => UploadedFile::fake()->image('a.jpg')])->assertNotFound();
        $this->assertSame(1, PaymentProof::count());
    }

    public function test_upload_after_due_date_is_refused(): void
    {
        $inv = $this->listenerInvoice();
        $inv->update(['tanggal_jatuh_tempo' => now()->subDay()->toDateString()]);
        $this->upload($inv)->assertUnprocessable();
        $this->assertSame(0, PaymentProof::count());
    }

    public function test_treasurer_lists_pending_payments_and_views_detail_and_file(): void
    {
        [$inv, $proof] = $this->pending();
        $other = $this->listenerInvoice('2'); // masih Menunggu Pembayaran

        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/payments')->assertOk()->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id_invoice', $inv->id_invoice);
        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/payments?status=all')->assertOk()->assertJsonPath('pagination.total', 2);
        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/payments?status=Bogus')->assertUnprocessable();
        $this->as(Role::ROLE_BENDAHARA)->getJson("/api/payments/{$inv->id_invoice}")->assertOk()->assertJsonCount(1, 'data.payment_proofs');
        $this->as(Role::ROLE_BENDAHARA)->get("/api/payment-proofs/{$proof->id_payment_proof}/file")->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/payments/9999')->assertNotFound();
    }

    public function test_valid_verification_marks_invoice_verified_and_issues_receipt(): void
    {
        [$inv, $proof] = $this->pending();

        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$proof->id_payment_proof}/verify", ['hasil_verifikasi' => 'Valid'])
            ->assertOk()->assertJsonPath('data.status_pembayaran', 'Terverifikasi')
            ->assertJsonPath('data.receipt.nomor_dokumen', fn ($n) => str_starts_with($n, 'RCP/'))
            ->assertJsonPath('data.nominal_matches_invoice', true);

        $this->assertSame('Terverifikasi', $inv->fresh()->status_pembayaran);
        $v = PaymentVerification::firstOrFail();
        $this->assertSame('Valid', $v->hasil_verifikasi);
        $this->assertSame($this->user(Role::ROLE_BENDAHARA)->id_user, $v->id_treasurer);

        $receipt = IssuedDocument::where('jenis_dokumen', 'Receipt')->firstOrFail();
        $this->assertSame($inv->id_registration, $receipt->id_registration);
        $this->assertStringContainsString('500.000,00', Storage::disk('local')->get($receipt->file_document));
        $this->as(Role::ROLE_PESERTA, '1')->get("/api/documents/{$receipt->id_document}/download")->assertOk();
    }

    public function test_rejection_requires_reason_stores_it_and_allows_reupload(): void
    {
        [$inv, $proof] = $this->pending();
        $url = "/api/payment-proofs/{$proof->id_payment_proof}/verify";

        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Tidak Valid'])->assertUnprocessable()->assertJsonValidationErrors('alasan_penolakan');
        $this->assertSame(0, PaymentVerification::count());

        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Tidak Valid', 'alasan_penolakan' => 'Nominal tidak sesuai'])
            ->assertOk()->assertJsonPath('data.status_pembayaran', 'Ditolak')->assertJsonPath('data.receipt', null);
        $this->assertSame('Nominal tidak sesuai', PaymentVerification::firstOrFail()->alasan_penolakan);
        $this->assertSame(0, IssuedDocument::where('jenis_dokumen', 'Receipt')->count());

        // peserta unggah ulang → proof baru, riwayat verifikasi lama tetap tersimpan
        $this->upload($inv)->assertCreated();
        $this->assertSame('Menunggu Verifikasi', $inv->fresh()->status_pembayaran);
        $this->assertSame(2, PaymentProof::count());
        $this->assertSame(1, PaymentVerification::count());
    }

    public function test_double_verification_is_blocked(): void
    {
        [$inv, $proof] = $this->pending();
        $url = "/api/payment-proofs/{$proof->id_payment_proof}/verify";

        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Valid'])->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Valid'])->assertStatus(409);
        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Tidak Valid', 'alasan_penolakan' => 'x'])->assertStatus(409);

        $this->assertSame(1, PaymentVerification::count());
        $this->assertSame(1, IssuedDocument::where('jenis_dokumen', 'Receipt')->count());
        $this->assertSame('Terverifikasi', $inv->fresh()->status_pembayaran);
    }

    public function test_only_latest_proof_can_be_verified(): void
    {
        [$inv, $first] = $this->pending();
        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$first->id_payment_proof}/verify", ['hasil_verifikasi' => 'Tidak Valid', 'alasan_penolakan' => 'Buram'])->assertOk();
        $this->upload($inv)->assertCreated();

        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$first->id_payment_proof}/verify", ['hasil_verifikasi' => 'Valid'])->assertStatus(409);
        $this->as(Role::ROLE_BENDAHARA)->postJson('/api/payment-proofs/9999/verify', ['hasil_verifikasi' => 'Valid'])->assertNotFound();
    }

    public function test_verification_is_validated_and_nominal_mismatch_is_reported(): void
    {
        [$inv, $proof] = $this->pending();
        $proof->update(['nominal_transfer' => 100]);
        $url = "/api/payment-proofs/{$proof->id_payment_proof}/verify";

        $this->as(Role::ROLE_BENDAHARA)->postJson($url, [])->assertUnprocessable()->assertJsonValidationErrors('hasil_verifikasi');
        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Mungkin'])->assertUnprocessable();
        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Valid'])->assertOk()->assertJsonPath('data.nominal_matches_invoice', false);
    }

    public function test_payment_endpoints_are_role_restricted(): void
    {
        [$inv, $proof] = $this->pending();
        $verify = "/api/payment-proofs/{$proof->id_payment_proof}/verify";

        foreach ([Role::ROLE_PESERTA, Role::ROLE_TIM_ABSTRAK] as $role) {
            $this->as($role)->getJson('/api/payments')->assertForbidden();
            $this->as($role)->getJson("/api/payments/{$inv->id_invoice}")->assertForbidden();
            $this->as($role)->get("/api/payment-proofs/{$proof->id_payment_proof}/file")->assertForbidden();
            $this->as($role)->postJson($verify, ['hasil_verifikasi' => 'Valid'])->assertForbidden();
        }
        // Sekretariat: hanya lihat (view_only), tidak boleh memverifikasi
        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/payments')->assertOk();
        $this->as(Role::ROLE_SEKRETARIAT)->postJson($verify, ['hasil_verifikasi' => 'Valid'])->assertForbidden();
        $this->as(Role::ROLE_ADMIN)->postJson($verify, ['hasil_verifikasi' => 'Valid'])->assertOk();
        $this->assertSame(1, PaymentVerification::count());
    }

    public function test_guest_cannot_access_payment_endpoints(): void
    {
        $this->getJson('/api/payments')->assertUnauthorized();
        $this->postJson('/api/payment-proofs/1/verify', [])->assertUnauthorized();
    }

    public function test_verified_luring_participant_can_obtain_attendance_qr(): void
    {
        $this->fee(['registration_type' => 'Regular', 'attendance_mode' => 'Luring', 'tujuan_publikasi' => 'Tidak', 'amount' => 1000, 'discount' => 0]);
        $reg = $this->registration('1', ['participation_type' => 'Listener', 'publication_option' => 'Tidak']);
        $inv = Invoice::where('id_registration', $reg->id_registration)->firstOrFail();
        $this->upload($inv)->assertCreated();
        $proof = PaymentProof::firstOrFail();

        $this->as(Role::ROLE_PESERTA, '1')->getJson("/api/registrations/{$reg->id_registration}/attendance-qr")->assertForbidden(); // belum terverifikasi
        $this->as(Role::ROLE_BENDAHARA)->postJson("/api/payment-proofs/{$proof->id_payment_proof}/verify", ['hasil_verifikasi' => 'Valid'])->assertOk();
        $this->as(Role::ROLE_PESERTA, '1')->getJson("/api/registrations/{$reg->id_registration}/attendance-qr")->assertOk()->assertJsonStructure(['data' => ['qr_token']]);
    }
}
