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
- Agar urutan alur yang disepakati dapat dilakukan (registrasi → invoice → bukti bayar → verifikasi → abstrak), invoice kini diterbitkan untuk semua registrasi yang memiliki tarif. LoA Listener tetap terbit saat registrasi; LoA presenter tetap menunggu hasil review. `InvoiceIssuer` idempoten sehingga pemanggilan setelah keputusan tidak menggandakan invoice. **Digantikan Tahap H (KF-20):** invoice presenter kini terbit saat abstrak Diterima.
- Tim Abstrak memakai `POST /api/tim-abstrak/submissions/{id_abstract}/review` dan payload DB `hasil_review`/`catatan_review`. Draft, override check-in, badge, kit, dan website settings belum didukung API dan tidak boleh ditampilkan sebagai sukses.
- Tarif memakai daftar `GET /api/registration-fees`; pembaruan backend dilakukan per ID lewat `PATCH /api/registration-fees/{id_fee}`, bukan PUT agregat siklus.
# Sekretariat: pencarian registrasi ulang

- Pencarian memakai `registration.id_registration` sebagai nomor yang ditampilkan/dicari, serta nama peserta dan email akun.
- Tidak menambahkan kolom nomor registrasi atau nomor badge karena identitas tersebut belum memiliki aturan penerbitan dan migrasi historis. Pencarian badge memerlukan keputusan produk dan kontrak penomoran tersendiri.

## Portal Admin

- Dashboard Admin menjadi satu-satunya halaman ringkasan. `/admin/overview` tetap tersedia sebagai redirect kompatibilitas ke `/admin/dashboard`.
- Angka dashboard dihitung dari `ReportService` dan tabel operasional. Bagian pembayaran hanya ditampilkan untuk pengguna dengan akses lihat `verify_payment`; nominal ditampilkan per mata uang (IDR dan USD terpisah, Tahap H).
- Aktivitas dashboard hanya memakai registrasi, verifikasi pembayaran, dan review yang benar-benar tersimpan. Tidak ada kurva, target, kanal pembayaran, atau aktivitas contoh.
- Audit perubahan ACL dan akun memakai tabel `acl_audit_log` baru. Baris audit mempertahankan email pelaku setelah akun dihapus; metadata tidak menyimpan password atau kredensial.
- Akun Admin yang sedang dipakai tidak dapat dihapus, dinonaktifkan, atau diturunkan perannya. Operasi tunggal dan batch juga harus menjaga setidaknya satu Admin aktif.
- Tanda tangan digital memakai endpoint dan penyimpanan privat yang telah tersedia, dengan capability `manage_digital_signatures`.
- CRUD arsip, CRUD template dokumen, dan pengaturan website global belum memiliki kontrak backend. Halaman tetap menampilkan **Belum tersedia** dengan aksi nonaktif. Halaman template menyebut kedua kelompok yang diminta: dokumen terbit dan template berkas unduhan.

## Tahap H — Perbaikan hasil audit PRD

### KF-12 registrasi ulang luring hari pertama

- Registrasi ulang menjadi konsep tersendiri: kolom baru `registration.reregistered_at` dan `registration.reregistered_by` (nullable, FK `user_account` nullOnDelete) lewat migration baru.
- Endpoint `POST /api/events/{id_event}/reregistration/scan` (body `qr_token`) dan `POST /api/events/{id_event}/reregistration` (body `id_registration`, untuk hasil pencarian manual), keduanya `permission:scan_attendance_qr` (full_access) dan memakai satu aturan di `ReregistrationService`: Terdaftar, event cocok (422), Luring (Daring 422), invoice Terverifikasi (403), sudah registrasi ulang (409, tanpa mengubah waktu), hari ini = `event.tanggal_mulai` (403 "Registrasi ulang hanya dibuka pada hari pertama kegiatan").
- Urutan pemeriksaan: kecocokan event → status → mode → pembayaran → sudah registrasi ulang → hari pertama. Peserta yang sudah registrasi ulang tetap mendapat 409 (bukan 403) bila dipindai di hari berikutnya agar petugas tahu statusnya.
- Zona waktu aplikasi kini `env('APP_TIMEZONE', 'Asia/Jakarta')` (sebelumnya `UTC`). Tanggal hari pertama dan waktu sesi dibandingkan dalam WIB.
- Kehadiran sesi (KF-14, `AttendanceController`) tidak diubah. Halaman Sekretariat `/secretariat/reregistration` kini hanya untuk registrasi ulang; pencatatan per sesi tetap di menu Kehadiran. Override, badge, dan kit tetap **Belum tersedia**.
- **Selisih yang dilaporkan, tidak diubah di tahap ini:** `AttendanceController::attendanceQr` menerbitkan QR kehadiran untuk peserta Daring maupun Luring, sedangkan PRD KF-13 hanya menyebut Luring (kesepakatan mitra Meeting 2 meminta QR untuk semua peserta). Peserta Daring tetap dapat hadir lewat QR sesi.

### KF-20 invoice presenter setelah abstrak diterima

- Menggantikan keputusan Tahap C. Alasan: PRD KF-20 menetapkan invoice dibuat "saat abstrak Presenter diterima atau saat Listener selesai mendaftar"; menagih presenter sebelum seleksi berisiko meminta pembayaran untuk abstrak yang kemudian ditolak (biaya seminar tidak dapat dikembalikan).
- `RegistrationObserver::created` hanya menerbitkan invoice + LoA untuk Listener. Invoice presenter diterbitkan `AbstractDecisionProcessor` saat Diterima (idempoten lewat `InvoiceIssuer`); abstrak Ditolak hanya mendapat surat penolakan.
- Pengiriman abstrak tidak bergantung pada pembayaran. Halaman `participant/abstract` sebelumnya memblokir pengiriman sebelum pembayaran Terverifikasi (kebuntuan dengan aturan baru) — pemblokiran itu dihapus. Setelah registrasi, presenter diarahkan ke halaman abstrak, Listener ke checkout.
- Unggah bukti bayar presenter yang abstraknya belum Diterima ditolak 422 (berlaku juga untuk invoice lama yang terbit dini).
- Data lama tidak dihapus. `php artisan asic:report-early-presenter-invoices` (read-only) mencantumkan invoice presenter yang abstraknya belum Diterima beserta jumlahnya.

### Mata uang IDR dan USD

- Kolom baru `registration_fee.mata_uang` dan `invoice.mata_uang` enum('IDR','USD') default 'IDR' (snapshot saat terbit). Data lama otomatis IDR.
- Aturan peserta dipusatkan di `App\Support\Currency::forParticipant`: negara `Indonesia` (juga `ID`, `IDN`, `Republic of Indonesia`, `Republik Indonesia`, tanpa membedakan huruf besar/kecil) atau kosong → IDR; selain itu → USD. Negara kosong dianggap IDR karena registrasi akun mengisi `Indonesia` sebagai bawaan. Form daftar akun kini meminta negara.
- `InvoiceIssuer` mencari tarif dengan mata uang peserta. Bila tarif untuk mata uang itu belum ada, hasilnya `skipped` dengan alasan yang menyebut mata uangnya; tidak ada fallback ke mata uang lain.
- Add-on (`add_on.harga`) hanya IDR; tidak ditambah kolom mata uang. Peserta bertarif USD tidak dapat memilih add-on (422 saat registrasi) dan `InvoiceIssuer` menolak invoice USD yang memuat add-on.
- Kombinasi tarif (event, periode, kategori, mode, tujuan publikasi, mata uang) divalidasi unik di Form Request dan unique index lewat migration baru; index hanya dibuat bila data lama tidak memiliki duplikat (jika ada, migration mencatat peringatan di log, tidak mengubah data).
- Dokumen: invoice/receipt menampilkan `AMOUNT (IDR|USD)`, format `1,000,000` untuk IDR dan `1,000.00` untuk USD, terbilang "rupiah" atau "US dollars ... cents". Nomor surat tidak berubah.
- Laporan: nominal tidak pernah dijumlahkan lintas mata uang. Kunci lama `total_tagihan`, `amount_verified`, `amount_outstanding` kini berisi nominal **IDR saja**; kunci baru `total_tagihan_by_currency`, `amount_verified_by_currency`, `amount_outstanding_by_currency` berisi `{IDR, USD}`. Ekspor Excel menambah kolom "Mata Uang" di akhir; sheet rekap memisahkan IDR dan USD.
- Pembayaran tetap verifikasi manual. Peserta USD melihat catatan internasional dari `config('asic.payment.international_note')`; tidak ada integrasi payment gateway.

### KF-30 notifikasi perubahan status di dalam situs

- Memakai notifikasi database bawaan Laravel (migration baru `notifications`; `UserAccount` sudah memakai `Notifiable`). `notifiable_id` (unsignedBigInteger) menyimpan `id_user` (increments); tidak ada morph map, `notifiable_type` = `App\Models\UserAccount`. Diuji di SQLite; kolom kompatibel dengan MySQL.
- Satu kelas `App\Notifications\StatusChanged` (channel database saja; email yang sudah ada tidak diubah). Data JSON menyimpan `kind`, `id_registration`, `url`, `params`, dan `dedupe`; judul/isi diterjemahkan saat dibaca (`lang/{id,en}/asic-notifications.php`) agar mengikuti bahasa pengguna.
- Semua pemicu lewat `App\Services\StatusNotifier`: dikirim **sinkron setelah commit** (`DB::afterCommit`) karena aplikasi belum memakai queue untuk pekerjaan serupa (email dokumen juga sinkron). Kegagalan dibungkus try/catch + `Log::warning('notification.failed')` dan tidak membatalkan transaksi utama.
- Idempoten lewat kunci dedupe per peristiwa (`document:{id}`, `abstract:{id}:{status}`, `verification:{id}`, `invoice_extended:{id}:{ke-n}`, `invoice_expired:{id}`, `reregistered:{id}`).
- Pemicu: dokumen terbit LoA/Invoice/Receipt/Penolakan (satu titik di `DocumentService::issue`, termasuk Sertifikat bila nanti terbit), invoice diperpanjang/kedaluwarsa (`InvoiceController`), bukti bayar Valid/Tidak Valid (`PaymentVerificationController`, alasan penolakan ikut di isi), keputusan abstrak (`AbstractDecisionProcessor`), registrasi ulang tercatat (`ReregistrationService`).
- API milik pengguna sendiri (`auth:sanctum`): `GET /api/me/notifications` (paginasi, `unread=1`, belum dibaca di atas), `GET /api/me/notifications/unread-count`, `PATCH /api/me/notifications/{id}/read` (404 bila bukan milik pengguna), `POST /api/me/notifications/read-all`.
- UI: halaman notifikasi peserta menampilkan daftar nyata, tab belum dibaca, tandai dibaca, tandai semua, empty/error state, dan pengumuman aktif dari `/api/announcements` (pengumuman tidak disalin menjadi notifikasi). Lonceng di topbar tampil untuk semua role dengan jumlah belum dibaca; role non-peserta saat ini selalu 0 karena belum ada pemicu untuk staf.

## Tahap I — Konten publik dan KF-35

### Apa yang nyata
- Tabel baru (migration baru, data lama tidak disentuh): `event_content` (section tetap: scope, accommodation, pickup, presentation_oral, presentation_poster, visa, travel, sponsorship_intro, exhibition_intro), `important_date`, `faq`, `contact_person`, `event_photo`, `sponsorship_package`, `sponsorship_inquiry`, `legal_document`, `user_consent`. Konten dua bahasa memakai pasangan `*_id`/`*_en`; bila bahasa aktif kosong, ditampilkan bahasa lain (`HasBilingualFields::localized`).
- API pengelola `/api/content/{event-content|important-dates|faqs|contacts|sponsorship-packages}` (CRUD + `PATCH .../reorder`), `/api/content/photos`, `/api/content/sponsorship-inquiries` (GET + PATCH status/catatan saja), `/api/content/legal-documents` (+ `POST .../{id}/publish`). Izin `manage_website_content` (GET view_only, tulis full_access). Jenis sederhana memakai satu `ContentItemController` + `ContentItemRequest` dengan aturan dari `App\Support\Content\ContentTypes` agar validasi seragam.
- API publik tanpa login: `/api/public/events/{id_event}/{content|important-dates|faqs|contacts|photos|sponsorship-packages}` (event Aktif/Selesai, status Dipublikasikan), `/api/public/legal-documents/{jenis}`, `POST /api/public/sponsorship-inquiries` (throttle 5/menit/IP, honeypot `website`).
- Halaman publik server-rendered: `/venue`, `/guidelines`, `/faq`, `/contact`, `/sponsorship` (paket + form), `/travel`, `/gallery`, `/terms`, `/privacy`, `/ethics`, dengan meta title/description per halaman. Landing: ruang lingkup dari blok `scope`, tanggal penting, FAQ ringkas + kontak, sponsor ringkas, galeri, tautan venue/panduan/perjalanan; tarif dari database per kategori dan mata uang. Teks "IDR TBA"/"USD TBA" dan daftar ruang lingkup statis dihapus.
- Pengelola: partial `content/manager` dipakai di `/secretariat/content` (di bawah pengelola event/pembicara/jadwal yang sudah ada) dan halaman baru `/admin/content` (`AdminController@content`, `portal.admin.content`, item nav Admin). Tab: ruang lingkup & panduan, tanggal penting, FAQ, kontak PIC, galeri, paket sponsor, permintaan sponsor, syarat & privasi; penanda "Terjemahan belum lengkap"; peringatan bila jenis dokumen hukum belum punya versi terpublikasi.
- KF-35: `agree_terms` wajib (`required|accepted`) di web dan API; alias lama `terms` disamakan lewat `prepareForValidation`; pesan dari lang file memakai `config('asic.edition')` (tidak lagi "ASIC 2026"). Registrasi mencatat `user_consent` untuk terms/privacy/ethics dalam transaksi pembuatan akun; jenis yang belum terpublikasi dicatat dengan versi null + log peringatan. Form daftar menautkan ketiga halaman (tab baru).
- Audit (`acl_audit_log`): unggah/hapus foto, publikasi dokumen hukum, perubahan status permintaan sponsor, serta tambah/ubah/hapus konten.

### Keputusan
- Konten panitia hanya teks biasa: dirender `e()` + `nl2br`, tanpa HTML mentah. Tautan wajib `https://` atau `mailto:` (validasi regex); `http://`, `javascript:`, `data:` ditolak.
- IP persetujuan disimpan **teranonimkan** (IPv4 oktet terakhir → 0; IPv6 80 bit terakhir dibuang). Dipilih daripada tidak menyimpan sama sekali agar tetap ada bukti asal persetujuan tanpa menyimpan alamat lengkap.
- Dokumen hukum: satu versi Dipublikasikan per jenis; publikasi versi baru mengarsipkan yang lama dalam satu transaksi; versi terbit/terarsip tidak dapat diubah atau dihapus (409). Publikasi ditolak bila isi kedua bahasa kosong.
- Nav/footer publik: tautan halaman informasi **dan** legal hanya tampil bila sudah ada isi terpublikasi (konsisten). Form registrasi tetap menautkan halaman legal; halaman menyatakan "belum dipublikasikan" bila kosong.
- Foto: disk `public` di `gallery/{id_event}/{uuid}.{ext}`, jpeg/png/webp maks 5 MB, encode ulang GD (maks sisi 1920 px) untuk membuang EXIF; bila GD tidak ada, berkas disimpan apa adanya dan dicatat di log. Berkas dihapus saat baris dihapus dan saat event dihapus (`Event::deleting`).
- Email pemberitahuan permintaan sponsor memakai `ASIC_CONTACT_EMAIL` (baru, opsional; belum ada kanal email panitia sebelumnya). Kegagalan email tidak menggagalkan penyimpanan.
- `SetLocale` minimal ditambahkan (sesi `locale` id/en → `config('app.locale')`, grup web dan api). Audit multibahasa penuh dikerjakan di Prompt 1 Langkah 5.

### Ditunda / perlu diisi panitia
- Isi teks hukum, paket sponsor, konten visa/penginapan/penjemputan, FAQ, kontak, dan foto **tidak** diisi agen; panitia mengisinya lewat `/secretariat/content` atau `/admin/content`. Tidak ada seeder konten karangan.
- Unduhan berkas panduan presentasi ditambahkan di Fase 2.

## Tahap J — Dokumen, sertifikat, dan arsip

### KF-13 QR kehadiran luring
- `attendanceQr` kini hanya menerbitkan token untuk registrasi Terdaftar + Luring + Terverifikasi; Daring 403 dengan pesan dari lang file. Token lama milik peserta Daring ditolak `scanParticipantQr` (403). `qr_attendance_token` disembunyikan dari serialisasi `Registration` agar hanya keluar lewat endpoint attendance-qr.
- **Perbedaan dengan kesepakatan mitra:** Meeting 2 meminta QR untuk semua peserta termasuk daring. Mengikuti PRD KF-13, peserta Daring tidak mendapat QR pribadi, tetapi tetap tercatat hadir lewat QR sesi (`submitSessionQr`) sehingga syarat sertifikat bisa terpenuhi. Test lama `AttendanceQrTest` (perbaikan BUG-013) diganti: Daring 403 untuk QR pribadi dan tetap dapat hadir lewat QR sesi. Perlu dikonfirmasi ke mitra.
- Halaman peserta menggambar QR di klien dengan paket npm `qrcode` (tanpa layanan luar), tombol unduh PNG, token teks cadangan, dan keterangan bahwa QR ini berbeda dengan QR pembayaran.

### KF-26/KF-06 template dokumen
- Migration baru: enum `jenis_dokumen` ditambah `Panduan` (cara `->change()` seperti migration 2026_11_12_000004), kolom nullable `subjenis`, `bahasa`, `versi`, `nama_file_asli`, `mime`, `ukuran_file`, `id_user_pengunggah`.
- Kelompok 1 (LOA, Penolakan, Invoice, Receipt, Sertifikat): tidak ada unggah DOCX; yang dapat diubah format nomor (token {SEQ} {SCOPE} {EDITION} {YYYY} {MM}, {SEQ} wajib) dan status lewat `PUT /api/document-templates/issued/{jenis}` / `PATCH /{id}`. `GET` menampilkan jenis tanpa baris sebagai "bawaan" tanpa menulis ke DB. Pratinjau `GET /api/document-templates/preview/{jenis}` memakai data berlabel CONTOH/SAMPLE dengan watermark dan tidak membuat IssuedDocument. Penanda tangan dan kop tetap dari `config/asic.php` (ditampilkan read-only).
- Kelompok 2 (Abstrak, Presentasi, Artikel, Panduan): unggah docx/doc/pptx/ppt/pdf maks 10 MB ke disk privat `config('asic.documents_disk')` di `templates/{id_event}/{jenis}/{uuid}.{ext}`; versi baru menonaktifkan versi Aktif dengan event/jenis/subjenis/bahasa sama dalam satu transaksi; tidak ada hapus permanen. Unduhan staf dan publik dialirkan lewat controller dengan nama berkas tersanitasi.
- Publik: `GET /api/public/downloads`, `/downloads` (daftar per jenis), `/downloads/{id}` (hanya Aktif, event Aktif/Selesai); tautan panduan di `/guidelines`, tautan template di halaman abstrak peserta. Tautan Unduhan dan Arsip di navigasi publik selalu tampil (halaman indeks dengan keadaan kosong yang jelas).
- Audit: unggah, ubah status, dan ubah format nomor dicatat di `acl_audit_log`.

### KF-27 sertifikat
- Syarat di `config('asic.certificate')`: `min_attendance_sessions` (bawaan 1), `require_event_ended` (bawaan false). `App\Services\CertificateEligibility::evaluate` adalah satu-satunya sumber kebenaran (Terdaftar; hadir ≥ N sesi; presenter juga status presentasi Sudah).
- `documents/certificate.blade.php` A4 landscape, dua bahasa (utama Indonesia bila negara peserta Indonesia, selain itu Inggris), isi dari data registrasi/event saja, ditandatangani Ketua (peran Admin) seperti LoA. Format nomor bawaan `{SEQ}/CERT/{EDITION} ASIC/{YYYY}` kecuali template Aktif berbeda.
- `GET/POST /api/registrations/{id}/certificate` untuk pemilik atau staf `issue_documents`. Penerbitan idempoten (dokumen & nomor sama). Unduh lewat `/api/documents/{id}/download`; Sertifikat ikut daftar dokumen peserta dan memicu notifikasi `certificate_issued`.
- Nama pada sertifikat tidak berubah setelah terbit; koreksi nama = penerbitan ulang manual oleh Admin (**belum diotomasi**).

### KF-29 arsip
- `/admin/archive` menampilkan edisi Selesai/Arsip dengan angka nyata (registrasi, presenter, abstrak diterima, peserta hadir) dan aksi Arsipkan (konfirmasi) lewat `PATCH /api/content/events/{id}`; status Arsip hanya dari Selesai (422), dicatat di audit.
- Publik: `GET /api/public/archive` (q, year, per_page), `/api/public/archive/{id}`, halaman `/archive` dan `/archive/{id}` tanpa data pribadi (hanya agregat jumlah peserta).
- Ditunda: prosiding/DOI/manifest (tidak ada skema) dan seminar lain Fakultas Pertanian (opsional di PRD) — keduanya tampil "Belum tersedia".

## Tahap H (lanjutan) — KF-31 multibahasa

### Apa yang nyata
- `App\Http\Middleware\SetLocale` (grup web dan api): `session('locale')` (hanya `id`/`en`) → `config('app.locale')`; juga `Carbon::setLocale`. Pilihan `/lang/{locale}` kini persisten di request berikutnya; locale lain 404. Untuk API, sesi tersedia bila request berasal dari domain stateful Sanctum (browser same-origin).
- Bahasa bawaan aplikasi `id` (`APP_LOCALE=id`, fallback `en`) karena pengguna utama panitia dan peserta Indonesia. Semua layout memakai `<html lang>` dari locale aktif; pemilih bahasa ada di layout publik (hero/topnav), auth, dan portal (topbar).
- `lang/id/validation.php`, `auth.php`, `passwords.php`, `pagination.php` ditulis manual (tanpa paket baru); `lang/en` dipublikasikan dari framework. Pesan validasi bawaan Laravel mengikuti locale.
- 124 pesan literal Indonesia di controller dipindah ke `lang/{id,en}/asic-api.php` (`messages.*`) dan dipakai lewat `__()`; pesan "Portal ASIC 2026" diperbaiki tanpa tahun.
- Teks di `resources/js/asic.js` disuntikkan dari layout (`window.asicI18n`, `asic-common.js`). View baru/ditulis ulang di tahap H–J memakai lang file (registrasi ulang, tarif, notifikasi, QR kehadiran, sertifikat, template, arsip, pengelola konten, halaman publik).
- Test: paritas kunci rekursif id/en untuk setiap file lang, persistensi locale, locale tidak valid 404, pesan API mengikuti locale, dan daftar frasa tetap yang sudah dipindahkan.

### Belum selesai (tidak diklaim "seluruh halaman")
- **View lama yang masih memuat teks tetap** (hasil audit heuristik, mencakup juga positif palsu seperti nama kelas): secretariat/content (bagian event/pembicara/jadwal), admin/users, admin/permissions, admin/overview, admin/signatures, admin/settings, reviewer/detail, reviewer/queue, reviewer/profile, reviewer/dashboard, secretariat/announcements, secretariat/recap, secretariat/abstracts/index & show, secretariat/attendance, secretariat/dashboard, participant/registration, participant/abstract, participant/payment/checkout, participant/full-paper (dashboard, status pembayaran, dokumen, profil, dan refund peserta sudah dua bahasa sejak 16 Nov malam), treasurer/payments/index & show, treasurer/dashboard, treasurer/receipts/preview, treasurer/refunds, portals/dashboard, components/layout/topbar & sidebar (sebagian), auth/reviewer, auth/participant-register (sebagian), public/landing (bagian lama), halaman error asic/errors/*, emails/reset-password. Alasan: volume besar (± 40 view) dan di luar waktu tahap ini; perlu dikerjakan per portal tanpa mengubah logika.
- **Pesan kustom di Form Request** (± 17 file, mis. `RegisterRequest`, `StoreRegistrationRequest`, `ResetPasswordRequest`) masih berbahasa Indonesia sehingga tidak berubah saat locale `en`. 7 pesan controller berbentuk string interpolasi (kutip ganda) juga belum dipindah.
- Format tanggal di view lama belum seluruhnya memakai helper terpusat; nominal uang sudah lewat `App\Support\Currency::format`/`window.asicMoney`.

## Tahap K — Temuan black box putaran 2 (16 Nov)

Black box putaran 2 (90 skenario Lampiran 6, lewat API + halaman): 89 Sesuai, 1 Belum dapat diuji (tampilan ponsel, perlu cek manual). Perbaikan dari temuan:
- **KF-11 periode pendaftaran:** `GET/POST/PATCH/DELETE /api/registration-periods` (izin `manage_tariffs`; Bendahara dan Admin). Tanggal selesai tidak boleh sebelum tanggal mulai; periode yang sudah dipakai tarif/registrasi tidak dapat dihapus (409, ubah ke Tidak Aktif). Menu di `/treasurer/tariffs` dan `/admin/settings`. Pemindahan otomatis early bird → reguler tetap di luar sistem.
- **KF-23 informasi pembayaran:** tabel baru `app_setting` (kunci-nilai) + `App\Support\PaymentSettings`; nilai dari menu menimpa `config('asic.payment')`/.env. `GET/PUT /api/payment-settings` (`manage_tariffs`). Dipakai di invoice/receipt yang terbit berikutnya (invoice lama tidak berubah), `payment_qr_info` invoice baru, dan halaman pembayaran peserta.
- **KF-32 tautan grup untuk pengunjung:** `GET /api/public/events/{id}/communication-channels` (kanal Aktif, event Aktif/Selesai, hanya nama/jenis/url) dan tampil di `/contact` (hanya URL https).
- Catatan yang tetap perlu konfirmasi PM/mitra: Sekretariat masih dapat membaca daftar judul/penulis/status abstrak (bukan isi naskah) dan data pembayaran (hanya lihat) lewat API untuk keperluan LoA dan dashboard (BUG-021/BUG-024). Halaman seleksi abstrak dan verifikasi pembayaran tertutup untuk Sekretariat.
