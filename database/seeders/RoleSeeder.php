<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nama_role' => Role::ROLE_PESERTA,
                'deskripsi' => 'Peserta seminar internasional ASIC (Presenter Oral/Poster, Listener) yang dapat mendaftar, mengirim abstrak, mengunggah bukti bayar, dan mengunduh berkas kegiatan.',
            ],
            [
                'nama_role' => Role::ROLE_SEKRETARIAT,
                'deskripsi' => 'Pengelola administrasi seminar, validasi berkas, registrasi ulang peserta luring melalui scan QR code kehadiran, rekapitulasi data, dan penerbitan pengumuman.',
            ],
            [
                'nama_role' => Role::ROLE_BENDAHARA,
                'deskripsi' => 'Pengelola keuangan seminar, tarif pendaftaran, verifikasi bukti pembayaran manual, dan penerbitan kuitansi (receipt).',
            ],
            [
                'nama_role' => Role::ROLE_TIM_ABSTRAK,
                'deskripsi' => 'Tim penilai ilmiah yang memeriksa naskah abstrak peserta, memberikan catatan review, serta memutuskan status Diterima atau Ditolak.',
            ],
            [
                'nama_role' => Role::ROLE_ADMIN,
                'deskripsi' => 'Ketua/Koordinator yang memiliki hak akses penuh untuk mengelola akun pengguna, hak akses peran, konfigurasi teknis sistem, dan memantau seluruh modul seminar.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['nama_role' => $role['nama_role']],
                ['deskripsi' => $role['deskripsi']]
            );
        }
    }
}

