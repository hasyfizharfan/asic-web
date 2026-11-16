<?php

namespace Tests\Feature;

use App\Http\Controllers\Portal\TreasurerController;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

class TreasurerPortalControllerTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Mail::fake();
    }

    private function invoiceWithPendingProof(): array
    {
        $this->fee(['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0]);
        $registration = $this->registration('treasurer-test', [
            'registration_type' => 'Student', 'attendance_mode' => 'Daring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak',
        ]);
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();
        $this->as(Role::ROLE_PESERTA, 'treasurer-test')->postJson('/api/invoices/'.$invoice->id_invoice.'/payment-proofs', [
            'nominal_transfer' => 500000, 'file_bukti' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertCreated();

        return [$invoice->fresh(), PaymentProof::where('id_invoice', $invoice->id_invoice)->firstOrFail()];
    }

    public function test_all_treasurer_portal_routes_render_through_the_controller_for_treasurer(): void
    {
        $this->fee();
        $registration = $this->registration('route-smoke');
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();
        $pageRoutes = [
            'portal.treasurer.' => route('portal.treasurer.'),
            'portal.treasurer.dashboard' => route('portal.treasurer.dashboard'),
            'portal.treasurer.tariffs' => route('portal.treasurer.tariffs'),
            'portal.treasurer.payments.index' => route('portal.treasurer.payments.index'),
            'portal.treasurer.payments.show' => route('portal.treasurer.payments.show', $invoice->id_invoice),
            'portal.treasurer.receipts.preview' => route('portal.treasurer.receipts.preview'),
            'portal.treasurer.refunds' => route('portal.treasurer.refunds'),
        ];
        $routes = collect(app('router')->getRoutes())->keyBy(fn (RoutingRoute $route) => $route->getName());

        foreach ($pageRoutes as $name => $url) {
            $route = $routes->get($name);
            $this->assertNotNull($route, $name);
            $this->assertStringStartsWith(TreasurerController::class.'@', $route->getActionName(), $name);
            $this->actingAs($this->user(Role::ROLE_BENDAHARA), 'sanctum')->get($url)->assertOk();
        }
    }

    public function test_other_roles_are_denied_all_treasurer_portal_routes(): void
    {
        $this->fee();
        $registration = $this->registration('other-role-route');
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();
        $urls = [
            route('portal.treasurer.'), route('portal.treasurer.dashboard'), route('portal.treasurer.tariffs'), route('portal.treasurer.payments.index'),
            route('portal.treasurer.payments.show', $invoice->id_invoice), route('portal.treasurer.receipts.preview'), route('portal.treasurer.refunds'),
        ];
        foreach ([Role::ROLE_PESERTA, Role::ROLE_SEKRETARIAT, Role::ROLE_TIM_ABSTRAK, Role::ROLE_ADMIN] as $role) {
            foreach ($urls as $url) {
                $this->actingAs($this->user($role, '.treasurer-denied'), 'sanctum')->get($url)->assertForbidden();
            }
        }
    }

    public function test_treasurer_verification_updates_invoice_but_participant_cannot_verify(): void
    {
        [$invoice, $proof] = $this->invoiceWithPendingProof();
        $url = '/api/payment-proofs/'.$proof->id_payment_proof.'/verify';

        $this->as(Role::ROLE_BENDAHARA)->get(route('portal.treasurer.dashboard'))->assertOk()
            ->assertSee('Bukti menunggu verifikasi')->assertSee('Pembayaran terverifikasi per kategori');

        $this->as(Role::ROLE_PESERTA, 'treasurer-test')->postJson($url, ['hasil_verifikasi' => 'Valid'])->assertForbidden();
        $this->assertSame('Menunggu Verifikasi', $invoice->fresh()->status_pembayaran);

        $this->as(Role::ROLE_BENDAHARA)->postJson($url, ['hasil_verifikasi' => 'Valid'])
            ->assertOk()->assertJsonPath('data.status_pembayaran', 'Terverifikasi');
        $this->assertSame('Terverifikasi', $invoice->fresh()->status_pembayaran);
        $this->get(route('portal.treasurer.dashboard'))->assertOk()->assertSee('500.000')->assertSee('Student');
    }
}
