<?php

namespace Tests\Feature;

use App\Models\AbstractSubmission;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;

/** Lonceng panitia: bukti bayar → Bendahara, abstrak → Tim Abstrak, permintaan kerja sama → Sekretariat. */
class StaffNotificationsTest extends TestCase
{
    use BuildsAsicFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootAsic();
        Storage::fake('local');
        Mail::fake();
    }

    /** @return array<int, string> */
    private function kinds(UserAccount $user): array
    {
        return $user->fresh()->notifications->pluck('data.kind')->all();
    }

    public function test_payment_proof_notifies_every_active_treasurer_only(): void
    {
        $treasurer = $this->user(Role::ROLE_BENDAHARA);
        $treasurer2 = $this->user(Role::ROLE_BENDAHARA, '2');
        $inactive = $this->user(Role::ROLE_BENDAHARA, '3');
        $inactive->update(['status_akun' => 'Nonaktif']);
        $secretariat = $this->user(Role::ROLE_SEKRETARIAT);

        $this->fee(['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0]);
        $registration = $this->registration('pay', ['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak']);
        $invoice = Invoice::where('id_registration', $registration->id_registration)->firstOrFail();
        $this->as(Role::ROLE_PESERTA, 'pay')->postJson('/api/invoices/'.$invoice->id_invoice.'/payment-proofs', [
            'file_bukti' => UploadedFile::fake()->image('proof.jpg'), 'metode_pembayaran' => 'bank',
        ])->assertCreated();

        $this->assertSame(['payment_proof_submitted'], $this->kinds($treasurer));
        $this->assertSame(['payment_proof_submitted'], $this->kinds($treasurer2));
        $this->assertSame([], $this->kinds($inactive));
        $this->assertSame([], $this->kinds($secretariat));
        $this->assertNotContains('payment_proof_submitted', $this->kinds($this->user(Role::ROLE_PESERTA, 'pay')));
        $this->assertSame(route('portal.treasurer.payments.show', $invoice->id_invoice, false), $treasurer->fresh()->notifications->first()->data['url']);

        $this->actingAs($treasurer, 'sanctum')->getJson('/api/me/notifications')->assertOk()
            ->assertJsonPath('data.0.title', 'Bukti bayar baru masuk')
            ->assertJsonPath('data.0.body', 'Budi Santoso pay mengunggah bukti bayar untuk invoice '.$invoice->nomor_invoice.'. Silakan diverifikasi.');
    }

    public function test_abstract_team_is_told_about_new_and_replaced_abstracts_without_participant_name(): void
    {
        $reviewer = $this->user(Role::ROLE_TIM_ABSTRAK);
        $registration = $this->registration('abs');
        $submit = fn (string $title) => $this->as(Role::ROLE_PESERTA, 'abs')->postJson("/api/registrations/{$registration->id_registration}/abstract", [
            'judul_abstrak' => $title, 'scope' => 'CP', 'metode_presentasi' => 'Oral',
            'file_abstrak' => UploadedFile::fake()->create('abstrak.pdf', 50, 'application/pdf'),
        ])->assertCreated();

        $submit('Smart Irrigation for Paddy');
        $submit('Smart Irrigation for Paddy (versi 2)');

        $this->assertEqualsCanonicalizing(['abstract_submitted', 'abstract_resubmitted'], $this->kinds($reviewer));
        $idAbstract = AbstractSubmission::where('id_registration', $registration->id_registration)->value('id_abstract');
        $this->assertSame(route('portal.reviewer.abstracts.show', $idAbstract, false), $reviewer->fresh()->notifications->first()->data['url']);

        $bodies = collect($this->actingAs($reviewer, 'sanctum')->getJson('/api/me/notifications')->assertOk()->json('data'))->pluck('body')->implode(' ');
        $this->assertStringContainsString('Smart Irrigation for Paddy', $bodies);
        $this->assertStringNotContainsString('Budi', $bodies);
    }

    public function test_public_partnership_inquiry_notifies_secretariat_with_link_to_inquiry_tab(): void
    {
        $secretariat = $this->user(Role::ROLE_SEKRETARIAT);
        $this->postJson('/api/public/sponsorship-inquiries', [
            'id_event' => $this->event->id_event, 'jenis' => 'Sponsorship', 'nama_organisasi' => 'PT Contoh', 'nama_kontak' => 'Rina',
            'email' => 'rina@contoh.test', 'pesan' => 'Kami tertarik menjadi sponsor.',
        ])->assertCreated();

        $this->assertSame(['sponsorship_inquiry'], $this->kinds($secretariat));
        $this->assertSame(route('portal.secretariat.content', [], false).'?tab=sponsorship-inquiries', $secretariat->fresh()->notifications->first()->data['url']);
    }

    public function test_staff_see_all_pages_render_for_their_role_only(): void
    {
        foreach ([Role::ROLE_SEKRETARIAT => 'secretariat', Role::ROLE_BENDAHARA => 'treasurer', Role::ROLE_TIM_ABSTRAK => 'reviewer', Role::ROLE_ADMIN => 'admin'] as $role => $portal) {
            $this->as($role)->get(route("portal.$portal.notifications"))->assertOk()
                ->assertSee(__('asic-notifications.page.subtitle_staff'))->assertDontSee('<h2 class="font-bold">'.__('asic-notifications.page.announcements').'</h2>', false);
        }
        $this->as(Role::ROLE_PESERTA, 'x')->get(route('portal.treasurer.notifications'))->assertForbidden();
        $this->as(Role::ROLE_PESERTA, 'x')->get(route('portal.participant.notifications'))->assertOk()->assertSee('<h2 class="font-bold">'.__('asic-notifications.page.announcements').'</h2>', false);
    }
}
