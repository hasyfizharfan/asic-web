# Log Keputusan Sistem ASIC 2027

Catatan keputusan aturan bisnis yang dipakai sistem, supaya jelas **apa yang diputuskan, oleh siapa, atas dasar apa, dan statusnya**.
Keputusan berstatus **Sementara** dibuat tim mengikuti praktik umum konferensi karena belum ada jawaban mitra. Keputusan itu sudah berjalan di sistem, tetapi wajib dikonfirmasi ke panitia (Ketua/Bendahara). Bila panitia memutuskan lain, ubah baris ini terlebih dahulu, baru kodenya.

Status:
- **Disepakati mitra**: berasal dari panitia (Meeting 2, 10 Nov 2026, atau dokumen resmi panitia).
- **Ditetapkan tim**: diputuskan tim proyek (QA/PM) dalam batas kesepakatan mitra.
- **Sementara**: praktik umum, menunggu konfirmasi mitra.

| ID | Topik | Keputusan yang berjalan di sistem | Dasar | Status | Tanggal | Diatur di |
|---|---|---|---|---|---|---|
| K-01 | Metode pembayaran | Transfer bank Mandiri (peserta dalam & luar negeri), PayPal (hanya tagihan USD), Wise/Flip ke rekening bank yang sama. Peserta memilih metode saat unggah bukti; Bendahara memverifikasi manual. Tanpa payment gateway. | Template invoice panitia (Mandiri + SWIFT, PayPal/Wise/Flip untuk peserta internasional) | Ditetapkan tim | 18 Nov 2026 | Pengaturan > Informasi pembayaran |
| K-02 | QR kehadiran | Dua QR: QR pribadi (dipindai panitia di meja registrasi ulang luring) dan QR sesi (dipindai peserta untuk absen, luring & daring). | Meeting 2: QR untuk semua peserta + QR per sesi | Ditetapkan tim | 18 Nov 2026 | Sekretariat > Kehadiran |
| K-03 | Font | Calibri di seluruh website dan email. Perangkat tanpa Calibri (Android, iPhone, Linux) memakai Carlito, pengganti Calibri berlisensi bebas dengan lebar huruf sama. Dokumen PDF tetap DejaVu Sans. | Meeting 2: permintaan mitra | Disepakati mitra | 18 Nov 2026 | `resources/css/asic.css`, `partials/fonts.blade.php` |
| K-04 | Biaya transfer | Semua biaya transfer, biaya PayPal, dan selisih kurs ditanggung peserta; dana yang diterima harus sama dengan tagihan. Transfer bank internasional memakai opsi biaya OUR. Kekurangan dana ditolak Bendahara dengan alasan, lalu peserta mengunggah bukti kekurangannya. | Praktik umum konferensi internasional | Sementara | 18 Nov 2026 | Teks di halaman bayar & invoice PDF |
| K-05 | Perpanjangan batas bayar | Hanya invoice bertarif reguler. Dilakukan Bendahara (Detail pembayaran > Perpanjang), maksimal **2 kali**. Jika lewat jatuh tempo lagi setelah perpanjangan ke-2, registrasi otomatis **Dibatalkan** dan peserta diberi tahu. Invoice reguler yang lewat jatuh tempo tanpa perpanjangan berstatus Kedaluwarsa (tidak otomatis batal). | Meeting 2: "harga reguler bisa diperpanjang 1–2 kali, setelah itu peserta dibatalkan" (dipakai angka atas: 2) | Sementara (angka 2) | 18 Nov 2026 | `ASIC_MAX_INVOICE_EXTENSIONS` (.env) |
| K-06 | Early bird telat bayar | Invoice early bird tidak bisa diperpanjang. Saat lewat jatuh tempo, sistem otomatis memindahkan invoice ke tarif periode berikutnya (reguler) dengan **nomor invoice sama**, PDF bertanda "Revised invoice", batas bayar baru 7 hari, dan notifikasi + email ke peserta. Dicek tiap jam (`invoices:process-overdue`). | Meeting 2: diskon hangus bila lewat; "kalau bisa dipindah ke harga reguler" | Sementara | 18 Nov 2026 | `App\Services\OverdueInvoiceProcessor` |
| K-07 | Kurs & biaya PayPal | Kolom opsional di Pengaturan: kurs USD→IDR (untuk menampilkan nilai Rupiah tagihan USD) dan persen biaya PayPal (peserta diminta mengirim tagihan + biaya). Kosong = tidak ditampilkan. | Praktik umum; angka ditentukan Bendahara | Sementara | 18 Nov 2026 | Pengaturan > Informasi pembayaran |
| K-08 | Revisi abstrak | Tidak ada putaran revisi formal. Tim Abstrak memutuskan Diterima/Ditolak dengan catatan yang **terlihat oleh peserta** (identitas reviewer tidak ditampilkan). Peserta boleh mengganti abstrak selama belum diputuskan; setelah diputuskan abstrak terkunci. Revisi hanya untuk artikel lengkap (di luar sistem). | Proposal kegiatan panitia ("memberikan penilaian dan saran, menentukan diterima atau tidak"); praktik umum konferensi | Sementara (tanya Ketua) | 18 Nov 2026 | Halaman Abstrak peserta & Tim Abstrak |
| K-09 | Batas bayar | 7 hari sejak invoice terbit. | Meeting 2 | Disepakati mitra | 10 Nov 2026 | `config/asic.php` `invoice_due_days` |
| K-10 | Verifikasi email | Peserta yang mendaftar sendiri wajib mengklik tautan verifikasi (berlaku 48 jam) sebelum bisa masuk. Akun buatan Admin/data lama dianggap terverifikasi. | Keamanan akun & memastikan email peserta benar (dokumen dikirim lewat email) | Ditetapkan tim | 17 Nov 2026 | `ASIC_REQUIRE_EMAIL_VERIFICATION` |
| K-11 | Pengirim email | Sementara memakai akun Gmail khusus sistem sampai ada email resmi Unand/DTI. | Email Unand belum bisa dipakai tanpa DTI | Sementara | 17 Nov 2026 | `.env` server (`MAIL_*`) |
| K-12 | Kategori peserta | Sistem: Student / Regular / Committee. Template invoice panitia memakai "Lecturer". | Belum diputuskan | **Terbuka** | — | Tarif & registrasi |
| K-13 | Pengembalian dana | Hanya biaya publikasi; biaya seminar tidak dikembalikan. Diproses manual di luar sistem. | Meeting 2 | Disepakati mitra | 10 Nov 2026 | Bendahara > Pengembalian dana |

## Riwayat perubahan
- 18 Nov 2026: dokumen dibuat (K-01 s.d. K-13); K-04 s.d. K-08 diterapkan di kode (ea1c8e4).
- 18 Nov 2026: audit QA (505a1dc): kontak publik memakai alamat resmi panitia dari template (`asic@agr.unand.ac.id`); alamat `@asic.unand.ac.id` hanya dipakai sebagai nama login akun uji (domainnya tidak menerima email).
- 18 Nov 2026: K-03 diubah dari Plus Jakarta Sans (sementara) ke Calibri sesuai permintaan mitra (keputusan tim, Bang).

## Yang perlu ditanyakan ke panitia
1. K-05: perpanjangan maksimal 1 atau 2 kali?
2. K-06: setuju invoice early bird yang telat otomatis pindah ke harga reguler dengan nomor invoice yang sama?
3. K-04/K-07: biaya transfer & kurs ditanggung peserta? Berapa persen biaya PayPal yang dibebankan? Kurs USD→IDR yang dipakai?
4. K-08: apakah abstrak boleh dikembalikan untuk diperbaiki, atau cukup diterima/ditolak dengan saran?
5. K-12: kategori peserta "Lecturer" atau "Regular"?
