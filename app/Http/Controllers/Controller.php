<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0',
    title: 'API Dokumentasi ASIC',
    description: 'Dokumentasi lengkap RESTful API untuk Sistem Informasi Annual Scientific International Conference (ASIC) 2026, Fakultas Pertanian, Universitas Andalas.',
    contact: new OA\Contact(
        name: 'Sekretariat ASIC 2026',
        email: 'asic@agr.unand.ac.id'
    ),
    license: new OA\License(
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
    )
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctumAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Gunakan token otentikasi Bearer: Bearer {token}'
)]
#[OA\Tag(name: 'Autentikasi', description: 'Endpoint Autentikasi, Login, Register, SSO, dan Profil Pengguna')]
#[OA\Tag(name: 'Reset Kata Sandi', description: 'Endpoint Permintaan Link dan Eksekusi Reset Kata Sandi')]
#[OA\Tag(name: 'Pendaftaran', description: 'Endpoint Pengelolaan Pendaftaran Kegiatan dan Layanan Tambahan (Add-On)')]
#[OA\Tag(name: 'Abstrak', description: 'Endpoint Pengunggahan dan Pengunduhan Naskah Abstrak Peserta')]
#[OA\Tag(name: 'Review Abstrak', description: 'Endpoint Penilaian dan Seleksi Abstrak oleh Tim Abstrak')]
#[OA\Tag(name: 'Berkas Peserta', description: 'Endpoint Berkas Presentasi (PPT) dan Artikel Lengkap')]
#[OA\Tag(name: 'Sesi Acara', description: 'Endpoint Manajemen Sesi Kegiatan dan QR Code Sesi')]
#[OA\Tag(name: 'Kehadiran', description: 'Endpoint Pencatatan Presensi Sesi dan Status Presentasi')]
#[OA\Tag(name: 'Invoice', description: 'Endpoint Detail Invoice, Perpanjangan Jatuh Tempo, dan Kedaluwarsa')]
#[OA\Tag(name: 'Tanda Tangan Digital', description: 'Endpoint Pengelolaan Tanda Tangan dan Stempel Berbasis Peran')]
#[OA\Tag(name: 'Admin - Manajemen Pengguna', description: 'Endpoint Pengelolaan Pengguna, Batch Actions, dan Export Data')]
#[OA\Tag(name: 'Admin - Hak Akses', description: 'Endpoint Pengaturan Matriks Peran dan Izin Akses (RBAC)')]
abstract class Controller
{
    //
}
