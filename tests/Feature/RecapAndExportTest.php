<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PaymentProof;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsAsicFixtures;
use Tests\TestCase;
use ZipArchive;

/** Task 9 (rekapitulasi) + Task 10 (ekspor Excel). Angka diverifikasi terhadap data yang dibuat di test. */
class RecapAndExportTest extends TestCase
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
            'tanggal_mulai' => now()->subMonth()->toDateString(), 'tanggal_selesai' => now()->addYear()->toDateString(), 'status_periode' => 'Aktif',
        ]);
        $this->seedData();
    }

    /**
     * Data (Early Bird): P1 Oral Regular/Luring Diterima (invoice Terverifikasi 1.400.000), P2 Poster Student/Daring Ditolak,
     *   P3 Oral Regular Diajukan, P4 Listener Student/Daring (invoice Menunggu Verifikasi 500.000, Malaysia).
     * Data (Regular): P5 Listener Regular/Luring (invoice 1.000.000, Menunggu Pembayaran). P6 Dibatalkan.
     */
    private function seedData(): void
    {
        $this->fee(['amount' => 1500000, 'discount' => 100000]);
        $this->fee(['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'tujuan_publikasi' => 'Tidak', 'amount' => 500000, 'discount' => 0]);
        $this->fee(['id_period' => $this->regular->id_period, 'tujuan_publikasi' => 'Tidak', 'amount' => 1000000, 'discount' => 0]);

        $r1 = $this->registration('1');
        $a1 = $this->abstractFor($r1);
        $r2 = $this->registration('2', ['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'participation_type' => 'Poster Presenter', 'publication_option' => 'Tidak']);
        $a2 = $this->abstractFor($r2, ['metode_presentasi' => 'Poster', 'scope' => 'Soil Management']);
        $this->abstractFor($this->registration('3'), ['scope' => 'Crop Production']);
        $this->registration('4', ['registration_type' => 'Student', 'attendance_mode' => 'Daring', 'participation_type' => 'Listener', 'publication_option' => 'Tidak'], ['country' => 'Malaysia']);
        $this->registration('5', ['id_period' => $this->regular->id_period, 'participation_type' => 'Listener', 'publication_option' => 'Tidak']);
        $this->registration('6', ['registration_status' => 'Dibatalkan', 'participation_type' => 'Listener']);

        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$a1->id_abstract}/review", ['hasil_review' => 'Diterima'])->assertOk();
        $this->as(Role::ROLE_TIM_ABSTRAK)->postJson("/api/tim-abstrak/submissions/{$a2->id_abstract}/review", ['hasil_review' => 'Ditolak', 'catatan_review' => 'Tidak sesuai dengan ruang lingkup konferensi.'])->assertOk();

        $inv1 = Invoice::where('id_registration', $r1->id_registration)->firstOrFail();
        $this->as(Role::ROLE_PESERTA, '1')->postJson("/api/invoices/{$inv1->id_invoice}/payment-proofs", ['file_bukti' => UploadedFile::fake()->image('a.jpg'), 'nominal_transfer' => 1400000])->assertCreated();
        $this->as(Role::ROLE_BENDAHARA)->postJson('/api/payment-proofs/'.PaymentProof::firstOrFail()->id_payment_proof.'/verify', ['hasil_verifikasi' => 'Valid'])->assertOk();

        $inv4 = Invoice::whereHas('registration', fn ($q) => $q->where('participation_type', 'Listener')->where('id_period', $this->period->id_period))->firstOrFail();
        $this->as(Role::ROLE_PESERTA, '4')->postJson("/api/invoices/{$inv4->id_invoice}/payment-proofs", ['file_bukti' => UploadedFile::fake()->image('b.jpg')])->assertCreated();
    }

    private function counts(array $rows): array
    {
        return collect($rows)->pluck('total', 'key')->all();
    }

    // ------------------------------------------------------------------ rekap

    public function test_registration_recap_matches_database(): void
    {
        $d = $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/recap/registrations')->assertOk()->json('data');

        $this->assertSame(6, $d['total']);
        $this->assertSame(['Dibatalkan' => 1, 'Terdaftar' => 5], $this->counts($d['by_status']));
        $this->assertSame(['Listener' => 3, 'Oral Presenter' => 2, 'Poster Presenter' => 1], $this->counts($d['by_participation_type']));
        $this->assertSame(['Early Bird' => 5, 'Regular' => 1], $this->counts($d['by_period']));

        $filtered = $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/recap/registrations?id_period='.$this->regular->id_period)->json('data');
        $this->assertSame(1, $filtered['total']);
        $terdaftar = $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/recap/registrations?status=Terdaftar')->json('data');
        $this->assertSame(5, $terdaftar['total']);
        $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/recap/registrations?id_event=9999')->assertUnprocessable();
    }

    public function test_participant_recap_by_category_period_and_country(): void
    {
        $d = $this->as(Role::ROLE_SEKRETARIAT)->getJson('/api/recap/participants?status=Terdaftar')->assertOk()->json('data');

        $this->assertSame(5, $d['total_participants']);
        $this->assertSame(['Indonesia' => 4, 'Malaysia' => 1], $this->counts($d['by_country']));
        $matrix = collect($d['by_category_and_period'])->mapWithKeys(fn ($r) => [$r['period'].'|'.$r['registration_type'] => $r['total']])->all();
        $this->assertSame(['Early Bird|Regular' => 2, 'Early Bird|Student' => 2, 'Regular|Regular' => 1], $matrix);
    }

    public function test_abstract_recap_and_selection_result(): void
    {
        $d = $this->as(Role::ROLE_TIM_ABSTRAK)->getJson('/api/recap/abstracts')->assertOk()->json('data');

        $this->assertSame(3, $d['total']);
        $this->assertSame(['Diajukan' => 1, 'Diterima' => 1, 'Ditolak' => 1], $this->counts($d['by_status']));
        $this->assertSame(['Oral' => 2, 'Poster' => 1], $this->counts($d['by_presentation_method']));
        $this->assertSame(['Crop Production' => 2, 'Soil Management' => 1], $this->counts($d['by_scope']));
        $this->assertEquals(['diterima' => 1, 'ditolak' => 1, 'menunggu_seleksi' => 1, 'acceptance_rate_percent' => 50], $d['selection']);
        $this->assertSame(2, $d['reviews_total']);
    }

    public function test_invoice_and_payment_recap_amounts(): void
    {
        $inv = $this->as(Role::ROLE_BENDAHARA)->getJson('/api/recap/invoices')->assertOk()->json('data');
        $this->assertSame(5, $inv['total']);
        $this->assertSame('4800000.00', $inv['total_tagihan']); // invoice presenter diterbitkan sejak registrasi
        $byStatus = collect($inv['by_status'])->pluck('total_tagihan', 'key')->all();
        $this->assertSame(['Menunggu Pembayaran' => '2900000.00', 'Menunggu Verifikasi' => '500000.00', 'Terverifikasi' => '1400000.00'], $byStatus);

        $pay = $this->as(Role::ROLE_BENDAHARA)->getJson('/api/recap/payments')->assertOk()->json('data');
        $this->assertSame(2, $pay['proofs_total']);
        $this->assertSame(1, $pay['verified_valid']);
        $this->assertSame(0, $pay['verified_invalid']);
        $this->assertSame(1, $pay['awaiting_verification']);
        $this->assertSame('1400000.00', $pay['amount_verified']);
        $this->assertSame('3400000.00', $pay['amount_outstanding']);

        $period = $this->as(Role::ROLE_BENDAHARA)->getJson('/api/recap/payments?id_period='.$this->regular->id_period)->json('data');
        $this->assertSame(0, $period['proofs_total']);
        $this->assertSame('1000000.00', $period['amount_outstanding']);
    }

    public function test_recap_access_is_role_restricted(): void
    {
        // Matriks: registrasi/peserta -> Sekretariat & Admin; abstrak -> + Tim Abstrak; pembayaran/invoice -> + Bendahara.
        $cases = [
            '/api/recap/registrations' => [Role::ROLE_SEKRETARIAT, Role::ROLE_ADMIN],
            '/api/recap/participants' => [Role::ROLE_SEKRETARIAT, Role::ROLE_ADMIN],
            '/api/recap/abstracts' => [Role::ROLE_SEKRETARIAT, Role::ROLE_TIM_ABSTRAK, Role::ROLE_ADMIN],
            '/api/recap/invoices' => [Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA, Role::ROLE_ADMIN],
            '/api/recap/payments' => [Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA, Role::ROLE_ADMIN],
        ];
        $all = [Role::ROLE_PESERTA, Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA, Role::ROLE_TIM_ABSTRAK, Role::ROLE_ADMIN];
        foreach ($cases as $url => $allowed) {
            foreach ($all as $role) {
                $this->as($role)->getJson($url)->assertStatus(in_array($role, $allowed, true) ? 200 : 403);
            }
        }
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/recap/registrations')->assertUnauthorized();
    }

    // ----------------------------------------------------------------- ekspor

    /** @return array<int, array<int, string>> */
    private function sheetRows(string $binary, int $sheet = 1): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'x');
        file_put_contents($tmp, $binary);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp));
        $xml = simplexml_load_string($zip->getFromName("xl/worksheets/sheet{$sheet}.xml"));
        $shared = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();
        unlink($tmp);

        $strings = $shared ? array_map(fn ($si) => (string) $si->t, iterator_to_array(simplexml_load_string($shared)->si, false)) : [];
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $t = (string) $c['t'];
                $cells[] = $t === 's' ? $strings[(int) $c->v] : ($t === 'inlineStr' ? (string) $c->is->t : (string) $c->v);
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    public function test_participant_export_is_valid_xlsx_with_all_registrations_and_filters(): void
    {
        $res = $this->as(Role::ROLE_SEKRETARIAT)->get('/api/exports/participants')->assertOk();
        $res->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $res->headers->get('content-disposition'));
        $this->assertStringStartsWith('PK', $res->getContent());

        $rows = $this->sheetRows($res->getContent());
        $this->assertSame('Nama', $rows[0][1]);
        $this->assertCount(7, $rows); // header + 6 registrasi
        $this->assertContains('peserta4@example.test', array_column($rows, 2));

        $one = $this->sheetRows($this->as(Role::ROLE_SEKRETARIAT)->get('/api/exports/participants?id_period='.$this->regular->id_period.'&status=Terdaftar')->getContent());
        $this->assertCount(2, $one);
    }

    public function test_abstract_export_contains_selection_result_and_reviewer(): void
    {
        $rows = $this->sheetRows($this->as(Role::ROLE_TIM_ABSTRAK)->get('/api/exports/abstracts')->assertOk()->getContent());
        $this->assertCount(4, $rows);
        $statuses = array_column($rows, 4);
        $this->assertEqualsCanonicalizing(['Status Seleksi', 'Diterima', 'Ditolak', 'Diajukan'], $statuses);
        $this->assertContains('timabstrak@example.test', array_column($rows, 12));

        $accepted = $this->sheetRows($this->as(Role::ROLE_TIM_ABSTRAK)->get('/api/exports/abstracts?status=Diterima')->getContent());
        $this->assertCount(2, $accepted);
    }

    public function test_payment_and_invoice_exports(): void
    {
        $pay = $this->sheetRows($this->as(Role::ROLE_BENDAHARA)->get('/api/exports/payments')->assertOk()->getContent());
        $this->assertCount(6, $pay);
        $verified = collect($pay)->firstWhere(3, 'Terverifikasi');
        $this->assertSame('1400000', $verified[4]);
        $this->assertSame('Valid', $verified[7]);

        $inv = $this->sheetRows($this->as(Role::ROLE_BENDAHARA)->get('/api/exports/invoices?status=Menunggu Pembayaran')->assertOk()->getContent());
        $this->assertCount(4, $inv);
        $this->assertContains('1000000', array_column($inv, 8));
    }

    public function test_recap_export_has_multiple_sheets_and_requires_all_three_permissions(): void
    {
        $res = $this->as(Role::ROLE_ADMIN)->get('/api/exports/recap')->assertOk();
        $tmp = tempnam(sys_get_temp_dir(), 'x');
        file_put_contents($tmp, $res->getContent());
        $zip = new ZipArchive;
        $zip->open($tmp);
        $wb = $zip->getFromName('xl/workbook.xml');
        $zip->close();
        unlink($tmp);
        foreach (['Registrasi', 'Peserta', 'Abstrak', 'Invoice', 'Pembayaran'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $wb);
        }

        $this->as(Role::ROLE_SEKRETARIAT)->get('/api/exports/recap')->assertOk();
        $this->as(Role::ROLE_BENDAHARA)->get('/api/exports/recap')->assertForbidden();
        $this->as(Role::ROLE_TIM_ABSTRAK)->get('/api/exports/recap')->assertForbidden();
    }

    public function test_export_access_is_role_restricted(): void
    {
        $this->as(Role::ROLE_PESERTA)->get('/api/exports/participants')->assertForbidden();
        $this->as(Role::ROLE_PESERTA)->get('/api/exports/abstracts')->assertForbidden();
        $this->as(Role::ROLE_PESERTA)->get('/api/exports/payments')->assertForbidden();
        $this->as(Role::ROLE_BENDAHARA)->get('/api/exports/participants')->assertForbidden();
        $this->as(Role::ROLE_TIM_ABSTRAK)->get('/api/exports/payments')->assertForbidden();
        $this->as(Role::ROLE_TIM_ABSTRAK)->get('/api/exports/invoices')->assertForbidden();
        $this->as(Role::ROLE_BENDAHARA)->get('/api/exports/abstracts')->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/exports/participants')->assertUnauthorized();
    }

    public function test_export_neutralises_spreadsheet_formula_injection(): void
    {
        Participant::query()->update(['institution' => '=HYPERLINK("http://evil","x")']);
        $content = $this->as(Role::ROLE_SEKRETARIAT)->get('/api/exports/participants')->getContent();
        $rows = $this->sheetRows($content);
        $this->assertStringStartsWith("'=", $rows[1][3]);
    }
}
