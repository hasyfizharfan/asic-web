# Catatan Perubahan File (CHANGES.md)

Dokumen ini mencatat seluruh file baru milik frontend serta `git diff` lengkap dari file bersama (*shared files*) yang disentuh.

---

## 1. File Bersama yang Disentuh (Hanya Penambahan)

Sesuai aturan kerja, file bersama hanya disentuh dengan penambahan (*append-only*), tanpa menghapus atau mengubah konfigurasi backend/starter kit yang sudah ada.

### A. `package.json`
Penambahan dependensi Alpine.js untuk interaktivitas komponen UI frontend:
```diff
diff --git a/package.json b/package.json
index 7686b29..21a2ddb 100644
--- a/package.json
+++ b/package.json
@@ -13,5 +13,8 @@
         "laravel-vite-plugin": "^2.0.0",
         "tailwindcss": "^4.0.0",
         "vite": "^7.0.7"
+    },
+    "dependencies": {
+        "alpinejs": "^3.17.5"
     }
 }
```

### B. `vite.config.js`
Penambahan dua file entrypoint mandiri frontend (`resources/css/asic.css` dan `resources/js/asic.js`) pada daftar input Laravel Vite Plugin tanpa mengubah input `app.css` dan `app.js` milik BE:
```diff
diff --git a/vite.config.js b/vite.config.js
index f35b4e7..17360c6 100644
--- a/vite.config.js
+++ b/vite.config.js
@@ -5,7 +5,12 @@ import tailwindcss from '@tailwindcss/vite';
 export default defineConfig({
     plugins: [
         laravel({
-            input: ['resources/css/app.css', 'resources/js/app.js'],
+            input: [
+                'resources/css/app.css',
+                'resources/js/app.js',
+                'resources/css/asic.css',
+                'resources/js/asic.js',
+            ],
             refresh: true,
         }),
         tailwindcss(),
```

---

## 2. File Baru Milik Frontend

### A. Entrypoint Styling & Script
* `resources/css/asic.css` — Entrypoint Tailwind v4 khusus FE dengan theme tokens Material 3 ASIC, font Calibri/Carlito, dan animasi.
* `resources/js/asic.js` — Entrypoint JS khusus FE dengan inisialisasi Alpine.js yang aman (*no double start*) dan Alpine store untuk toast.

### B. Aset Statis Ber-namespace
* `public/images/asic/avatar.svg` — Placeholder avatar profil pengguna.
* `public/images/asic/logo.svg` — Logo resmi ASIC 2027.
* `public/images/asic/hero-bg.jpg` — Gambar latar visual pendukung.
* `public/images/asic/minangkabau-hills.jpg` — Gambar panorama alam Padang / Sumbar.
* `public/images/asic/qr-sample.svg` — Placeholder kode QR untuk check-in dan pembayaran.
* `resources/private/templates/iop-conference-template.docx` — Template manuskrip IOP untuk author (disajikan via route preview).

### C. File Bahasa (Namespace Awalan `asic-`)
* `lang/en/asic-admin.php` & `lang/id/asic-admin.php`
* `lang/en/asic-auth.php` & `lang/id/asic-auth.php`
* `lang/en/asic-common.php` & `lang/id/asic-common.php`
* `lang/en/asic-landing.php` & `lang/id/asic-landing.php`
* `lang/en/asic-nav.php` & `lang/id/asic-nav.php`
* `lang/en/asic-participant.php` & `lang/id/asic-participant.php`
* `lang/en/asic-secretariat.php` & `lang/id/asic-secretariat.php`
* `lang/en/asic-status.php` & `lang/id/asic-status.php`
* `lang/en/asic-treasurer.php` & `lang/id/asic-treasurer.php`

### D. File Template View & Komponen
* `resources/views/layouts/public.blade.php`, `layouts/portal.blade.php`, `layouts/auth.blade.php`
* `resources/views/public/landing.blade.php`, `resources/views/public/partials/hero-art.blade.php`
* `resources/views/participant/*` (Dashboard, Abstract, Documents, Payment, Full Paper, QR, Certificate, dll.)
* `resources/views/secretariat/*` (Abstracts, Attendance, Announcements, Content, Recap, Reregistration)
* `resources/views/treasurer/*` (Tariffs, Payments, Receipts Preview, Refunds)
* `resources/views/admin/*` (Overview, Users, Permissions, Templates, Settings, Archive)
* `resources/views/auth/*` (Login forms untuk 4 role)
* `resources/views/components/layout/*` & `resources/views/components/ui/*` (20+ komponen UI reusable)
* `resources/views/asic/errors/{401, 403, 404, 419, 429, 500}.blade.php` & `error-layout.blade.php`

### E. File Route & Handoff
* `routes/fe-preview.php` — Route preview terisolasi dengan data fixture.
* `docs/frontend-handoff/*` — Skrip audit scope, dokumentasi setup, contract, changes, dan requests for BE.
* `docs/.gitignore` — Pengabaian folder prototype pages lokal.

