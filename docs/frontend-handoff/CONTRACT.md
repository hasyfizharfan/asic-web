# Kontrak Integrasi View & Route (CONTRACT.md)

Dokumen ini memetakan seluruh view Blade frontend, nama route preview `fe.*`, variabel yang diharapkan oleh setiap view, beserta kolom untuk nama route final backend. Tim backend dapat menggunakan tabel ini untuk menyiapkan controller tanpa perlu membaca source code Blade satu per satu.

---

## Tabel Kontrak Halaman Publik & Autentikasi

| Nama View Blade | Route Preview FE | URL Preview | Variabel Data yang Diharapkan | Tipe & Contoh Bentuk | Nullable? | Nama Route Final (diisi BE) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `public.landing` | `fe.home` | `/_fe` | `$asic` | `array` (`year`, `full_title`, `theme`, `dates`, `venue`, `fees`, `email`, dll.) | Tidak | `home` |
| `auth.participant` | `fe.participant.login` | `/_fe/participant/login` | `$errors` | `ViewErrorBag` | Ya | `participant.login` |
| `auth.secretariat` | `fe.secretariat.login` | `/_fe/secretariat/login` | `$errors` | `ViewErrorBag` | Ya | `secretariat.login` |
| `auth.treasurer` | `fe.treasurer.login` | `/_fe/treasurer/login` | `$errors` | `ViewErrorBag` | Ya | `treasurer.login` |
| `auth.admin` | `fe.admin.login` | `/_fe/admin/login` | `$errors` | `ViewErrorBag` | Ya | `admin.login` |

---

## Tabel Kontrak Portal Peserta (Participant)

| Nama View Blade | Route Preview FE | URL Preview | Variabel Data yang Diharapkan | Tipe & Contoh Bentuk | Nullable? | Nama Route Final (diisi BE) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `participant.dashboard` | `fe.participant.dashboard` | `/_fe/participant/dashboard` | `$participant`<br>`$isListener`<br>`$participantType` | `array` (`name`, `reg_no`, `institution`, `abstract_status`, `payment_status`, dll.)<br>`bool`<br>`string` ('oral', 'poster', 'listener') | Tidak | `participant.dashboard` |
| `participant.abstract` | `fe.participant.abstract` | `/_fe/participant/abstract` | `$abstract` | `array` (`id`, `title`, `theme`, `authors`, `keywords`, `abstract_text`, `status`, `submitted_at`, `file_url`, `reviewer_notes`) | Tidak (Presenter) | `participant.abstract` |
| `participant.documents` | `fe.participant.documents` | `/_fe/participant/documents` | `$documents` | `array` of 5 items (`LOA`, `Tagihan`, `Bukti Pembayaran`, `Berkas Abstrak`, `Sertifikat`) | Tidak | `participant.documents` |
| `participant.payment.checkout` | `fe.participant.payment.checkout` | `/_fe/participant/payment/checkout` | `$invoice` | `array` (`number`, `amount_idr`, `amount_usd`, `due_date`, `status`, `items`, `payment_method`) | Tidak | `participant.payment.checkout` |
| `participant.attendance-qr` | `fe.participant.attendance-qr` | `/_fe/participant/attendance-qr` | `$checkin` | `array` (`qr_code`, `participant_name`, `reg_no`, `status`, `hall_name`) | Tidak | `participant.attendance-qr` |
| `participant.full-paper` | `fe.participant.full-paper` | `/_fe/participant/full-paper` | `$paper` | `array` (`status`, `file_name`, `due_date`, `uploaded_at`, `notes`) | Ya | `participant.full-paper` |
| `participant.certificate` | `fe.participant.certificate` | `/_fe/participant/certificate` | `$certificate` | `array` (`ready`, `cert_number`, `type_label`, `download_url`) | Tidak | `participant.certificate` |
| `participant.notifications` | `fe.participant.notifications` | `/_fe/participant/notifications` | `$notifications` | `array` of notification items (`id`, `title`, `message`, `read`, `created_at`) | Tidak | `participant.notifications` |
| `participant.profile` | `fe.participant.profile` | `/_fe/participant/profile` | `$profile` | `array` (`name`, `email`, `phone`, `institution`, `country`, `affiliation`) | Tidak | `participant.profile` |

---

## Tabel Kontrak Portal Sekretariat (Secretariat)

| Nama View Blade | Route Preview FE | URL Preview | Variabel Data yang Diharapkan | Tipe & Contoh Bentuk | Nullable? | Nama Route Final (diisi BE) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `secretariat.abstracts.index` | `fe.secretariat.abstracts.index` | `/_fe/secretariat/abstracts` | `$abstracts`<br>`$statistics` | `LengthAwarePaginator`<br>`array` (`total`, `under_review`, `accepted`, `rejected`) | Tidak | `secretariat.abstracts.index` |
| `secretariat.abstracts.show` | `fe.secretariat.abstracts.show` | `/_fe/secretariat/abstracts/{id}` | `$abstract` | `array` (detail lengkap manuskrip, form review, status) | Tidak | `secretariat.abstracts.show` |
| `secretariat.attendance` | `fe.secretariat.attendance` | `/_fe/secretariat/attendance` | `$attendanceList` | `LengthAwarePaginator` / `array` | Tidak | `secretariat.attendance` |
| `secretariat.announcements` | `fe.secretariat.announcements` | `/_fe/secretariat/announcements` | `$announcements` | `array` of announcement records | Tidak | `secretariat.announcements` |
| `secretariat.recap` | `fe.secretariat.recap` | `/_fe/secretariat/recap` | `$recapData` | `array` statistik demografi negara, track, dsb. | Tidak | `secretariat.recap` |

---

## Tabel Kontrak Portal Bendahara (Treasurer)

| Nama View Blade | Route Preview FE | URL Preview | Variabel Data yang Diharapkan | Tipe & Contoh Bentuk | Nullable? | Nama Route Final (diisi BE) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `treasurer.tariffs` | `fe.treasurer.tariffs` | `/_fe/treasurer/tariffs` | `$tariffs` | `array` kategori tarif (Early bird, Regular, Mahasiswa, Internasional) | Tidak | `treasurer.tariffs` |
| `treasurer.payments.index` | `fe.treasurer.payments.index` | `/_fe/treasurer/payments` | `$payments`<br>`$summary` | `LengthAwarePaginator`<br>`array` (`pending`, `verified`, `total_idr`, `total_usd`) | Tidak | `treasurer.payments.index` |
| `treasurer.receipts.preview` | `fe.treasurer.receipts.preview` | `/_fe/treasurer/receipts/preview` | `$receipt` | `array` (`receipt_no`, `payer_name`, `amount`, `paid_at`, `verified_by`) | Tidak | `treasurer.receipts.preview` |
| `treasurer.refunds` | `fe.treasurer.refunds` | `/_fe/treasurer/refunds` | `$refundRequests` | `array` of refund application items | Tidak | `treasurer.refunds` |

---

## Tabel Kontrak Portal Admin (Administrator)

| Nama View Blade | Route Preview FE | URL Preview | Variabel Data yang Diharapkan | Tipe & Contoh Bentuk | Nullable? | Nama Route Final (diisi BE) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `admin.overview` | `fe.admin.overview` | `/_fe/admin/overview` | `$stats`<br>`$recentLogs` | `array` (metrik global sistem, kapasitas server, user aktif)<br>`array` log audit | Tidak | `admin.overview` |
| `admin.users` | `fe.admin.users` | `/_fe/admin/users` | `$users` | `LengthAwarePaginator` daftar akun semua role | Tidak | `admin.users` |
| `admin.permissions` | `fe.admin.permissions` | `/_fe/admin/permissions` | `$rolesPermissions` | `array` mapping role ke permission RBAC | Tidak | `admin.permissions` |
| `admin.templates` | `fe.admin.templates` | `/_fe/admin/templates` | `$templates` | `array` daftar file template dokumen & email | Tidak | `admin.templates` |
| `admin.settings` | `fe.admin.settings` | `/_fe/admin/settings` | `$settings` | `array` konfigurasi tanggal, email gateway, kuota | Tidak | `admin.settings` |
| `admin.archive` | `fe.admin.archive` | `/_fe/admin/archive` | `$archives` | `array` riwayat konferensi tahun sebelumnya | Tidak | `admin.archive` |

---

## Halaman Error (Global / Preview)

| Nama View Blade | Route Preview FE | URL Preview | Variabel Data yang Diharapkan | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `asic.errors.401` | `fe.error` | `/_fe/errors/401` | `$code`, `$title`, `$message`, `$icon` | Unauthorized Access |
| `asic.errors.403` | `fe.error` | `/_fe/errors/403` | `$code`, `$title`, `$message`, `$icon` | Access Forbidden |
| `asic.errors.404` | `fe.error` | `/_fe/errors/404` | `$code`, `$title`, `$message`, `$icon` | Page Not Found |
| `asic.errors.419` | `fe.error` | `/_fe/errors/419` | `$code`, `$title`, `$message`, `$icon` | CSRF Token / Page Expired |
| `asic.errors.429` | `fe.error` | `/_fe/errors/429` | `$code`, `$title`, `$message`, `$icon` | Too Many Requests (Rate limit) |
| `asic.errors.500` | `fe.error` | `/_fe/errors/500` | `$code`, `$title`, `$message`, `$icon` | Internal Server Error |

## Stage C — Integrasi view ke kontrak API

| View action lama | Kontrak yang dipakai | Payload / perilaku |
| --- | --- | --- |
| `participant.registration` → `/api/participant/register` (tidak ada) | `POST /api/registrations` | `id_event`, `id_period`, `registration_type` (`Student`/`Regular`/`Committee`), `participation_type` (`Oral Presenter`/`Poster Presenter`/`Listener`), `attendance_mode` (`Daring`/`Luring`), `publication_option`, opsional `add_ons`. Arahkan ke checkout hanya setelah HTTP 201 dan menerima `id_registration`. Event dan periode dipilih dari `GET /api/public/events` serta `GET /api/public/registration-fees`. |
| `reviewer.detail` → `/api/reviewer/abstracts/{id}/review` (tidak ada) | `POST /api/tim-abstrak/submissions/{id_abstract}/review` | `hasil_review: Diterima|Ditolak`, `catatan_review`; UI baru terkunci setelah sukses. Draft review belum memiliki endpoint. |
| `treasurer.tariffs` → `/api/treasurer/tariffs` (tidak ada) | `GET /api/registration-fees`; perubahan memakai `PATCH /api/registration-fees/{id_fee}` | Setiap baris tarif harus menggunakan kontrak `RegistrationFeeResource` dan payload `UpdateRegistrationFeeRequest`. PUT agregat per siklus tidak didukung. |
| `secretariat.reregistration` → `/api/secretariat/checkin/{override,print-badge,issue-kit}` (tidak ada) | Scan QR staf: `POST /api/sessions/{id_session}/attendance/scan`; sesi peserta: `GET /api/sessions/{id_session}/attendees` | Payload scan mengikuti `ScanAttendanceRequest`. Override, cetak badge, penerbitan kit, dan pencarian peserta berdasarkan nomor registrasi belum tersedia; UI tidak boleh menandai tindakan tersebut berhasil. |
| `admin.settings` → `/api/admin/settings` (tidak ada) | Belum ada endpoint settings | Tombol simpan harus menyatakan fitur belum tersedia, tanpa menyimpan state lokal. |

Status API dibungkus sebagai `{success, message?, data}`. Untuk daftar berhalaman, baca item dari `data.data`; untuk `RegistrationFeeResource`, field tarif menggunakan `id_event`, `id_period`, `registration_type`, `attendance_mode`, `tujuan_publikasi`, `final_amount`, serta relasi `event` dan `period`.

### Field UI yang memakai nama/enum database

- Halaman abstrak menampilkan `id_abstract` integer (PK) sebagai identifier; kode tampilan lama `ASIC27-*` tidak menjadi route ID. Status ditampilkan memakai nilai DB `Diajukan`, `Revisi`, `Diterima`, `Ditolak`.
- Status invoice ditampilkan memakai nilai DB (`Menunggu Pembayaran`, `Menunggu Verifikasi`, `Terverifikasi`, `Ditolak`, `Kedaluwarsa`). UI tidak membuat status `failed` atau `under_review` sendiri.
- Profil peserta ditampilkan lewat relasi `registration.participant`; file download menggunakan endpoint terotorisasi `participant-files/{id_file}/download`, bukan `file_url` publik.
- Endpoint kehadiran mengembalikan token `qr_token`; format gambar QR/kamera belum menjadi bagian kontrak backend.

### Pemetaan Tahap C tambahan

- `admin.users` memakai `GET/POST /api/admin/users`, `GET/PUT/DELETE /api/admin/users/{id_user}`, `PATCH /api/admin/users/{id_user}/toggle-status`, `POST /api/admin/users/batch-action`, dan `GET /api/admin/users/export-csv`. Reset password admin dan pengiriman undangan tetap belum tersedia.
- `secretariat.announcements` memakai CRUD `/api/content/announcements` dan `/api/content/communication-channels`. Penyimpanan pengumuman tidak mengirim email/WhatsApp karena gateway broadcast belum tersedia.
- Editor `secretariat.content` untuk event, speaker, dan jadwal masih belum terhubung penuh; endpoint CRUD tersedia dan pekerjaan UI tercatat di `REMAINING.md`.

