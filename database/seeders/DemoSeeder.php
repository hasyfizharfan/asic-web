<?php

namespace Database\Seeders;

use App\Models\AbstractSubmission;
use App\Models\AddOn;
use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\EventSession;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\UserAccount;
use App\Support\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->command?->getLaravel()->environment('local')) {
            return;
        }

        $password = config('asic.admin_default_password');
        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException('ADMIN_DEFAULT_PASSWORD wajib diatur untuk akun demo lokal.');
        }

        $event = Event::updateOrCreate(
            ['nama_event' => 'ASIC 2027'],
            [
                'tema' => 'Sustainable Agriculture and Food Systems',
                'deskripsi' => 'Contoh acara untuk pengembangan dan uji coba lokal.',
                'tanggal_mulai' => '2027-08-12',
                'tanggal_selesai' => '2027-08-13',
                'lokasi' => 'Universitas Andalas, Padang',
                'mode_kegiatan' => 'Hybrid',
                'status_event' => 'Aktif',
            ]
        );

        $period = RegistrationPeriod::updateOrCreate(
            ['id_event' => $event->id_event, 'nama_periode' => 'Early Bird'],
            ['tanggal_mulai' => '2026-11-01', 'tanggal_selesai' => '2027-01-31', 'status_periode' => 'Aktif']
        );

        // Nominal contoh (bukan tarif resmi panitia). IDR untuk peserta Indonesia, USD untuk peserta negara lain.
        $feeAmounts = [
            'IDR' => ['Student' => 250000, 'Regular' => 400000, 'Committee' => 0],
            'USD' => ['Student' => 20, 'Regular' => 30, 'Committee' => 0],
        ];
        foreach ($feeAmounts as $currency => $amounts) {
            foreach ($amounts as $type => $amount) {
                foreach (['Daring', 'Luring'] as $attendanceMode) {
                    RegistrationFee::updateOrCreate(
                        [
                            'id_event' => $event->id_event,
                            'id_period' => $period->id_period,
                            'registration_type' => $type,
                            'attendance_mode' => $attendanceMode,
                            'tujuan_publikasi' => 'Tidak',
                            'mata_uang' => $currency,
                        ],
                        ['amount' => $amount, 'discount' => 0]
                    );
                }
            }
        }

        AddOn::updateOrCreate(
            ['id_event' => $event->id_event, 'nama_add_on' => 'Conference Dinner'],
            ['deskripsi' => 'Contoh add-on jamuan malam konferensi.', 'harga' => 150000, 'status' => 'Aktif']
        );

        $participants = [
            ['email' => 'oral@asic.test', 'first_name' => 'Alya', 'last_name' => 'Pratama', 'participation_type' => 'Oral Presenter', 'registration_type' => 'Student', 'country' => 'Indonesia'],
            ['email' => 'poster@asic.test', 'first_name' => 'Bima', 'last_name' => 'Saputra', 'participation_type' => 'Poster Presenter', 'registration_type' => 'Regular', 'country' => 'Indonesia'],
            ['email' => 'listener@asic.test', 'first_name' => 'Morgan', 'last_name' => 'Lee', 'participation_type' => 'Listener', 'registration_type' => 'Regular', 'country' => 'Malaysia'],
        ];

        $participantRole = Role::where('nama_role', Role::ROLE_PESERTA)->firstOrFail();
        foreach ($participants as $index => $attributes) {
            $user = UserAccount::updateOrCreate(
                ['email' => $attributes['email']],
                ['id_role' => $participantRole->id_role, 'password' => Hash::make($password), 'status_akun' => 'Aktif']
            );
            $participant = Participant::updateOrCreate(
                ['id_user' => $user->id_user],
                [
                    'prefix' => null,
                    'first_name' => $attributes['first_name'],
                    'last_name' => $attributes['last_name'],
                    'passport_no' => 'DEMO-ASIC-'.($index + 1),
                    'job_title' => $index === 2 ? 'Researcher' : 'Student',
                    'phone' => '+62000000000'.($index + 1),
                    'institution' => $index === 2 ? 'Universiti Demo' : 'Universitas Andalas',
                    'address' => 'Alamat contoh untuk akun demo',
                    'city' => $index === 2 ? 'Kuala Lumpur' : 'Padang',
                    'country' => $attributes['country'],
                    'postcode' => '25000',
                ]
            );

            $registration = Registration::updateOrCreate(
                ['id_participant' => $participant->id_participant, 'id_event' => $event->id_event],
                [
                    'id_period' => $period->id_period,
                    'registration_type' => $attributes['registration_type'],
                    'attendance_mode' => 'Luring',
                    'participation_type' => $attributes['participation_type'],
                    'publication_option' => 'Tidak',
                    'registration_date' => now()->subDays(2 - $index),
                    'registration_status' => 'Terdaftar',
                ]
            );

            if ($attributes['participation_type'] !== 'Listener') {
                AbstractSubmission::updateOrCreate(
                    ['id_registration' => $registration->id_registration],
                    [
                        'judul_abstrak' => $index === 0
                            ? 'Climate-Smart Agriculture for Smallholder Farms'
                            : 'Community-Based Seed Systems in West Sumatra',
                        'scope' => 'CP',
                        'metode_presentasi' => $attributes['participation_type'] === 'Oral Presenter' ? 'Oral' : 'Poster',
                        'tanggal_submit' => now()->subDay(),
                        'status_abstrak' => 'Diajukan',
                    ]
                );
            }

            // KF-20: invoice presenter baru terbit setelah abstrak Diterima; demo hanya menagih Listener.
            if ($attributes['participation_type'] !== 'Listener') {
                continue;
            }

            $currency = Currency::forCountry($attributes['country']);
            $fee = RegistrationFee::where('id_event', $event->id_event)
                ->where('id_period', $period->id_period)
                ->where('registration_type', $attributes['registration_type'])
                ->where('attendance_mode', 'Luring')
                ->where('tujuan_publikasi', 'Tidak')
                ->where('mata_uang', $currency)
                ->firstOrFail();

            $invoice = Invoice::updateOrCreate(
                ['id_registration' => $registration->id_registration],
                [
                    'id_fee' => $fee->id_fee,
                    'nomor_invoice' => 'DEMO-ASIC-2027-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'tanggal_invoice' => now()->subDay(),
                    'tanggal_jatuh_tempo' => now()->addDays(6)->toDateString(),
                    'total_tagihan' => $fee->finalAmount(),
                    'mata_uang' => $currency,
                    'status_pembayaran' => 'Menunggu Pembayaran',
                ]
            );

            InvoiceDetail::updateOrCreate(
                ['id_invoice' => $invoice->id_invoice, 'jenis_biaya' => 'Registrasi'],
                ['deskripsi' => 'Biaya registrasi '.$attributes['registration_type'], 'jumlah' => 1, 'harga_satuan' => $fee->finalAmount(), 'subtotal' => $fee->finalAmount()]
            );
        }

        $this->seedStaff($password);

        Speaker::updateOrCreate(
            ['id_event' => $event->id_event, 'nama_speaker' => 'Dr. Nadia Rahman'],
            ['institusi' => 'Universitas Andalas', 'negara' => 'Indonesia', 'deskripsi' => 'Contoh pembicara untuk halaman publik.']
        );

        EventSchedule::updateOrCreate(
            ['id_event' => $event->id_event, 'nama_kegiatan' => 'Opening and Keynote Session'],
            ['tanggal' => '2027-08-12', 'waktu_mulai' => '09:00:00', 'waktu_selesai' => '10:30:00', 'lokasi' => 'Main Hall', 'deskripsi' => 'Pembukaan dan sesi utama.']
        );

        EventSession::updateOrCreate(
            ['id_event' => $event->id_event, 'nama_sesi' => 'Opening Session'],
            ['waktu' => '2027-08-12 09:00:00', 'waktu_selesai' => '2027-08-12 10:30:00', 'qr_token' => 'demo-asic-2027-opening']
        );

    }

    private function seedStaff(string $password): void
    {
        $staff = [
            ['email' => 'secretariat@asic.test', 'role' => Role::ROLE_SEKRETARIAT],
            ['email' => 'reviewer@asic.test', 'role' => Role::ROLE_TIM_ABSTRAK],
            ['email' => 'treasurer@asic.test', 'role' => Role::ROLE_BENDAHARA],
        ];

        foreach ($staff as $account) {
            $role = Role::where('nama_role', $account['role'])->firstOrFail();
            UserAccount::updateOrCreate(
                ['email' => $account['email']],
                ['id_role' => $role->id_role, 'password' => Hash::make($password), 'status_akun' => 'Aktif']
            );
        }
    }
}
