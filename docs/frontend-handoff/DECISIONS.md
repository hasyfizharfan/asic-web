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

## Tahap B — auth dan portal

- Pilihan auth web: semua halaman portal memakai `auth:sanctum` pada origin yang sama. Middleware `permission:` berikutnya memeriksa capability portal di `PermissionMatrix`; tidak ada package role tambahan.
- Capability `access_participant_portal`, `access_secretariat_portal`, `access_treasurer_portal`, `access_reviewer_portal`, dan `access_admin_portal` diberikan hanya ke role terkait. `RolePermissionSeeder` menambahkan capability yang belum tersimpan tanpa menimpa izin lain yang telah dikustomisasi.
- Form kelima role memakai `POST /api/login`. `AuthController::resolveRedirectUrl()` mengarahkan Peserta, Sekretariat, Bendahara, Tim Abstrak, dan Admin ke `/participant/dashboard`, `/secretariat/dashboard`, `/treasurer/dashboard`, `/reviewer/dashboard`, dan `/admin/dashboard`.
- Kelima dashboard Blade merangkum registrasi, abstrak, tagihan, dan event lewat query model. Landing publik membaca event aktif dan tarif dari database. `GET /password/reset/{token}` menampilkan form yang mengirim `POST /api/password/reset`; tautan email reset kini memakai path token tersebut.
- Logout form memanggil `POST /api/logout`. `asicApi` menggunakan cookie same-origin, mem-prime/refresh CSRF, retry satu kali pada 419, menangani 401/403/422, dan menyediakan `upload`/`postForm` untuk `FormData`.
- Route portal simulasi dan flag `logged_in_*` telah dihapus dari `routes/fe-preview.php`. Route `/_fe/*` lokal yang tersisa adalah fixture visual tanpa autentikasi dan bukan alur login.
- Feature coverage: redirect login setiap role, guard lintas role, guest redirect, logout, reset form, landing, dan form login. `php artisan test`: 115 tests, 782 assertions; Pint, build, serta route list lulus.

## Tahap C — keputusan kontrak

- Registrasi peserta memakai `POST /api/registrations`; pilihan event dan periode diambil dari endpoint publik, dan FE hanya melanjutkan ke checkout setelah respons 201 memuat `id_registration`.
- Agar urutan alur yang disepakati dapat dilakukan (registrasi → invoice → bukti bayar → verifikasi → abstrak), invoice kini diterbitkan untuk semua registrasi yang memiliki tarif. LoA Listener tetap terbit saat registrasi; LoA presenter tetap menunggu hasil review. `InvoiceIssuer` idempoten sehingga pemanggilan setelah keputusan tidak menggandakan invoice.
- Tim Abstrak memakai `POST /api/tim-abstrak/submissions/{id_abstract}/review` dan payload DB `hasil_review`/`catatan_review`. Draft, override check-in, badge, kit, dan website settings belum didukung API dan tidak boleh ditampilkan sebagai sukses.
- Tarif memakai daftar `GET /api/registration-fees`; pembaruan backend dilakukan per ID lewat `PATCH /api/registration-fees/{id_fee}`, bukan PUT agregat siklus.
# Sekretariat: pencarian registrasi ulang

- Pencarian memakai `registration.id_registration` sebagai nomor yang ditampilkan/dicari, serta nama peserta dan email akun.
- Tidak menambahkan kolom nomor registrasi atau nomor badge karena identitas tersebut belum memiliki aturan penerbitan dan migrasi historis. Pencarian badge memerlukan keputusan produk dan kontrak penomoran tersendiri.

## Portal Admin

- Dashboard Admin menjadi satu-satunya halaman ringkasan. `/admin/overview` tetap tersedia sebagai redirect kompatibilitas ke `/admin/dashboard`.
- Angka dashboard dihitung dari `ReportService` dan tabel operasional. Bagian pembayaran hanya ditampilkan untuk pengguna dengan akses lihat `verify_payment`; nominal disebut IDR karena skema tidak memiliki kode mata uang.
- Aktivitas dashboard hanya memakai registrasi, verifikasi pembayaran, dan review yang benar-benar tersimpan. Tidak ada kurva, target, kanal pembayaran, atau aktivitas contoh.
- Audit perubahan ACL dan akun memakai tabel `acl_audit_log` baru. Baris audit mempertahankan email pelaku setelah akun dihapus; metadata tidak menyimpan password atau kredensial.
- Akun Admin yang sedang dipakai tidak dapat dihapus, dinonaktifkan, atau diturunkan perannya. Operasi tunggal dan batch juga harus menjaga setidaknya satu Admin aktif.
- Tanda tangan digital memakai endpoint dan penyimpanan privat yang telah tersedia, dengan capability `manage_digital_signatures`.
- CRUD arsip, CRUD template dokumen, dan pengaturan website global belum memiliki kontrak backend. Halaman tetap menampilkan **Belum tersedia** dengan aksi nonaktif. Halaman template menyebut kedua kelompok yang diminta: dokumen terbit dan template berkas unduhan.
