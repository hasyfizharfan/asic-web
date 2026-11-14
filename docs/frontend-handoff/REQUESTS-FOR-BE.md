# Permintaan & Rekomendasi untuk Backend (REQUESTS-FOR-BE.md)

Dokumen ini memuat daftar permintaan fitur, usulan potongan kode, dan keputusan arsitektur yang perlu dikerjakan oleh tim Backend (BE) saat proses integrasi.

---

## 1. Penyambungan Route Preview di Lingkungan Lokal
* **Kebutuhan:** Agar tim frontend dan penguji dapat meninjau seluruh halaman preview tanpa mengotori `routes/web.php` di repository git.
* **Usulan Kode:** Tambahkan satu baris berikut di baris paling bawah `routes/web.php`:
  ```php
  if (app()->environment('local') && file_exists(__DIR__.'/fe-preview.php')) {
      require __DIR__.'/fe-preview.php';
  }
  ```

---

## 2. Multi-Role Authentication & Guards
* **Kebutuhan:** Konferensi memiliki 4 role pengguna berbeda dengan akses portal terisolasi:
  1. `participant` (Peserta — Oral, Poster, Listener)
  2. `secretariat` (Tim Review Abstrak, Rundown, Registrasi Ulang)
  3. `treasurer` (Bendahara — Verifikasi Tarif, Pembayaran, Kuitansi, Refund)
  4. `admin` (Administrator Sistem — Hak Akses, Backup, Log, Pengaturan Global)
* **Usulan Keputusan BE:**
  * Opsi 1: Satu tabel `users` dengan kolom enum `role` ('participant', 'secretariat', 'treasurer', 'admin') dan policy/middleware `EnsureUserHasRole`.
  * Opsi 2: Multi-auth guards di `config/auth.php` dengan model khusus jika kredensial dipisahkan.

---

## 3. Session Driver di Lingkungan Lokal
* **Kebutuhan:** Saat pengujian lokal dengan data fixture, pastikan `.env` menggunakan:
  ```env
  SESSION_DRIVER=file
  ```
  Hal ini mencegah route preview mencoba menulis session dummy ke tabel `sessions` database sebelum migrasi DB dijalankan.

---

## 4. Penyimpanan File Manuskrip & Template Author
* **Kebutuhan:**
  * File template manuskrip author (IOP Conference Series) saat ini diletakkan frontend di `resources/private/templates/iop-conference-template.docx`.
* **Usulan Keputusan BE:**
  * Pindahkan template ke private storage: `storage/app/private/templates/iop-conference-template.docx`.
  * Siapkan controller download yang memeriksa autentikasi sebelum mengalirkan file (`Storage::disk('private')->download(...)`).
  * Manuskrip abstrak dan full paper yang diunggah peserta harus disimpan di private disk (bukan public disk) agar tidak dapat diakses tanpa izin melalui browser.

---

## 5. Mesin Pembuat PDF (LoA, Tagihan, Kuitansi, Sertifikat)
* **Kebutuhan:**
  Peserta membutuhkan dokumen PDF resmi untuk:
  1. **LoA (Letter of Acceptance)** — Diterbitkan otomatis saat status abstrak disetujui sekretariat.
  2. **Invoice Tagihan Registrasi** — Mencantumkan nomor referensi, rincian biaya (IDR/USD), dan batas waktu pembayaran.
  3. **Bukti Pembayaran / Kuitansi Resmi** — Diterbitkan otomatis saat bendahara memverifikasi pembayaran.
  4. **Sertifikat Kehadiran / Presenter** — Terkunci hingga peserta melakukan check-in di lokasi via QR.
* **Usulan Package BE:**
  * `barryvdh/laravel-dompdf` (Ringan, cocok untuk layout tabular/kuitansi).
  * Atau `spatie/laravel-pdf` (Berdasarkan headless Chromium, mendukung CSS modern/Tailwind untuk desain sertifikat beresolusi tinggi).

---

## 6. Integrasi Payment Gateway (Dual-Currency: IDR & USD)
* **Kebutuhan:**
  * Peserta domestik: Mendukung QRIS Instant Settlement, Mandiri Virtual Account, BNI Virtual Account, BCA Virtual Account.
  * Peserta internasional: Mendukung PayPal REST API / Kartu Kredit Internasional (Stripe).
* **Usulan Webhook BE:**
  * Endpoint webhook callback: `POST /api/webhooks/payment/midtrans` dan `POST /api/webhooks/payment/paypal`.
  * Status invoice diupdate secara otomatis ke `verified`, notifikasi otomatis dikirim ke peserta via email/portal, dan kuitansi otomatis diterbitkan.

---

## 7. Registrasi Global Halaman Error ASIC
* **Kebutuhan:**
  Frontend telah menyiapkan 6 halaman error bertema ASIC di `resources/views/asic/errors/{401, 403, 404, 419, 429, 500}.blade.php`.
* **Usulan Keputusan BE:**
  * Di `bootstrap/app.php` (Laravel 11), daftarkan penanganan error HTTP untuk merender template ASIC:
  ```php
  ->withExceptions(function (Exceptions $exceptions) {
      $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
          $status = $e->getStatusCode();
          if (view()->exists("asic.errors.{$status}")) {
              return response()->view("asic.errors.{$status}", [], $status);
          }
      });
  })
  ```

