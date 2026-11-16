<?php

namespace Tests\Feature;

use App\Models\CommunicationChannel;
use App\Models\Invoice;
use App\Models\IssuedDocument;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Temuan black box putaran 2: KF-11 periode, KF-23 informasi pembayaran, KF-32 tautan grup untuk pengunjung. */
class PeriodPaymentSettingsChannelsTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    public function test_admin_and_treasurer_manage_registration_periods(): void
    {
        $payload = ['id_event' => $this->event->id_event, 'nama_periode' => 'Regular', 'tanggal_mulai' => '2027-02-01', 'tanggal_selesai' => '2027-05-15', 'status_periode' => 'Aktif'];

        $this->as(Role::ROLE_PESERTA, 'p')->postJson('/api/registration-periods', $payload)->assertForbidden();
        $this->as(Role::ROLE_SEKRETARIAT)->postJson('/api/registration-periods', $payload)->assertForbidden();
        $this->as(Role::ROLE_ADMIN)->postJson('/api/registration-periods', ['tanggal_selesai' => '2027-01-01'] + $payload)->assertUnprocessable()->assertJsonValidationErrors('tanggal_selesai');

        $id = $this->as(Role::ROLE_ADMIN)->postJson('/api/registration-periods', $payload)->assertCreated()->json('data.id_period');
        $this->as(Role::ROLE_BENDAHARA)->patchJson("/api/registration-periods/{$id}", ['status_periode' => 'Tidak Aktif'])->assertOk()->assertJsonPath('data.status_periode', 'Tidak Aktif');
        $this->as(Role::ROLE_BENDAHARA)->getJson('/api/registration-periods?id_event='.$this->event->id_event)->assertOk()->assertJsonCount(2, 'data');

        $this->fee(); // tarif memakai periode bawaan fixture
        $this->as(Role::ROLE_ADMIN)->deleteJson("/api/registration-periods/{$this->period->id_period}")->assertStatus(409);
        $this->as(Role::ROLE_ADMIN)->deleteJson("/api/registration-periods/{$id}")->assertOk();
        $this->assertNull(RegistrationPeriod::find($id));

        $this->as(Role::ROLE_ADMIN)->get('/admin/settings')->assertOk()->assertSee(__('asic-treasurer.settings_panel.periods_title'));
        $this->as(Role::ROLE_BENDAHARA)->get('/treasurer/tariffs')->assertOk()->assertSee(__('asic-treasurer.settings_panel.payment_title'));
    }

    public function test_payment_settings_appear_on_next_invoice_and_checkout(): void
    {
        $this->as(Role::ROLE_SEKRETARIAT)->putJson('/api/payment-settings', ['bank_name' => 'Bank Uji'])->assertForbidden();
        $this->as(Role::ROLE_BENDAHARA)->putJson('/api/payment-settings', ['account_no' => '<script>'])->assertUnprocessable();
        $this->as(Role::ROLE_BENDAHARA)->putJson('/api/payment-settings', ['bank_name' => 'Bank Uji Nusantara', 'account_no' => '1234 5678 90', 'qr_info' => 'QRIS panitia'])
            ->assertOk()->assertJsonPath('data.bank_name', 'Bank Uji Nusantara');

        $this->fee(['tujuan_publikasi' => 'Tidak']);
        $registration = $this->registration('pay', ['participation_type' => 'Listener', 'publication_option' => 'Tidak']);
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();
        $this->assertSame('QRIS panitia', $invoice->payment_qr_info);
        $doc = IssuedDocument::where('id_registration', $registration->id_registration)->where('jenis_dokumen', 'Invoice')->firstOrFail();
        $text = (new Parser)->parseContent(Storage::disk('local')->get($doc->file_document))->getText();
        $this->assertStringContainsString('Bank Uji Nusantara', $text);
        $this->as(Role::ROLE_PESERTA, 'pay')->get('/participant/payment/checkout?id_registration='.$registration->id_registration)->assertOk()->assertSee('Bank Uji Nusantara');
    }

    public function test_visitors_see_active_communication_channels(): void
    {
        CommunicationChannel::create(['id_event' => $this->event->id_event, 'nama_kanal' => 'Grup WA Peserta', 'jenis_kanal' => 'WhatsApp', 'url' => 'https://chat.whatsapp.com/contoh', 'status' => 'Aktif']);
        CommunicationChannel::create(['id_event' => $this->event->id_event, 'nama_kanal' => 'Grup Lama', 'jenis_kanal' => 'WhatsApp', 'url' => 'https://chat.whatsapp.com/lama', 'status' => 'Nonaktif']);

        $this->getJson("/api/public/events/{$this->event->id_event}/communication-channels")->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.url', 'https://chat.whatsapp.com/contoh');
        $this->get('/contact')->assertOk()->assertSee('https://chat.whatsapp.com/contoh')->assertDontSee('Grup Lama');
    }

    public function test_secretariat_pages_for_abstract_selection_and_payment_verification_are_forbidden(): void
    {
        $this->as(Role::ROLE_SEKRETARIAT)->get('/reviewer/queue')->assertForbidden();
        $this->as(Role::ROLE_SEKRETARIAT)->get('/treasurer/payments')->assertForbidden();
    }
}
