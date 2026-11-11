# Product Requirement Document (PRD)
## Sistem Informasi Kegiatan Seminar Internasional ASIC
**Fakultas Pertanian Universitas Andalas**

---

## 1. Pendahuluan & Informasi Umum
Dokumen ini memuat analisis kebutuhan fungsional, kebutuhan nonfungsional, alur bisnis, serta pembagian modul Sistem Informasi Kegiatan Seminar Internasional ASIC Fakultas Pertanian Universitas Andalas. Kebutuhan disusun berdasarkan hasil wawancara mitra (W) dan referensi situs seminar ICRF 2026 (R).

### Peran Pengguna (User Roles)
1. **Peserta (Presenter - Oral/Poster):** Mengirim abstrak, menerima LOA, melakukan pembayaran, mengunggah berkas presentasi/artikel, mengikuti seminar, presentasi, dan mengunduh sertifikat.
2. **Peserta (Listener):** Mendaftar tanpa abstrak, menerima invoice, melakukan pembayaran, mengikuti seminar, dan mengunduh sertifikat.
3. **Sekretariat:** Mengelola administrasi pendaftaran, validasi berkas, registrasi ulang (pemindaian QR kehadiran), jadwal, pengumuman, dan rekapitulasi data.
4. **Bendahara:** Mengelola tarif pendaftaran, memverifikasi bukti pembayaran secara manual, mengonfirmasi status pembayaran, serta menerbitkan receipt.
5. **Tim Abstrak (Fungsi Seleksi):** Memeriksa, memberikan penilaian, serta menentukan hasil seleksi abstrak (Diterima/Ditolak) beserta catatan review.
6. **Admin:** Ketua/Koordinator yang mengelola teknis sistem, akun pengguna, hak akses peran, serta memantau seluruh data operasional seminar.

---

## 2. Alur Utama Peserta (User Journey)
- **Peserta Presenter (Oral/Poster):**  
  Registrasi Akun $\rightarrow$ Registrasi Pendaftaran $\rightarrow$ Kirim Abstrak $\rightarrow$ Seleksi oleh Tim Abstrak $\rightarrow$ Penerbitan LOA & Invoice Otomatis $\rightarrow$ Bayar (Maksimal 1 Minggu) $\rightarrow$ Unggah Bukti Bayar $\rightarrow$ Verifikasi Bendahara $\rightarrow$ Penerbitan Receipt $\rightarrow$ QR Kehadiran Terbit $\rightarrow$ Registrasi Ulang Hari Pertama (Scan QR) $\rightarrow$ Presentasi & Absensi $\rightarrow$ Unggah Artikel & PPT $\rightarrow$ Unduh Sertifikat.

- **Peserta Listener:**  
  Registrasi Akun $\rightarrow$ Registrasi Pendaftaran $\rightarrow$ Invoice Terbit Otomatis $\rightarrow$ Bayar (Maksimal 1 Minggu) $\rightarrow$ Unggah Bukti Bayar $\rightarrow$ Verifikasi Bendahara $\rightarrow$ Penerbitan Receipt $\rightarrow$ QR Kehadiran Terbit $\rightarrow$ Registrasi Ulang Hari Pertama (Scan QR) $\rightarrow$ Absensi $\rightarrow$ Unduh Sertifikat.

---

## 3. Kebutuhan Fungsional (Functional Requirements)

| Kelompok | Kode | Kebutuhan Fungsional | Aktor | Sumber |
| :--- | :--- | :--- | :--- | :--- |
| **Informasi Kegiatan** | KF-01 | Menampilkan profil seminar, tema, ruang lingkup bidang, tanggal penting, dan galeri foto kegiatan seminar sebelumnya. | Semua Pengunjung | W, R |
| **Informasi Kegiatan** | KF-02 | Menampilkan profil narasumber (nama, foto, institusi, negara asal). | Semua Pengunjung | W, R |
| **Informasi Kegiatan** | KF-03 | Menampilkan biaya dan potongan harga berdasarkan periode (Early Bird/Regular/Late), kategori peserta (Student/Regular/Committee), dan mode kehadiran (Daring/Luring). | Semua Pengunjung | W, R |
| **Informasi Kegiatan** | KF-04 | Menampilkan lokasi acara, penginapan, dan informasi penjemputan. | Semua Pengunjung | W, R |
| **Informasi Kegiatan** | KF-05 | Menampilkan program/jadwal acara dan panduan presentasi (oral/poster). | Semua Pengunjung | W, R |
| **Informasi Kegiatan** | KF-06 | Menyediakan unduhan template abstrak, artikel, presentasi, dan panduan kegiatan. | Semua Pengunjung | W, R |
| **Informasi Kegiatan** | KF-07 | Menampilkan FAQ dan kontak PIC. | Semua Pengunjung | W, R |
| **Akun & Hak Akses** | KF-08 | Menyediakan registrasi akun (registrasi awal), login, dan reset kata sandi. | Peserta, Sekretariat, Bendahara, Tim Abstrak, Admin | W, R |
| **Akun & Hak Akses** | KF-09 | Mengelola akun pengguna, akun panitia, serta hak akses sistem berdasarkan lima peran: Peserta, Sekretariat, Bendahara, Tim Abstrak, dan Admin. | Admin | W |
| **Pendaftaran Peserta** | KF-10 | Menyediakan Registrasi Awal dengan pilihan jenis peserta (Presenter Oral/Poster, Listener), mode kehadiran (Daring/Luring), opsi publikasi (Prosiding/Jurnal/Tidak), dan opsi layanan tambahan (misal: field trip). | Peserta | W |
| **Pendaftaran Peserta** | KF-11 | Menutup Registrasi Awal secara otomatis setelah periode pendaftaran berakhir. | Sistem | W, R |
| **Pendaftaran Peserta** | KF-12 | Mendukung registrasi ulang peserta luring pada hari pertama kegiatan melalui pemindaian QR kehadiran. Hanya berlaku bagi peserta berstatus pembayaran Terverifikasi. | Sekretariat | W |
| **Pendaftaran Peserta** | KF-13 | Menerbitkan QR kehadiran pada akun peserta luring yang pembayarannya Terverifikasi. QR ini berbeda dengan QR pembayaran. | Peserta, Sistem | W |
| **Pendaftaran Peserta** | KF-14 | Memungkinkan Sekretariat memindai QR kehadiran peserta; sistem otomatis memperbarui status kehadiran menjadi Hadir. | Sekretariat, Sistem | W |
| **Abstrak & Berkas** | KF-15 | Menyediakan pengiriman abstrak melalui website dengan pilihan presentasi oral atau poster (khusus Presenter; Listener tidak mengirim abstrak). | Peserta | W, R |
| **Abstrak & Berkas** | KF-16 | Menyediakan fitur unggah artikel lengkap dan berkas presentasi (PPT) melalui website, disertai validasi jenis dan ukuran berkas. | Peserta | W (opsional) |
| **Abstrak & Berkas** | KF-17 | Memungkinkan peserta memantau status abstrak dan berkas yang dikirimkan. | Peserta | W |
| **Seleksi & LOA** | KF-18 | Mendukung seleksi abstrak oleh Tim Abstrak dengan hasil Diterima atau Ditolak beserta catatan review. | Tim Abstrak | W |
| **Seleksi & LOA** | KF-19 | Menerbitkan LOA otomatis bagi abstrak yang diterima dan surat penolakan bagi yang ditolak berdasarkan template. LOA terbit bersamaan dengan invoice dan dikirimkan juga via email. | Sistem, Peserta | W |
| **Pembayaran** | KF-20 | Membuat invoice otomatis saat abstrak Presenter diterima atau saat Listener selesai mendaftar. Menghitung tarif berdasarkan kategori, periode, dan layanan tambahan. Memiliki jatuh tempo 1 minggu. | Sistem, Bendahara | W |
| **Pembayaran** | KF-21 | Memungkinkan peserta mengunggah bukti pembayaran sebelum batas waktu jatuh tempo. | Peserta | W |
| **Pembayaran** | KF-22 | Mendukung Bendahara memeriksa bukti pembayaran secara manual. Jika valid, status menjadi Terverifikasi dan receipt diterbitkan otomatis. Jika tidak valid, Bendahara menolak beserta alasan. | Bendahara, Sistem | W |
| **Pembayaran** | KF-23 | Menampilkan invoice yang memuat informasi QR pembayaran yang disediakan oleh panitia (statis). | Peserta, Bendahara | W |
| **Rekapitulasi Data** | KF-24 | Menyediakan rekapitulasi data bagi Sekretariat (jumlah pendaftar per jenis peserta, abstrak masuk, dan status pembayaran). | Sekretariat | W |
| **Rekapitulasi Data** | KF-25 | Menyediakan fitur ekspor data rekapitulasi ke format Excel. | Sekretariat | W |
| **Template & Sertifikat** | KF-26 | Menyediakan pengelolaan template dokumen (LOA, Surat Penolakan, Invoice, Receipt, Sertifikat, Template Berkas) oleh panitia. | Sekretariat, Bendahara, Admin, Tim Abstrak | W (opsional) |
| **Template & Sertifikat** | KF-27 | Menyediakan unduhan sertifikat otomatis jika syarat terpenuhi (Presenter: Sudah Presentasi & Hadir; Listener: Hadir). | Peserta, Sistem | W (opsional) |
| **Konten & Arsip** | KF-28 | Menyediakan pengelolaan konten website (narasumber, biaya, jadwal, FAQ). | Sekretariat, Bendahara, Admin | W |
| **Konten & Arsip** | KF-29 | Menyediakan arsip seminar sebelumnya dan informasi seminar lain di Fakultas Pertanian. | Admin | W (opsional) |
| **Notifikasi & Komunikasi**| KF-30 | Menyediakan pengumuman dan notifikasi perubahan status (LOA, invoice, receipt) di dalam website. | Sekretariat, Sistem | W |
| **Multibahasa** | KF-31 | Menyediakan tampilan dua bahasa (Bahasa Indonesia dan Bahasa Inggris) di seluruh halaman. | Semua Pengguna | W |
| **Notifikasi & Komunikasi**| KF-32 | Menampilkan tautan grup WhatsApp/Google Group untuk komunikasi peserta dan panitia. | Peserta, Sekretariat | W |
| **Informasi Pendukung** | KF-33 | Menampilkan informasi sponsorship dan exhibition (paket sponsor, pendaftaran). | Semua Pengunjung | R (opsional) |
| **Informasi Pendukung** | KF-34 | Menampilkan informasi visa dan perjalanan bagi peserta luar negeri. | Semua Pengunjung | R (opsional) |
| **Informasi Pendukung** | KF-35 | Menampilkan syarat & ketentuan (terms & conditions) dan kebijakan privasi. | Semua Pengunjung | R (opsional) |

---

## 4. Kebutuhan Nonfungsional (Non-Functional Requirements)
- **KNF-01 (Usability):** Antarmuka yang mudah digunakan oleh peserta umum maupun panitia.
- **KNF-02 (Responsiveness):** Tampilan responsif yang dapat diakses dengan optimal melalui desktop/laptop dan ponsel.
- **KNF-03 (Security & Privacy):** Keamanan akses berbasis peran (Role-Based Access Control) dan perlindungan data pribadi peserta.
- **KNF-04 (Localization):** Konsistensi tampilan multibahasa (Bahasa Indonesia dan Bahasa Inggris) pada seluruh komponen halaman.
- **KNF-05 (Design Reference):** Desain antarmuka terinspirasi dari standar situs konferensi internasional ICRF 2026.
- **KNF-06 (Reusability):** Sistem dapat dikelola dan digunakan kembali untuk seminar periode berikutnya dengan arsip seminar tersimpan.
- **KNF-07 (File Security):** Pembatasan jenis (*mime type*) dan ukuran berkas yang diunggah peserta, serta hak akses berkas terproteksi.

---

## 5. Identifikasi Modul Sistem (Modules)
1. **M-01 Informasi Kegiatan:** Halaman publik, narasumber, lokasi, biaya, FAQ, sponsorship, visa, dan kebijakan privasi.
2. **M-02 Akun dan Hak Akses:** Registrasi akun, autentikasi login/logout, reset password, dan manajemen user 5 peran.
3. **M-03 Pendaftaran Peserta:** Formulir registrasi awal, pilihan kehadiran, registrasi ulang QR, dan marking status presentasi.
4. **M-04 Abstrak dan Berkas:** Pengiriman abstrak, pengunggah artikel lengkap & PPT presentasi, serta monitoring status.
5. **M-05 Seleksi dan LOA:** Penilaian abstrak oleh Tim Abstrak, penerbitan LOA otomatis atau surat penolakan.
6. **M-06 Pembayaran:** Tarif pendaftaran, batas waktu 1 minggu, invoice, unggah bukti bayar, verifikasi Bendahara, dan receipt.
7. **M-07 Manajemen Konten dan Arsip:** Pengelolaan konten publik website dan arsip seminar.
8. **M-08 Notifikasi, Pengumuman, dan Komunikasi:** Sistem pemberitahuan internal web dan tautan WhatsApp Group.
9. **M-09 Multibahasa:** Fitur switch bahasa (ID/EN) yang bersifat lintas modul (*cross-cutting module*).
10. **M-10 Rekapitulasi dan Ekspor Data:** Dashboard rekap data pendaftaran dan statistik serta ekspor file Excel.
11. **M-11 Template Dokumen dan Sertifikat:** Pengelolaan template otomatisasi PDF (LOA, Invoice, Receipt, Sertifikat).

---

## 6. Ketentuan & Siklus Status Pembayaran
- Batas waktu pembayaran invoice adalah **1 minggu** sejak diterbitkan.
- **Status Pembayaran:**
  1. `Menunggu Pembayaran`: Invoice diterbitkan, bukti bayar belum diunggah.
  2. `Menunggu Verifikasi`: Peserta mengunggah bukti bayar, menunggu pemeriksaan Bendahara.
  3. `Terverifikasi`: Bendahara menyetujui pembayaran. Receipt terbit dan QR Kehadiran dibuat.
  4. `Ditolak`: Bendahara menolak bukti bayar beserta alasan. Peserta dapat mengunggah ulang.
  5. `Kadaluwarsa`: Melewati batas 1 minggu tanpa pembayaran terverifikasi. Pendaftaran otomatis batal.