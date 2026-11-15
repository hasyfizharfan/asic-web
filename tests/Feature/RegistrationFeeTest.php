<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use App\Models\UserAccount;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrationFeeTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    private RegistrationPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        // Project belum memasang Sanctum (guard `sanctum` tidak terdefinisi di config/auth.php).
        // Guard didefinisikan lokal di test agar `auth:sanctum` pada route dapat diuji
        // tanpa mengubah konfigurasi autentikasi global.
        config([
            'auth.guards.sanctum' => ['driver' => 'session', 'provider' => 'user_account'],
            'auth.providers.user_account' => ['driver' => 'eloquent', 'model' => UserAccount::class],
        ]);

        $this->seed([RoleSeeder::class, RolePermissionSeeder::class]);

        $this->event = Event::create([
            'nama_event' => 'ASIC 2027', 'tanggal_mulai' => '2027-08-01', 'tanggal_selesai' => '2027-08-03',
            'mode_kegiatan' => 'Hybrid', 'status_event' => 'Aktif',
        ]);
        $this->period = $this->makePeriod($this->event, 'Early Bird', 'Aktif');
    }

    // ---------------------------------------------------------------- helpers

    private function makePeriod(Event $event, string $name = 'Regular', string $status = 'Aktif'): RegistrationPeriod
    {
        return RegistrationPeriod::create([
            'id_event' => $event->id_event, 'nama_periode' => $name,
            'tanggal_mulai' => '2027-01-01', 'tanggal_selesai' => '2027-03-01', 'status_periode' => $status,
        ]);
    }

    private function userWithRole(string $roleName): UserAccount
    {
        return UserAccount::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '', $roleName)).'@example.test'],
            [
                'id_role' => Role::where('nama_role', $roleName)->value('id_role'),
                'password' => 'secret-pass-123',
                'status_akun' => 'Aktif',
            ]
        );
    }

    private function as(string $roleName): static
    {
        return $this->actingAs($this->userWithRole($roleName), 'sanctum');
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'id_event' => $this->event->id_event,
            'id_period' => $this->period->id_period,
            'registration_type' => 'Regular',
            'attendance_mode' => 'Luring',
            'tujuan_publikasi' => 'Prosiding',
            'amount' => 1500000,
            'discount' => 100000,
        ], $override);
    }

    private function makeFee(array $override = []): RegistrationFee
    {
        return RegistrationFee::create($this->payload($override));
    }

    private function makeInvoiceFor(RegistrationFee $fee): Invoice
    {
        $userId = $this->userWithRole(Role::ROLE_PESERTA)->id_user;
        $participantId = DB::table('participant')->insertGetId(['id_user' => $userId, 'first_name' => 'Budi']);
        $registration = Registration::create([
            'id_participant' => $participantId, 'id_event' => $fee->id_event, 'id_period' => $fee->id_period,
            'registration_type' => $fee->registration_type, 'attendance_mode' => $fee->attendance_mode,
            'participation_type' => 'Listener', 'publication_option' => 'Tidak',
        ]);

        return Invoice::create([
            'id_registration' => $registration->id_registration, 'id_fee' => $fee->id_fee,
            'nomor_invoice' => 'INV-TEST-001', 'tanggal_jatuh_tempo' => '2027-02-01',
            'total_tagihan' => 1400000, 'status_pembayaran' => 'Menunggu Pembayaran',
        ]);
    }

    // ------------------------------------------------------------------- CRUD

    public function test_bendahara_can_create_fee(): void
    {
        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.registration_type', 'Regular')
            ->assertJsonPath('data.amount', '1500000.00')
            ->assertJsonPath('data.discount', '100000.00')
            ->assertJsonPath('data.final_amount', '1400000.00');

        $this->assertDatabaseHas('registration_fee', [
            'id_event' => $this->event->id_event, 'tujuan_publikasi' => 'Prosiding',
        ]);
    }

    public function test_discount_defaults_to_zero_when_omitted(): void
    {
        $payload = $this->payload();
        unset($payload['discount']);

        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $payload)
            ->assertCreated()
            ->assertJsonPath('data.discount', '0.00')
            ->assertJsonPath('data.final_amount', '1500000.00');
    }

    public function test_index_lists_and_filters_fees(): void
    {
        $this->makeFee();
        $this->makeFee(['registration_type' => 'Student', 'amount' => 900000, 'discount' => 0]);

        $this->as(Role::ROLE_BENDAHARA)
            ->getJson('/api/registration-fees')
            ->assertOk()
            ->assertJsonPath('pagination.total', 2)
            ->assertJsonCount(2, 'data');

        $this->as(Role::ROLE_BENDAHARA)
            ->getJson('/api/registration-fees?registration_type=Student&attendance_mode=Luring&tujuan_publikasi=Prosiding&id_event='.$this->event->id_event)
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.registration_type', 'Student');
    }

    public function test_show_returns_detail_with_relations(): void
    {
        $fee = $this->makeFee();

        $this->as(Role::ROLE_BENDAHARA)
            ->getJson("/api/registration-fees/{$fee->id_fee}")
            ->assertOk()
            ->assertJsonPath('data.id_fee', $fee->id_fee)
            ->assertJsonPath('data.event.nama_event', 'ASIC 2027')
            ->assertJsonPath('data.period.nama_periode', 'Early Bird')
            ->assertJsonPath('data.is_used', false);
    }

    public function test_show_returns_json_404_for_unknown_fee(): void
    {
        $this->as(Role::ROLE_BENDAHARA)
            ->getJson('/api/registration-fees/9999')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_update_fee_partially(): void
    {
        $fee = $this->makeFee();

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", ['amount' => 2000000, 'discount' => 250000])
            ->assertOk()
            ->assertJsonPath('data.amount', '2000000.00')
            ->assertJsonPath('data.final_amount', '1750000.00');

        $this->assertDatabaseHas('registration_fee', ['id_fee' => $fee->id_fee, 'amount' => 2000000]);
    }

    public function test_update_does_not_flag_itself_as_duplicate(): void
    {
        $fee = $this->makeFee();

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", $this->payload(['amount' => 1600000]))
            ->assertOk();
    }

    public function test_destroy_unused_fee(): void
    {
        $fee = $this->makeFee();

        $this->as(Role::ROLE_BENDAHARA)
            ->deleteJson("/api/registration-fees/{$fee->id_fee}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('registration_fee', ['id_fee' => $fee->id_fee]);
    }

    public function test_destroy_unknown_fee_returns_404(): void
    {
        $this->as(Role::ROLE_BENDAHARA)->deleteJson('/api/registration-fees/9999')->assertNotFound();
    }

    // --------------------------------------------------------------- validasi

    public function test_required_fields_are_validated(): void
    {
        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['id_event', 'id_period', 'registration_type', 'attendance_mode', 'tujuan_publikasi', 'amount']);
    }

    public function test_enum_values_are_validated(): void
    {
        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload([
                'registration_type' => 'VIP', 'attendance_mode' => 'Hybrid', 'tujuan_publikasi' => 'Buku',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['registration_type', 'attendance_mode', 'tujuan_publikasi']);
    }

    public function test_all_valid_enum_values_are_accepted(): void
    {
        $this->as(Role::ROLE_BENDAHARA);
        foreach (['Student', 'Regular', 'Committee'] as $type) {
            foreach (['Daring', 'Luring'] as $mode) {
                foreach (['Prosiding', 'Jurnal', 'Tidak'] as $goal) {
                    $this->postJson('/api/registration-fees', $this->payload([
                        'registration_type' => $type, 'attendance_mode' => $mode, 'tujuan_publikasi' => $goal,
                    ]))->assertCreated();
                }
            }
        }
        $this->assertSame(18, RegistrationFee::count());
    }

    public function test_event_must_exist(): void
    {
        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload(['id_event' => 9999]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_event');
    }

    public function test_period_must_belong_to_the_event(): void
    {
        $otherEvent = Event::create([
            'nama_event' => 'Event Lain', 'tanggal_mulai' => '2027-09-01', 'tanggal_selesai' => '2027-09-02',
        ]);
        $foreignPeriod = $this->makePeriod($otherEvent, 'Periode Event Lain');

        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload(['id_period' => $foreignPeriod->id_period]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_period');
    }

    public function test_discount_cannot_exceed_amount(): void
    {
        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload(['amount' => 500000, 'discount' => 500000.01]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('discount');

        // diskon == amount diperbolehkan (tarif gratis)
        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload(['amount' => 500000, 'discount' => 500000]))
            ->assertCreated()
            ->assertJsonPath('data.final_amount', '0.00');
    }

    public function test_negative_amount_and_discount_are_rejected(): void
    {
        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload(['amount' => -1, 'discount' => -5]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'discount']);
    }

    public function test_update_validates_discount_against_stored_amount(): void
    {
        $fee = $this->makeFee(['amount' => 1000000, 'discount' => 0]);

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", ['discount' => 1000001])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('discount');

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", ['amount' => 100, 'discount' => 0])
            ->assertOk();

        $fee->update(['discount' => 50]);
        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", ['amount' => 10])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('discount');
    }

    public function test_update_validates_period_against_event(): void
    {
        $fee = $this->makeFee();
        $otherEvent = Event::create([
            'nama_event' => 'Event Lain', 'tanggal_mulai' => '2027-09-01', 'tanggal_selesai' => '2027-09-02',
        ]);
        $foreignPeriod = $this->makePeriod($otherEvent);

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", ['id_period' => $foreignPeriod->id_period])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_period');
    }

    // --------------------------------------------------------------- duplikasi

    public function test_duplicate_combination_is_rejected_on_store(): void
    {
        $this->makeFee();

        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload(['amount' => 1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('registration_type');

        $this->assertSame(1, RegistrationFee::count());
    }

    public function test_changing_any_dimension_is_not_a_duplicate(): void
    {
        $this->makeFee();
        $secondPeriod = $this->makePeriod($this->event, 'Regular');

        foreach ([
            ['registration_type' => 'Student'],
            ['attendance_mode' => 'Daring'],
            ['tujuan_publikasi' => 'Jurnal'],
            ['id_period' => $secondPeriod->id_period],
        ] as $override) {
            $this->as(Role::ROLE_BENDAHARA)
                ->postJson('/api/registration-fees', $this->payload($override))
                ->assertCreated();
        }
    }

    public function test_update_into_existing_combination_is_rejected(): void
    {
        $this->makeFee();
        $other = $this->makeFee(['registration_type' => 'Student']);

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$other->id_fee}", ['registration_type' => 'Regular'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('registration_type');
    }

    // -------------------------------------------------------------- otorisasi

    public function test_guest_is_unauthenticated(): void
    {
        $this->getJson('/api/registration-fees')->assertUnauthorized();
        $this->postJson('/api/registration-fees', $this->payload())->assertUnauthorized();
    }

    public function test_admin_has_full_access(): void
    {
        $this->as(Role::ROLE_ADMIN)->postJson('/api/registration-fees', $this->payload())->assertCreated();
    }

    public function test_roles_without_manage_tariffs_are_forbidden(): void
    {
        $fee = $this->makeFee();

        foreach ([Role::ROLE_PESERTA, Role::ROLE_SEKRETARIAT, Role::ROLE_TIM_ABSTRAK] as $role) {
            $this->as($role);
            $this->getJson('/api/registration-fees')->assertForbidden();
            $this->getJson("/api/registration-fees/{$fee->id_fee}")->assertForbidden();
            $this->postJson('/api/registration-fees', $this->payload(['amount' => 1]))->assertForbidden();
            $this->patchJson("/api/registration-fees/{$fee->id_fee}", ['amount' => 1])->assertForbidden();
            $this->deleteJson("/api/registration-fees/{$fee->id_fee}")->assertForbidden();
        }

        $this->assertDatabaseHas('registration_fee', ['id_fee' => $fee->id_fee, 'amount' => 1500000]);
    }

    public function test_view_only_level_can_read_but_not_write(): void
    {
        $fee = $this->makeFee();
        $user = $this->userWithRole(Role::ROLE_SEKRETARIAT);

        $matrix = json_decode(DB::table('role_permission_matrix')->value('matrix'), true);
        $matrix['manage_tariffs'][(string) $user->id_role] = 'view_only';
        DB::table('role_permission_matrix')->where('id', 1)->update(['matrix' => json_encode($matrix)]);

        $this->actingAs($user, 'sanctum');
        $this->getJson('/api/registration-fees')->assertOk();
        $this->getJson("/api/registration-fees/{$fee->id_fee}")->assertOk();
        $this->postJson('/api/registration-fees', $this->payload(['amount' => 1]))->assertForbidden();
        $this->patchJson("/api/registration-fees/{$fee->id_fee}", ['amount' => 1])->assertForbidden();
        $this->deleteJson("/api/registration-fees/{$fee->id_fee}")->assertForbidden();
    }

    // ----------------------------------------------------------------- relasi

    public function test_model_relations(): void
    {
        $fee = $this->makeFee();
        $invoice = $this->makeInvoiceFor($fee);

        $this->assertTrue($fee->event->is($this->event));
        $this->assertTrue($fee->registrationPeriod->is($this->period));
        $this->assertTrue($fee->invoices->first()->is($invoice));
        $this->assertTrue($this->event->registrationFees->first()->is($fee));
        $this->assertTrue($this->period->registrationFees->first()->is($fee));
        $this->assertTrue($invoice->registrationFee->is($fee));
    }

    // ------------------------------------------------ proteksi invoice lama

    public function test_fee_used_by_invoice_cannot_be_deleted(): void
    {
        $fee = $this->makeFee();
        $this->makeInvoiceFor($fee);

        $this->as(Role::ROLE_BENDAHARA)
            ->deleteJson("/api/registration-fees/{$fee->id_fee}")
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('registration_fee', ['id_fee' => $fee->id_fee]);
    }

    public function test_fee_used_by_invoice_cannot_have_amount_or_identity_changed(): void
    {
        $fee = $this->makeFee();
        $invoice = $this->makeInvoiceFor($fee);

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", ['amount' => 9000000, 'registration_type' => 'Student'])
            ->assertStatus(409)
            ->assertJsonPath('locked_fields', ['registration_type', 'amount']);

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", ['discount' => 0])
            ->assertStatus(409);

        $fee->refresh();
        $this->assertSame('1500000.00', (string) $fee->amount);
        $this->assertSame('Regular', $fee->registration_type);
        $this->assertSame('1400000.00', (string) $invoice->fresh()->total_tagihan);
    }

    public function test_resubmitting_identical_values_for_used_fee_is_allowed(): void
    {
        $fee = $this->makeFee();
        $this->makeInvoiceFor($fee);

        $this->as(Role::ROLE_BENDAHARA)
            ->patchJson("/api/registration-fees/{$fee->id_fee}", $this->payload())
            ->assertOk()
            ->assertJsonPath('data.is_used', true)
            ->assertJsonPath('data.invoices_count', 1);
    }

    public function test_new_fee_does_not_alter_existing_invoice(): void
    {
        $fee = $this->makeFee();
        $invoice = $this->makeInvoiceFor($fee);

        $this->as(Role::ROLE_BENDAHARA)
            ->postJson('/api/registration-fees', $this->payload(['id_period' => $this->makePeriod($this->event, 'Regular')->id_period, 'amount' => 2500000]))
            ->assertCreated();

        $invoice->refresh();
        $this->assertSame($fee->id_fee, $invoice->id_fee);
        $this->assertSame('1400000.00', (string) $invoice->total_tagihan);
    }

    // ------------------------------------------------------------ route publik

    public function test_public_endpoint_is_read_only_and_shows_only_active_event_and_period(): void
    {
        $visible = $this->makeFee();
        $inactivePeriod = $this->makePeriod($this->event, 'Lama', 'Tidak Aktif');
        $this->makeFee(['id_period' => $inactivePeriod->id_period]);
        $draftEvent = Event::create([
            'nama_event' => 'Draft Event', 'tanggal_mulai' => '2027-09-01', 'tanggal_selesai' => '2027-09-02', 'status_event' => 'Draft',
        ]);
        $this->makeFee(['id_event' => $draftEvent->id_event, 'id_period' => $this->makePeriod($draftEvent)->id_period]);

        $this->getJson('/api/public/registration-fees')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_fee', $visible->id_fee)
            ->assertJsonPath('data.0.final_amount', '1400000.00');

        $this->postJson('/api/public/registration-fees', $this->payload())->assertStatus(405);
        $this->deleteJson('/api/public/registration-fees')->assertStatus(405);
    }
}
