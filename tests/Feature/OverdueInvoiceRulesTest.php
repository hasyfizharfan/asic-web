<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use App\Notifications\StatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/**
 * Kesepakatan mitra (Meeting 2): early bird hangus bila lewat jatuh tempo dan pindah ke harga reguler;
 * harga reguler bisa diperpanjang maksimal 2 kali, lalu peserta dibatalkan (K-05/K-06 docs/KEPUTUSAN.md).
 */
class OverdueInvoiceRulesTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    private RegistrationPeriod $regular;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
        $this->regular = RegistrationPeriod::create([
            'id_event' => $this->event->id_event, 'nama_periode' => 'Regular',
            'tanggal_mulai' => now()->addMonth()->toDateString(), 'tanggal_selesai' => now()->addYear()->toDateString(), 'status_periode' => 'Tidak Aktif',
        ]);
        foreach ([[$this->period, 500000], [$this->regular, 750000]] as [$period, $amount]) {
            RegistrationFee::create([
                'id_event' => $this->event->id_event, 'id_period' => $period->id_period, 'registration_type' => 'Student',
                'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => $amount, 'discount' => 0,
            ]);
        }
    }

    private function listenerInvoice(?RegistrationPeriod $period = null): Invoice
    {
        $reg = $this->registration('1', ['participation_type' => 'Listener', 'registration_type' => 'Student', 'attendance_mode' => 'Daring',
            'publication_option' => 'Tidak', 'id_period' => ($period ?? $this->period)->id_period]);

        return Invoice::where('id_registration', $reg->id_registration)->firstOrFail();
    }

    private function extend(Invoice $inv, int $days = 5)
    {
        return $this->as(Role::ROLE_BENDAHARA)->postJson("/api/invoices/{$inv->id_invoice}/extend", ['tanggal_jatuh_tempo_baru' => now()->addDays($days)->toDateString()]);
    }

    public function test_overdue_early_bird_invoice_moves_to_regular_rate_with_same_number(): void
    {
        Notification::fake();
        $inv = $this->listenerInvoice();
        $doc = IssuedDocument::where('id_registration', $inv->id_registration)->where('jenis_dokumen', 'Invoice')->firstOrFail();
        $this->assertSame('500000.00', (string) $inv->total_tagihan);

        $this->travel(8)->days();
        $this->artisan('invoices:process-overdue')->assertSuccessful();

        $inv->refresh();
        $this->assertSame('750000.00', (string) $inv->total_tagihan);
        $this->assertSame('Menunggu Pembayaran', $inv->status_pembayaran);
        $this->assertSame(now()->addDays(7)->toDateString(), $inv->tanggal_jatuh_tempo->toDateString());
        $this->assertSame($this->regular->id_period, $inv->registration->fresh()->id_period);
        $doc->refresh();
        $this->assertSame($inv->nomor_invoice, $doc->nomor_dokumen);
        $this->assertStringContainsString('Revised invoice', (new Parser)->parseContent(Storage::disk('local')->get($doc->file_document))->getText());
        Notification::assertSentTo($this->user(Role::ROLE_PESERTA, '1'), StatusChanged::class, fn ($n) => $n->kind === 'invoice_repriced');
    }

    public function test_early_bird_invoice_cannot_be_extended(): void
    {
        $inv = $this->listenerInvoice();
        $this->extend($inv)->assertStatus(409)->assertJsonPath('message', __('asic-api.invoice.extend_early_bird'));
    }

    public function test_regular_invoice_expires_can_be_extended_twice_then_registration_is_cancelled(): void
    {
        Notification::fake();
        $inv = $this->listenerInvoice($this->regular);

        $this->travel(8)->days();
        $this->artisan('invoices:process-overdue')->assertSuccessful();
        $this->assertSame('Kedaluwarsa', $inv->fresh()->status_pembayaran);

        $this->extend($inv)->assertOk();
        $this->assertSame('Menunggu Pembayaran', $inv->fresh()->status_pembayaran);
        $this->travel(6)->days();
        $this->artisan('invoices:process-overdue');
        $this->extend($inv)->assertOk();
        $this->assertSame(2, $inv->fresh()->jumlah_perpanjangan);
        $this->extend($inv)->assertStatus(409)->assertJsonPath('message', __('asic-api.invoice.extend_limit', ['max' => 2]));

        $this->travel(6)->days();
        $this->artisan('invoices:process-overdue');
        $this->assertSame('Dibatalkan', $inv->registration->fresh()->registration_status);
        Notification::assertSentTo($this->user(Role::ROLE_PESERTA, '1'), StatusChanged::class, fn ($n) => $n->kind === 'registration_cancelled');
    }

    public function test_paid_or_waiting_verification_invoices_are_not_touched_or_extended(): void
    {
        $inv = $this->listenerInvoice($this->regular);
        $inv->update(['status_pembayaran' => 'Menunggu Verifikasi']);
        $this->travel(8)->days();
        $this->artisan('invoices:process-overdue');
        $this->assertSame('Menunggu Verifikasi', $inv->fresh()->status_pembayaran);

        $inv->update(['status_pembayaran' => 'Terverifikasi']);
        $this->extend($inv)->assertStatus(409);
    }

    public function test_treasurer_detail_shows_extension_info(): void
    {
        $inv = $this->listenerInvoice();
        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/payments/'.$inv->id_invoice)->assertOk()
            ->assertJsonPath('data.extension.max', 2)->assertJsonPath('data.extension.early_bird', true);
    }

    public function test_participant_sees_abstract_team_notes_without_reviewer_identity(): void
    {
        $reg = $this->registration('1');
        $abstract = $this->abstractFor($reg, ['status_abstrak' => 'Ditolak']);
        $abstract->reviews()->create(['id_reviewer' => $this->user(Role::ROLE_TIM_ABSTRAK)->id_user, 'hasil_review' => 'Ditolak', 'catatan_review' => 'Metode belum dijelaskan.']);

        $json = $this->as(Role::ROLE_PESERTA, '1')->getJson('/api/registrations')->assertOk()->json('data.data.0.abstract_submission.latest_review');
        $this->assertSame('Metode belum dijelaskan.', $json['catatan_review']);
        $this->assertArrayNotHasKey('id_reviewer', $json);
    }
}
