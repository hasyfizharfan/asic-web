<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\RegistrationFee;
use App\Models\Role;
use App\Support\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Dasbor Bendahara: total per mata uang terpisah, dan satu baris per kategori (IDR & USD sebagai kolom). */
class TreasurerDashboardSummaryTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
    }

    private function verifiedInvoice(string $suffix, string $category, string $currency, float $amount, string $status = 'Terverifikasi'): void
    {
        $fee = RegistrationFee::firstOrCreate(
            ['id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => $category, 'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'mata_uang' => $currency],
            ['amount' => $amount, 'discount' => 0],
        );
        $registration = $this->registration($suffix, ['registration_type' => $category]);
        $invoice = Invoice::firstOrNew(['id_registration' => $registration->id_registration]);
        $invoice->forceFill([
            'id_fee' => $fee->id_fee, 'nomor_invoice' => 'T-'.$suffix, 'tanggal_jatuh_tempo' => now()->addWeek()->toDateString(),
            'total_tagihan' => $amount, 'mata_uang' => $currency, 'status_pembayaran' => $status,
        ])->save();
    }

    public function test_each_category_is_listed_once_with_currency_columns_and_invoice_counts(): void
    {
        $this->verifiedInvoice('a', 'Student', 'IDR', 500000);
        $this->verifiedInvoice('b', 'Student', 'IDR', 1000000);
        $this->verifiedInvoice('c', 'Student', 'USD', 50);
        $this->verifiedInvoice('d', 'Regular', 'IDR', 2000000);
        $this->verifiedInvoice('e', 'Regular', 'IDR', 9000000, 'Menunggu Verifikasi'); // belum terverifikasi: tidak dihitung

        $html = $this->as(Role::ROLE_BENDAHARA)->get(route('portal.treasurer.dashboard'))->assertOk()
            ->assertSee(Currency::format(3500000, 'IDR'))->assertSee(Currency::format(1500000, 'IDR'))->assertSee(Currency::format(50, 'USD'))
            ->assertSee('4 invoice terverifikasi')->assertSee('3 invoice')->assertSee('1 invoice')
            ->assertDontSee(Currency::format(12500000, 'IDR'))
            ->getContent();

        $this->assertSame(1, substr_count($html, '>Mahasiswa</p>'), 'Kategori Student hanya satu baris walau ada invoice IDR dan USD');
        $this->assertSame(1, substr_count($html, '>Umum / dosen</p>'));
        $this->assertLessThan(strpos($html, '>Umum / dosen</p>'), strpos($html, '>Mahasiswa</p>'));

        $this->withSession(['locale' => 'en'])->get(route('portal.treasurer.dashboard'))->assertOk()
            ->assertSee('4 verified invoices')->assertSee('Regular / lecturer')->assertSee('1 invoice');
    }

    public function test_empty_state_without_verified_payments(): void
    {
        $this->as(Role::ROLE_BENDAHARA)->get(route('portal.treasurer.dashboard'))->assertOk()
            ->assertSee('Belum ada pembayaran terverifikasi.')->assertSee('0 invoice');
    }
}
