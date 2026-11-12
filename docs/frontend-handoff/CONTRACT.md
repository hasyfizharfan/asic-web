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

