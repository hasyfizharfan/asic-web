<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Role;
use App\Services\InvoiceIssuer;
use App\Support\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Dukungan mata uang IDR/USD pada tarif, invoice, dokumen, dan laporan. */
class CurrencySupportTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    private function feePayload(array $override = []): array
    {
        return $override + [
            'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => 'Regular',
            'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 120, 'discount' => 0, 'mata_uang' => 'USD',
        ];
    }

    private function listener(string $suffix, string $country): Registration
    {
        return $this->registration($suffix, ['attendance_mode' => 'Daring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak'], ['country' => $country]);
    }

    public function test_treasurer_creates_usd_fee_and_duplicate_is_rejected(): void
    {
        $this->as(Role::ROLE_BENDAHARA)->postJson('/api/registration-fees', $this->feePayload())->assertCreated()->assertJsonPath('data.mata_uang', 'USD');
        $this->as(Role::ROLE_BENDAHARA)->postJson('/api/registration-fees', $this->feePayload())->assertUnprocessable();
        // Kombinasi sama dengan mata uang berbeda tetap boleh.
        $this->as(Role::ROLE_BENDAHARA)->postJson('/api/registration-fees', $this->feePayload(['mata_uang' => 'IDR', 'amount' => 1500000]))->assertCreated();
        $this->as(Role::ROLE_BENDAHARA)->postJson('/api/registration-fees', $this->feePayload(['mata_uang' => 'EUR']))->assertUnprocessable();

        $this->getJson('/api/public/registration-fees')->assertOk()->assertJsonPath('data.0.mata_uang', fn ($v) => in_array($v, ['IDR', 'USD'], true));
    }

    public function test_indonesian_gets_idr_invoice_and_foreign_participant_gets_usd_invoice(): void
    {
        $this->fee(['attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 1500000, 'discount' => 0]);
        $this->fee(['attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 120, 'discount' => 0, 'mata_uang' => 'USD']);

        $local = $this->listener('id', 'Indonesia');
        $foreign = $this->listener('my', 'Malaysia');

        $idr = Invoice::where('id_registration', $local->id_registration)->firstOrFail();
        $usd = Invoice::where('id_registration', $foreign->id_registration)->firstOrFail();
        $this->assertSame(['IDR', '1500000.00'], [$idr->mata_uang, (string) $idr->total_tagihan]);
        $this->assertSame(['USD', '120.00'], [$usd->mata_uang, (string) $usd->total_tagihan]);
    }

    public function test_missing_usd_fee_is_skipped_without_falling_back_to_idr(): void
    {
        $this->fee(['attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 1500000, 'discount' => 0]);
        $foreign = $this->listener('jp', 'Japan');

        $this->assertFalse(Invoice::where('id_registration', $foreign->id_registration)->exists());
        $result = app(InvoiceIssuer::class)->issueFor($foreign);
        $this->assertSame('skipped', $result['status']);
        $this->assertStringContainsString('USD', $result['reason']);
    }

    public function test_usd_participant_cannot_choose_idr_only_add_on(): void
    {
        $addOn = $this->addOn();
        Participant::create(['id_user' => $this->user(Role::ROLE_PESERTA, 'addon')->id_user, 'first_name' => 'Aiko', 'last_name' => 'Tanaka', 'country' => 'Japan']);

        $this->as(Role::ROLE_PESERTA, 'addon')->postJson('/api/registrations', [
            'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => 'Regular',
            'attendance_mode' => 'Daring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak',
            'add_ons' => [['id_add_on' => $addOn->id_add_on, 'jumlah' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('add_ons');
    }

    public function test_money_format_and_words_follow_currency(): void
    {
        $this->assertSame('1,500,000', Currency::plain(1500000, 'IDR'));
        $this->assertSame('120.50', Currency::plain(120.5, 'USD'));
        $this->assertSame('Five hundred thousand rupiah', Currency::words(500000, 'IDR'));
        $this->assertSame('One hundred twenty US dollars and fifty cents', Currency::words(120.5, 'USD'));
        $this->assertSame('IDR', Currency::forCountry('indonesia'));
        $this->assertSame('IDR', Currency::forCountry(null));
        $this->assertSame('USD', Currency::forCountry('Malaysia'));
    }

    public function test_usd_invoice_pdf_uses_us_dollars(): void
    {
        $this->fee(['attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 120, 'discount' => 0, 'mata_uang' => 'USD']);
        $foreign = $this->listener('pdf', 'Singapore');
        $doc = IssuedDocument::where('id_registration', $foreign->id_registration)->where('jenis_dokumen', 'Invoice')->firstOrFail();

        $text = (new Parser)->parseContent(Storage::disk('local')->get($doc->file_document))->getText();
        $this->assertStringContainsString('TOTAL (USD)', $text);
        $this->assertStringContainsString('120.00', $text);
    }
}
