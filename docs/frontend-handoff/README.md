# Dokumentasi Handoff Frontend - ASIC 2027

Selamat datang di repositori Frontend ASIC 2027 (Agrifood System International Conference), Fakultas Pertanian, Universitas Andalas.

Dokumen ini menjelaskan cara menjalankan route preview frontend, arsitektur isolasi frontend, serta kontrak integrasi untuk tim Backend (BE).

---

## 1. Syarat Menjalankan Preview Lokal

1. **Konfigurasi Environment (.env):**
   * Pastikan `SESSION_DRIVER` diatur ke `file`:
     ```env
     SESSION_DRIVER=file
     ```
   * Sesuai aturan isolasi runtime, route preview tidak boleh menulis ke tabel database `sessions`. Jika driver bernilai `database`, route preview akan menolak memproses simulasi login/session demi menjaga integritas database BE.

2. **Kompilasi Aset Frontend (Vite & Tailwind v4):**
   * Jalankan Vite development server:
     ```bash
     npm run dev
     ```
   * Atau lakukan build produksi untuk aset mandiri FE:
     ```bash
     npm run build
     ```
   * *Catatan:* File entry CSS/JS frontend berada di `resources/css/asic.css` dan `resources/js/asic.js` (tidak mengubah atau mengotori `app.css` / `app.js` milik BE).

3. **Menghidupkan Server Lokal Laravel:**
   ```bash
   php artisan serve
   ```

---

## 2. URL Awal & Akses Route Preview

Seluruh route preview frontend menggunakan prefix URL `/_fe/` dan prefix nama route `fe.*`.

* **Landing Page (Publik):**
  `http://127.0.0.1:8000/_fe` (Route: `fe.home`)
* **Portal Peserta (Participant Portal):**
  `http://127.0.0.1:8000/_fe/participant/login` (Route: `fe.participant.login`)
* **Portal Sekretariat (Secretariat Portal):**
  `http://127.0.0.1:8000/_fe/secretariat/login` (Route: `fe.secretariat.login`)
* **Portal Bendahara (Treasurer Portal):**
  `http://127.0.0.1:8000/_fe/treasurer/login` (Route: `fe.treasurer.login`)
* **Portal Administrator (Admin Portal):**
  `http://127.0.0.1:8000/_fe/admin/login` (Route: `fe.admin.login`)
* **Halaman Error Preview (401, 403, 404, 419, 429, 500):**
  `http://127.0.0.1:8000/_fe/errors/{code}` (Route: `fe.error`)
* **Komponen & UI Gallery:**
  `http://127.0.0.1:8000/_fe/_ui` (Route: `fe.dev.ui`)

---

## 3. Navigasi Dokumen Handoff

* [`PREVIEW-SETUP.md`](./PREVIEW-SETUP.md) — Opsi penyambungan route preview (Pilihan A vs Pilihan B).
* [`CONTRACT.md`](./CONTRACT.md) — Kontrak view, route `fe.*`, dan variabel data yang diharapkan Blade.
* [`CHANGES.md`](./CHANGES.md) — Daftar file baru dan git diff lengkap dari file bersama yang disentuh.
* [`REQUESTS-FOR-BE.md`](./REQUESTS-FOR-BE.md) — Permintaan dan rekomendasi implementasi backend.
* [`check-fe-scope.sh`](./check-fe-scope.sh) — Skrip verifikasi batasan wilayah kerja frontend.

