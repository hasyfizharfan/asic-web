# Fitur portal yang belum tersedia atau belum terhubung

Fitur berikut belum boleh menampilkan keberhasilan atau mengubah state seolah-olah operasi berhasil.

| Fitur | Status |
| --- | --- |
| Override check-in sekretariat | Belum tersedia; alur otorisasi supervisor belum dirancang. |
| Pencarian registrasi ulang dengan `reg_no` / nomor badge | Belum tersedia; check-in QR dan daftar peserta sesi tersedia. |
| Cetak badge dan issue kit | Belum tersedia. |
| Draft review reviewer | Belum tersedia; pengiriman review final tersedia. |
| Website settings | Belum tersedia. |
| Refund | Belum tersedia. |
| Notifikasi | Belum tersedia. |
| Sertifikat | Belum tersedia. |
| Archive | Belum tersedia. |
| CRUD template dokumen | Belum tersedia. |
| Profil peserta dan ganti password | Endpoint belum tersedia; UI menyatakan fitur belum tersedia. |
| Dashboard peserta agregat dan assignment reviewer | Endpoint belum tersedia. |
| Secretariat website content manager (events/speakers/schedules) | Endpoint CRUD tersedia, tetapi editor CMS belum dihubungkan. Konten yang tampil pada landing event/speaker/schedule sudah mengambil data API. |
| Landing sections selain event/speaker/schedule/tarif aktif | Masih memakai konten presentasi statis. |
| Pemindaian kamera QR dan pembentukan gambar QR | Check-in menerima token melalui input manual/pemindai keyboard; kamera dan gambar QR belum disediakan. |

Untuk fitur baru, tambahkan kontrak backend (Form Request, middleware `permission:`, API Resource, OpenAPI, dan Feature test) sebelum menghubungkan kontrol UI.
