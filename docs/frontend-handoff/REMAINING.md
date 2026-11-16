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
| Assignment reviewer | Belum tersedia. |
| Mata uang USD pada tarif bendahara | Skema `registration_fee` hanya memiliki kolom `amount` dan `discount` tanpa kode mata uang. UI saat ini menampilkan nominal sebagai IDR; dukungan USD memerlukan perubahan skema dan aturan invoice. |
| Landing sections selain event/speaker/schedule/tarif aktif | Masih memakai konten presentasi statis. |
| Pemindaian kamera QR dan pembentukan gambar QR | Check-in menerima token melalui input manual/pemindai keyboard; kamera dan gambar QR belum disediakan. |

Untuk fitur baru, tambahkan kontrak backend (Form Request, middleware `permission:`, API Resource, OpenAPI, dan Feature test) sebelum menghubungkan kontrol UI.
# Fitur portal peserta yang belum tersedia

- Pengajuan dan pemrosesan refund belum memiliki endpoint backend. Halaman refund menampilkan status **Belum tersedia** dan aksi pengajuan dinonaktifkan.
- Penerbitan/pengunduhan sertifikat belum memiliki endpoint backend. Halaman sertifikat menampilkan status **Belum tersedia** dan tombol unduh dinonaktifkan.
- Layanan notifikasi peserta belum memiliki endpoint backend. Halaman notifikasi menampilkan status **Belum tersedia** dan aksinya dinonaktifkan.

## Bendahara

- Refund bendahara belum memiliki endpoint backend. Halaman refund menampilkan **Belum tersedia** dengan aksi dinonaktifkan.
- Skema tarif menyimpan `amount` dan `discount` tanpa kode mata uang. Portal bendahara menampilkan nominal tersebut sebagai IDR; USD belum dapat dibedakan atau dihitung dengan aman.
