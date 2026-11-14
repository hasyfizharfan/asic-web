# Panduan Setup Preview Frontend (PREVIEW-SETUP.md)

Dokumen ini menjelaskan cara mengaktifkan route preview frontend (`routes/fe-preview.php`) di lingkungan lokal tanpa mengganggu atau memodifikasi file backend (`routes/web.php`).

---

## 1. Pilihan Penyambungan Route Preview

Pilih salah satu dari dua opsi berikut untuk mengaktifkan route preview:

### Pilihan A (Disarankan): Ditambahkan oleh Backend Developer
Backend developer menambahkan satu baris pengaman permanen di baris paling akhir file `routes/web.php`:
```php
if (app()->environment('local') && file_exists(__DIR__.'/fe-preview.php')) {
    require __DIR__.'/fe-preview.php';
}
```
* **Kelebihan:** Bersih, terproteksi hanya aktif di environment `local`, dan otomatis aktif untuk siapa saja yang menguji frontend di lokal.

---

### Pilihan B (Lokal Saja, Tidak Ikut Commit): Wiring Lokal
Jika backend developer belum menambahkan baris di atas, pengembang frontend dapat menyambungkannya secara lokal menggunakan mekanisme `skip-worktree` dari Git agar perubahan pada `routes/web.php` tidak terlacak oleh Git dan tidak ikut ter-commit:

1. **Jalankan perintah skip-worktree:**
   ```bash
   git update-index --skip-worktree routes/web.php
   ```
2. **Tambahkan baris berikut di akhir `routes/web.php`:**
   ```php
   if (app()->environment('local') && file_exists(__DIR__.'/fe-preview.php')) {
       require __DIR__.'/fe-preview.php';
   }
   ```
3. **Cara membatalkan (jika ingin revert ke status upstream):**
   ```bash
   git update-index --no-skip-worktree routes/web.php
   git checkout -- routes/web.php
   ```
> **Peringatan Pilihan B:** Jika backend mengubah `routes/web.php` di upstream remote repository, perintah `git pull` akan menolak merger sampai `skip-worktree` dibatalkan terlebih dahulu.
>
> **Aturan Eksekusi:** Frontend Assistant hanya diizinkan menjalankan Pilihan B jika pengguna secara eksplisit memberikan konfirmasi teks: **`SETUJU wiring lokal`**. Tanpa konfirmasi tersebut, `routes/web.php` tidak akan disentuh.

---

## 2. Syarat Konfigurasi Session Driver (.env)

Sesuai aturan isolasi runtime, route preview tidak boleh menulis ke tabel database backend (`sessions`).
* Pastikan file `.env` lokal Anda menggunakan driver session berbasis file:
  ```env
  SESSION_DRIVER=file
  ```
* Jika `.env` Anda memakai `SESSION_DRIVER=database`, `redis`, atau `memcached`, route preview akan menampilkan pesan peringatan dan menolak manipulasi session runtime demi menjaga integritas database backend.

---

## 3. URL Awal dan Struktur Route Preview

Setelah wiring aktif:
* **Halaman Utama (Landing Page):** `http://127.0.0.1:8000/_fe` (Route: `fe.home`)
* **Portal Peserta:** `http://127.0.0.1:8000/_fe/participant/login` (Route: `fe.participant.login`)
* **Portal Sekretariat:** `http://127.0.0.1:8000/_fe/secretariat/login` (Route: `fe.secretariat.login`)
* **Portal Bendahara:** `http://127.0.0.1:8000/_fe/treasurer/login` (Route: `fe.treasurer.login`)
* **Portal Admin:** `http://127.0.0.1:8000/_fe/admin/login` (Route: `fe.admin.login`)
* **Halaman Error Preview:** `http://127.0.0.1:8000/_fe/errors/{code}` (401, 403, 404, 419, 429, 500)
* **Design System / Component Gallery:** `http://127.0.0.1:8000/_fe/_ui` (Route: `fe.dev.ui`)

