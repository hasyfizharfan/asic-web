<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\RegistrationFee;
use App\Models\Role;
use App\Support\PaymentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/**
 * Kesepakatan mitra 18 Nov 2026: pembayaran lewat transfer bank Mandiri, PayPal, Wise, atau Flip;
 * halaman staf tersedia dalam dua bahasa.
 */
class PaymentMethodsAndStaffLocaleTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Storage::fake('local');
        Mail::fake();
    }

    private function listenerInvoice(): Invoice
    {
        RegistrationFee::create([
            'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period, 'registration_type' => 'Student',
            'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0,
        ]);
        $reg = $this->registration('1', ['participation_type' => 'Listener', 'registration_type' => 'Student', 'attendance_mode' => 'Daring', 'publication_option' => 'Tidak']);

        return Invoice::where('id_registration', $reg->id_registration)->firstOrFail();
    }

    public function test_participant_records_the_payment_method_used(): void
    {
        $inv = $this->listenerInvoice();
        $this->as(Role::ROLE_PESERTA, '1')->postJson("/api/invoices/{$inv->id_invoice}/payment-proofs", [
            'file_bukti' => UploadedFile::fake()->image('bukti.jpg'), 'metode_pembayaran' => 'wise',
        ])->assertCreated();

        $this->assertSame('wise', PaymentProof::firstOrFail()->metode_pembayaran);
        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/payments/'.$inv->id_invoice)->assertOk()
            ->assertJsonPath('data.payment_proofs.0.metode_pembayaran', 'wise');
    }

    public function test_unknown_payment_method_is_rejected(): void
    {
        $inv = $this->listenerInvoice();
        $this->as(Role::ROLE_PESERTA, '1')->postJson("/api/invoices/{$inv->id_invoice}/payment-proofs", [
            'file_bukti' => UploadedFile::fake()->image('bukti.jpg'), 'metode_pembayaran' => 'bitcoin',
        ])->assertUnprocessable()->assertJsonValidationErrors('metode_pembayaran');
    }

    public function test_treasurer_sets_paypal_account_shown_on_checkout_and_invoice_options(): void
    {
        $this->as(Role::ROLE_BENDAHARA)->putJson('/api/payment-settings', [
            'bank_name' => 'Bank Mandiri', 'account_no' => '000-TEST', 'paypal_account' => 'paypal-uji@example.test',
        ])->assertOk();
        $this->assertSame('paypal-uji@example.test', PaymentSettings::current()['paypal_account']);

        $this->listenerInvoice();
        $html = $this->actingAs($this->user(Role::ROLE_PESERTA, '1'))->get('/participant/payment/checkout')->assertOk()->getContent();
        $this->assertStringContainsString('paypal-uji@example.test', html_entity_decode($html));
        $this->assertStringContainsString(__('asic-participant.portal.checkout.method_wiseflip_desc'), $html);
        $this->assertStringContainsString('name="metode_pembayaran"', $html);
    }

    public function test_paypal_option_hidden_when_no_paypal_account(): void
    {
        $this->listenerInvoice();
        $html = $this->actingAs($this->user(Role::ROLE_PESERTA, '1'))->get('/participant/payment/checkout')->assertOk()->getContent();
        $this->assertStringNotContainsString('paypal', strtolower(html_entity_decode(substr($html, strpos($html, 'methods:'), 200))));
    }

    public function test_staff_pages_render_in_english(): void
    {
        $inv = $this->listenerInvoice();
        $pages = [
            Role::ROLE_SEKRETARIAT => ['/secretariat/dashboard' => 'Secretariat dashboard', '/secretariat/attendance' => 'Attendance and presentations', '/secretariat/announcements' => 'Announcements &amp; communication channels', '/secretariat/content' => 'Website content management'],
            Role::ROLE_BENDAHARA => ['/treasurer/dashboard' => 'Treasurer overview', '/treasurer/payments/'.$inv->id_invoice => 'Back to queue'],
            Role::ROLE_ADMIN => ['/admin/permissions' => 'Role and permission matrix', '/admin/signatures' => 'Digital signatures and stamps', '/admin/settings' => 'Settings'],
        ];
        foreach ($pages as $role => $list) {
            foreach ($list as $url => $english) {
                $html = $this->actingAs($this->user($role))->withSession(['locale' => 'en'])->get($url)->assertOk()->getContent();
                $this->assertStringContainsString($english, $html, $url);
                $this->assertStringNotContainsString('asic-secretariat.', $html, $url);
                $this->assertStringNotContainsString('asic-admin.', $html, $url);
                $this->assertStringNotContainsString('asic-treasurer.', $html, $url);
            }
        }
    }
}
