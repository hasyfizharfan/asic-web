# Fitur portal yang belum tersedia atau belum terhubung

Fitur berikut belum boleh menampilkan keberhasilan atau mengubah state seolah-olah operasi berhasil.

## Sekretariat

- Override check-in, cetak badge, dan penerbitan kit tetap dinonaktifkan.
- Nomor badge/reg_no belum ada; pencarian registrasi memakai primary key `id_registration`, nama, atau email.
- Target audiens pengumuman dan pengiriman email/WhatsApp belum didukung backend. Jadwal serta status Draft/Dipublikasikan tersimpan; kanal komunikasi dikelola sebagai entitas terpisah.

| Fitur | Status |
| --- | --- |
| Override check-in sekretariat | Belum tersedia; alur otorisasi supervisor belum dirancang. |
| Pencarian registrasi ulang dengan nomor badge terpisah | Belum tersedia; pencarian memakai ID primary key registrasi, nama, atau email melalui `GET /api/registrations/search`. Registrasi ulang luring hari pertama (KF-12) tersedia lewat pindai QR atau hasil pencarian (`POST /api/events/{id_event}/reregistration[/scan]`). |
| Cetak badge dan issue kit | Belum tersedia. |
| Draft review reviewer | Belum tersedia; pengiriman review final tersedia. |
| Website settings | Belum tersedia. |
| Refund | Belum tersedia. |
| Notifikasi | Belum tersedia. |
| Sertifikat | Belum tersedia. |
| Archive | Belum tersedia. |
| CRUD template dokumen | Belum tersedia. |
| Assignment reviewer | Belum tersedia; antrean reviewer menampilkan abstrak sesuai filter tanpa penugasan individual. Kontrol penugasan Sekretariat dinonaktifkan. |
| Status Revisi | Belum tersedia; keputusan final hanya Diterima atau Ditolak. |
| Landing sections selain event/speaker/schedule/tarif aktif | Masih memakai konten presentasi statis. |
| Pemindaian kamera QR dan pembentukan gambar QR | Check-in menerima token melalui input manual/pemindai keyboard; kamera dan gambar QR belum disediakan. |
| Target audiens pengumuman dan pengiriman kanal | Belum tersedia di skema/API; pengumuman memiliki event, jadwal, dan status Draft/Dipublikasikan. Kanal komunikasi menyimpan jenis, URL, dan status tanpa relasi target pengumuman. |

Untuk fitur baru, tambahkan kontrak backend (Form Request, middleware `permission:`, API Resource, OpenAPI, dan Feature test) sebelum menghubungkan kontrol UI.
# Fitur portal peserta yang belum tersedia

- Pengajuan dan pemrosesan refund belum memiliki endpoint backend. Halaman refund menampilkan status **Belum tersedia** dan aksi pengajuan dinonaktifkan.
- Penerbitan/pengunduhan sertifikat belum memiliki endpoint backend. Halaman sertifikat menampilkan status **Belum tersedia** dan tombol unduh dinonaktifkan.
- Layanan notifikasi peserta belum memiliki endpoint backend. Halaman notifikasi menampilkan status **Belum tersedia** dan aksinya dinonaktifkan.

## Bendahara

- Refund bendahara belum memiliki endpoint backend. Halaman refund menampilkan **Belum tersedia** dengan aksi dinonaktifkan.

## Admin

- Arsip belum memiliki API atau skema; halaman arsip dan aksinya tetap nonaktif dengan status **Belum tersedia**.
- CRUD template dokumen belum tersedia. Kedua kelompok ditampilkan sebagai informasi: LOA, Penolakan, Invoice, Receipt, Sertifikat; serta berkas unduhan Abstrak, Presentasi, Artikel. Aksinya tetap nonaktif.
- Pengaturan website global belum memiliki API atau model settings. Kontrol perubahan tetap nonaktif dan menampilkan **Belum tersedia**.
- Tanda tangan digital tersedia melalui `/api/digital-signatures` dan dibatasi capability `manage_digital_signatures`; unggah privat hanya muncul untuk level `full_access`.
