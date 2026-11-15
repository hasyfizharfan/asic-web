# Catatan keputusan dan baseline integrasi

## Baseline sebelum perubahan Tahap A

Dijalankan pada branch `feat/fe-be-integration`, dari HEAD awal `4d96ac4` (`dev`), 15 November 2026.

| Perintah | Hasil |
| --- | --- |
| `composer install` | Lulus; lock file cocok dan dependensi tersedia. |
| Persiapan `.env` + `php artisan key:generate` | Lulus. `.env` kerja awal menunjuk ke MySQL (`DB_DATABASE=db_asicweb`), sehingga tidak dipakai untuk baseline `migrate:fresh` agar data MySQL lokal di luar sandbox tidak terhapus. Baseline menggunakan `.env.example` dan SQLite. |
| `php artisan migrate:fresh --seed` | Migration lulus; seeding gagal karena `.env.example` memiliki `ADMIN_DEFAULT_PASSWORD` kosong dan `AdminUserSeeder` melempar `RuntimeException`. |
| `php artisan test` | Lulus: 110 tests, 711 assertions. |
| `php artisan route:list` | Lulus. |
| `npm install` | Lulus; semua paket sudah tersedia. |
| `npm run build` | Lulus; Vite 7.3.8 membangun aset. |

## Keputusan Tahap A

- URL lokal kanonis adalah `http://127.0.0.1:8000`. Domain cookie sesi kosong (host-only) dan Sanctum stateful hanya mencantumkan `127.0.0.1:8000` agar penggunaan `localhost` dan `127.0.0.1` tidak tercampur.
- `.env.example` menyediakan kredensial demo yang jelas hanya untuk lokal; `ADMIN_DEFAULT_PASSWORD` harus diganti di produksi.
- `composer run setup` membuat berkas SQLite jika belum ada dan menjalankan migration beserta seeder.
- `DemoSeeder` hanya dijalankan oleh `DatabaseSeeder` ketika `APP_ENV=local`; ia menyediakan event ASIC 2027, periode, tarif yang didukung skema saat ini, add-on, akun demo, registrasi/abstrak/invoice, sesi, speaker, dan jadwal.
- Skema tarif saat ini belum merepresentasikan negara/domestik-internasional atau mata uang. Seeder tidak menambah kolom atau mengarang mata uang; nominal demo menggunakan IDR. Perlu keputusan perubahan skema/API di tahap berikutnya untuk tarif USD atau tarif berdasarkan negara.
- Petunjuk menjalankan aplikasi dan akun demo ada di `docs/LOCAL-SETUP.md`.

## Verifikasi Tahap A

- `composer run setup` pada SQLite kosong: lulus; migration, `AdminUserSeeder`, `DemoSeeder`, `npm install`, dan `npm run build` selesai.
- Server `php artisan serve --host=127.0.0.1 --port=8000` berhasil dimulai. Login admin melalui `POST /api/login` berhasil dengan sesi cookie, dan request lanjutan `GET /api/me` pada sesi yang sama berhasil.
- `php artisan test`: lulus, 110 tests dan 711 assertions.
- `./vendor/bin/pint`: lulus dan menerapkan format Pint pada berkas PHP yang ada.
- `npm run build`: lulus.
- `php artisan route:list`: lulus.
