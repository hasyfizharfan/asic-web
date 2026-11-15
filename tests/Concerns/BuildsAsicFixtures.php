<?php

namespace Tests\Concerns;

use App\Models\AbstractSubmission;
use App\Models\AddOn;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use App\Models\UserAccount;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

/**
 * Fixture bersama untuk feature test. Project belum memasang Sanctum, sehingga guard `sanctum`
 * didefinisikan lokal pada test (tanpa mengubah konfigurasi autentikasi global).
 */
trait BuildsAsicFixtures
{
    protected Event $event;

    protected RegistrationPeriod $period;

    protected function bootAsic(): void
    {
        config([
            'auth.guards.sanctum' => ['driver' => 'session', 'provider' => 'user_account'],
            'auth.providers.user_account' => ['driver' => 'eloquent', 'model' => UserAccount::class],
        ]);
        Storage::fake('local');
        Storage::fake('public');
        $this->seed([RoleSeeder::class, RolePermissionSeeder::class]);

        $this->event = Event::create([
            'nama_event' => 'ASIC 2027', 'tema' => 'Sustainable Agriculture', 'tanggal_mulai' => '2027-08-01',
            'tanggal_selesai' => '2027-08-03', 'lokasi' => 'Padang', 'mode_kegiatan' => 'Hybrid', 'status_event' => 'Aktif',
        ]);
        $this->period = RegistrationPeriod::create([
            'id_event' => $this->event->id_event, 'nama_periode' => 'Early Bird',
            'tanggal_mulai' => now()->subMonth()->toDateString(), 'tanggal_selesai' => now()->addYear()->toDateString(), 'status_periode' => 'Aktif',
        ]);
    }

    protected function user(string $role, string $suffix = ''): UserAccount
    {
        return UserAccount::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '', $role)).$suffix.'@example.test'],
            ['id_role' => Role::where('nama_role', $role)->value('id_role'), 'password' => 'secret-pass-123', 'status_akun' => 'Aktif']
        );
    }

    protected function as(string $role, string $suffix = ''): static
    {
        return $this->actingAs($this->user($role, $suffix), 'sanctum');
    }

    protected function fee(array $o = []): RegistrationFee
    {
        return RegistrationFee::create($o + [
            'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period,
            'registration_type' => 'Regular', 'attendance_mode' => 'Luring', 'tujuan_publikasi' => 'Prosiding',
            'amount' => 1500000, 'discount' => 100000,
        ]);
    }

    /** Registrasi + peserta. $o menimpa kolom registrasi. */
    protected function registration(string $suffix = '1', array $o = [], array $participant = []): Registration
    {
        $user = $this->user(Role::ROLE_PESERTA, $suffix);
        $p = Participant::firstOrCreate(['id_user' => $user->id_user], $participant + [
            'first_name' => 'Budi', 'last_name' => 'Santoso '.$suffix, 'institution' => 'Universitas Andalas', 'country' => 'Indonesia',
        ]);

        return Registration::create($o + [
            'id_participant' => $p->id_participant, 'id_event' => $this->event->id_event, 'id_period' => $this->period->id_period,
            'registration_type' => 'Regular', 'attendance_mode' => 'Luring', 'participation_type' => 'Oral Presenter',
            'publication_option' => 'Prosiding', 'registration_status' => 'Terdaftar',
        ]);
    }

    protected function abstractFor(Registration $r, array $o = []): AbstractSubmission
    {
        return AbstractSubmission::create($o + [
            'id_registration' => $r->id_registration, 'judul_abstrak' => 'Smart Irrigation for Paddy', 'scope' => 'Agronomy',
            'metode_presentasi' => 'Oral', 'status_abstrak' => 'Diajukan',
        ]);
    }

    protected function addOn(float $price = 250000): AddOn
    {
        return AddOn::create(['id_event' => $this->event->id_event, 'nama_add_on' => 'Field Trip', 'harga' => $price, 'status' => 'Aktif']);
    }
}
