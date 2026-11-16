# Menjalankan ASIC Web secara lokal

## Prasyarat

- PHP 8.2 atau lebih baru dengan ekstensi SQLite, PDO, OpenSSL, Mbstring, XML, Ctype, Fileinfo, Tokenizer, dan cURL.
- Composer 2.
- Node.js 20+ dan npm.

## Clone dan jalankan

```powershell
git clone https://github.com/hasyfizharfan/asic-web.git
cd asic-web
git switch dev
composer run setup
```

`composer run setup` memasang dependensi, membuat `.env` dari `.env.example` bila belum ada, membuat `database/database.sqlite` bila belum ada, membuat application key, menjalankan migration dan seeder, lalu membangun aset frontend. Seeder demo hanya berjalan pada `APP_ENV=local`.

Jalankan aplikasi dalam dua terminal:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

```powershell
npm run dev
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000). Jika tidak memerlukan hot reload, jalankan `npm run build` dan cukup gunakan server Laravel.

## Akun demo lokal

Semua akun demo menggunakan password dari `ADMIN_DEFAULT_PASSWORD` di `.env`. Nilai awal pada `.env.example` adalah `ASICAdminDev-GantiDiProduksi-2027!`; ganti nilai ini untuk lingkungan apa pun selain mesin pengembangan lokal.

| Peran / contoh | Email |
| --- | --- |
| Admin | `admin@asic.unand.ac.id` |
| Peserta presenter oral | `oral@asic.test` |
| Peserta presenter poster | `poster@asic.test` |
| Peserta listener | `listener@asic.test` |
| Sekretariat | `secretariat@asic.test` |
| Tim Abstrak | `reviewer@asic.test` |
| Bendahara | `treasurer@asic.test` |

API login menggunakan sesi cookie Sanctum. Ambil cookie CSRF dari `/sanctum/csrf-cookie`, lalu kirim kredensial ke `POST /api/login` dari origin `http://127.0.0.1:8000`. Tidak ada token yang disimpan di localStorage.

## Queue

Queue lokal memakai database. Jalankan worker bila sedang menguji pekerjaan antrean:

```powershell
php artisan queue:work --tries=1
```

Email diarahkan ke log. Periksa `storage/logs/laravel.log` untuk melihat email reset kata sandi atau notifikasi yang dicatat aplikasi.

## Troubleshooting

- **419 / CSRF**: akses `/sanctum/csrf-cookie` lebih dulu dan pertahankan cookie jar yang sama untuk request login berikutnya. Gunakan host yang sama persis, `127.0.0.1` (jangan campur dengan `localhost`).
- **401 setelah login**: pertahankan cookie sesi pada request berikutnya; login Sanctum berbasis sesi, bukan bearer token. Pastikan `SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000` dan `SESSION_DRIVER=file`.
- **Cookie tidak tersimpan**: akses Laravel dan frontend melalui host `127.0.0.1`; `SESSION_DOMAIN` sengaja kosong agar cookie host-only. Jangan membuka satu melalui `localhost` dan satu lagi lewat alamat IP.
- **Database SQLite tidak ditemukan**: pastikan `database/database.sqlite` ada dan ekstensi PHP `pdo_sqlite` aktif. `composer run setup` membuat file database ini otomatis.
- **Login demo gagal**: pastikan migration dan seeder selesai, `APP_ENV=local`, dan gunakan password yang saat ini tertulis di `.env`, bukan nilai contoh yang mungkin sudah diganti.

## Catatan tarif demo

Tarif memiliki mata uang (`IDR` atau `USD`). Peserta dengan negara Indonesia ditagih dengan tarif IDR, peserta negara lain dengan tarif USD; bila tarif USD untuk kombinasi tersebut belum dibuat Bendahara, invoice tidak terbit (tercatat di log sebagai `invoice.skipped`). Demo hanya memuat tarif IDR.

## Perintah pemeliharaan

- `php artisan migrate` setelah menarik perubahan terbaru (kolom registrasi ulang dan mata uang).
- `php artisan asic:report-early-presenter-invoices` — laporan read-only invoice presenter yang terbit sebelum abstraknya Diterima (sisa aturan lama). Tidak mengubah data.
- `php artisan registration-periods:close-expired` — menonaktifkan periode yang tanggal selesainya lewat (juga dijadwalkan per jam; jalankan `php artisan schedule:work` atau cron `schedule:run`).
- Zona waktu aplikasi: `APP_TIMEZONE=Asia/Jakarta`.
