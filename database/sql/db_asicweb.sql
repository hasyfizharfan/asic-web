-- =====================================================================
--  Database : db_asicweb
--  Sistem   : Website Seminar ASIC (registrasi, abstrak, pembayaran,
--             dokumen, kehadiran)
--  Sumber   : Rancangan ERD dan hak akses (22 tabel)
--  Engine   : MySQL 8.0.16+ / MariaDB 10.2+ (InnoDB, utf8mb4)
--
--  Revisi 1 (update hasil diskusi tim, 9 Nov 2026) - 23 tabel:
--    [+] registration.jenis_peserta (Presenter Oral / Presenter Poster / Listener)
--    [+] registration_fee.jenis_peserta (ikut di UNIQUE KEY tarif)
--    [+] abstract_submission.status_presentasi, tanggal_presentasi, id_penanda
--    [+] tabel baru signatory (tanda tangan & stempel Ketua / Bendahara)
--    [+] document_template.id_signatory, issued_document.id_signatory
--    [~] invoice.tanggal_jatuh_tempo menjadi NOT NULL (invoice + 7 hari)
--
--  Penanda "-- [TUNGGU]" = bagian yang menunggu jawaban Ibu hari Selasa.
--  Belum diubah; hanya ditandai sebagai komentar.
-- =====================================================================

DROP DATABASE IF EXISTS db_asicweb;
CREATE DATABASE db_asicweb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_asicweb;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- A. AKUN DAN DATA PENGGUNA
-- =====================================================================

-- 1. role
CREATE TABLE role (
  id_role     INT          NOT NULL AUTO_INCREMENT,
  nama_role   VARCHAR(30)  NOT NULL COMMENT 'Peserta, Sekretariat, Bendahara, Admin',
  deskripsi   TEXT         NULL,
  PRIMARY KEY (id_role),
  UNIQUE KEY uq_role_nama (nama_role)
) ENGINE=InnoDB;

-- 2. user_account
CREATE TABLE user_account (
  id_user       INT           NOT NULL AUTO_INCREMENT,
  id_role       INT           NOT NULL,
  email         VARCHAR(100)  NOT NULL,
  password      VARCHAR(255)  NOT NULL COMMENT 'Hash kata sandi (bcrypt/argon2), bukan teks asli',
  status_akun   ENUM('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user),
  UNIQUE KEY uq_user_email (email),
  KEY idx_user_role (id_role),
  CONSTRAINT fk_user_role FOREIGN KEY (id_role)
    REFERENCES role (id_role) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. participant
CREATE TABLE participant (
  id_participant  INT           NOT NULL AUTO_INCREMENT,
  id_user         INT           NOT NULL,
  prefix          VARCHAR(10)   NULL COMMENT 'Mr., Ms., Dr., dst.',
  first_name      VARCHAR(50)   NOT NULL,
  last_name       VARCHAR(50)   NULL,
  passport_no     VARCHAR(30)   NULL,
  job_title       VARCHAR(100)  NULL,
  phone           VARCHAR(20)   NULL,
  institution     VARCHAR(150)  NULL,
  address         TEXT          NULL,
  city            VARCHAR(100)  NULL,
  country         VARCHAR(100)  NULL,
  postcode        VARCHAR(15)   NULL,
  PRIMARY KEY (id_participant),
  UNIQUE KEY uq_participant_user (id_user),          -- relasi 1 : 1 dengan user_account
  CONSTRAINT fk_participant_user FOREIGN KEY (id_user)
    REFERENCES user_account (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================================
-- B. INFORMASI KEGIATAN DAN REGISTRASI
-- =====================================================================

-- 4. event
CREATE TABLE event (
  id_event         INT           NOT NULL AUTO_INCREMENT,
  nama_event       VARCHAR(150)  NOT NULL,
  tema             VARCHAR(255)  NULL,
  deskripsi        TEXT          NULL,
  tanggal_mulai    DATE          NOT NULL,
  tanggal_selesai  DATE          NOT NULL,
  lokasi           VARCHAR(150)  NULL,
  mode_kegiatan    ENUM('Hybrid','Daring','Luring') NOT NULL DEFAULT 'Hybrid',
  status_event     ENUM('Draft','Aktif','Selesai','Arsip') NOT NULL DEFAULT 'Draft',
  PRIMARY KEY (id_event),
  CONSTRAINT chk_event_tanggal CHECK (tanggal_selesai >= tanggal_mulai)
) ENGINE=InnoDB;

-- 5. registration_period
CREATE TABLE registration_period (
  id_period        INT          NOT NULL AUTO_INCREMENT,
  id_event         INT          NOT NULL,
  nama_periode     VARCHAR(50)  NOT NULL COMMENT 'Early Bird, Regular, Late',
  tanggal_mulai    DATE         NOT NULL,
  tanggal_selesai  DATE         NOT NULL,
  status_periode   ENUM('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Tidak Aktif',
  PRIMARY KEY (id_period),
  KEY idx_period_event (id_event),
  CONSTRAINT fk_period_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_period_tanggal CHECK (tanggal_selesai >= tanggal_mulai)
) ENGINE=InnoDB;

-- 6. registration
CREATE TABLE registration (
  id_registration      INT       NOT NULL AUTO_INCREMENT,
  id_participant       INT       NOT NULL,
  id_event             INT       NOT NULL,
  id_period            INT       NOT NULL,
  -- [TUNGGU] Apakah Student/Regular/Committee tetap menjadi kategori tarif,
  --          atau digantikan oleh jenis_peserta? Sementara dipertahankan.
  registration_type    ENUM('Student','Regular','Committee') NOT NULL,
  jenis_peserta        ENUM('Presenter Oral','Presenter Poster','Listener') NOT NULL,
  attendance_mode      ENUM('Daring','Luring') NOT NULL,
  -- Catatan: Listener tidak mengirim abstrak, publication_option diisi 'Tidak' (validasi di aplikasi).
  publication_option   ENUM('Prosiding','Jurnal','Tidak') NOT NULL DEFAULT 'Tidak',
  registration_date    DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  registration_status  ENUM('Terdaftar','Dibatalkan') NOT NULL DEFAULT 'Terdaftar',
  PRIMARY KEY (id_registration),
  KEY idx_reg_participant (id_participant),
  KEY idx_reg_event (id_event),
  KEY idx_reg_period (id_period),
  KEY idx_reg_jenis (jenis_peserta),
  CONSTRAINT fk_reg_participant FOREIGN KEY (id_participant)
    REFERENCES participant (id_participant) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reg_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_reg_period FOREIGN KEY (id_period)
    REFERENCES registration_period (id_period) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. add_on
CREATE TABLE add_on (
  id_add_on    INT            NOT NULL AUTO_INCREMENT,
  id_event     INT            NOT NULL,
  nama_add_on  VARCHAR(100)   NOT NULL,
  deskripsi    TEXT           NULL,
  harga        DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  status       ENUM('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  PRIMARY KEY (id_add_on),
  KEY idx_addon_event (id_event),
  CONSTRAINT fk_addon_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_addon_harga CHECK (harga >= 0)
) ENGINE=InnoDB;

-- 8. registration_add_on (tabel penghubung M:N registration <-> add_on)
CREATE TABLE registration_add_on (
  id_registration  INT            NOT NULL,
  id_add_on        INT            NOT NULL,
  jumlah           INT            NOT NULL DEFAULT 1,
  subtotal         DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id_registration, id_add_on),
  KEY idx_regaddon_addon (id_add_on),
  CONSTRAINT fk_regaddon_reg FOREIGN KEY (id_registration)
    REFERENCES registration (id_registration) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_regaddon_addon FOREIGN KEY (id_add_on)
    REFERENCES add_on (id_add_on) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_regaddon_jumlah CHECK (jumlah > 0),
  CONSTRAINT chk_regaddon_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB;

-- =====================================================================
-- C. ABSTRAK DAN SELEKSI
-- =====================================================================

-- 9. abstract_submission
CREATE TABLE abstract_submission (
  id_abstract       INT           NOT NULL AUTO_INCREMENT,
  id_registration   INT           NOT NULL,
  judul_abstrak     VARCHAR(255)  NOT NULL,
  -- [TUNGGU] metode_presentasi menduplikasi registration.jenis_peserta.
  --          Pilih satu sumber data. Dipertahankan bila reviewer boleh
  --          memindahkan Oral <-> Poster (keputusan akhir ada di sini).
  metode_presentasi ENUM('Oral','Poster') NOT NULL,
  file_abstrak      VARCHAR(255)  NULL,
  tanggal_submit    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status_abstrak    ENUM('Diajukan','Diterima','Ditolak') NOT NULL DEFAULT 'Diajukan',
  -- Syarat sertifikat presenter: status_presentasi = 'Sudah' DAN kehadiran = 'Hadir'.
  -- [TUNGGU] Penanda diusulkan Sekretariat; tunggu penetapan dari tim analisis.
  status_presentasi ENUM('Belum','Sudah') NOT NULL DEFAULT 'Belum',
  tanggal_presentasi DATETIME     NULL,
  id_penanda        INT           NULL COMMENT 'user_account yang menandai sudah presentasi',
  PRIMARY KEY (id_abstract),
  UNIQUE KEY uq_abstract_reg (id_registration),      -- relasi 1 : 1 (asumsi rancangan awal)
  KEY idx_abstract_penanda (id_penanda),
  CONSTRAINT fk_abstract_reg FOREIGN KEY (id_registration)
    REFERENCES registration (id_registration) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_abstract_penanda FOREIGN KEY (id_penanda)
    REFERENCES user_account (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 10. abstract_review
CREATE TABLE abstract_review (
  id_review       INT       NOT NULL AUTO_INCREMENT,
  id_abstract     INT       NOT NULL,
  id_reviewer     INT       NOT NULL COMMENT 'user_account dengan role Sekretariat',
  tanggal_review  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  hasil_review    ENUM('Diterima','Ditolak') NOT NULL,
  catatan_review  TEXT      NULL,
  PRIMARY KEY (id_review),
  KEY idx_review_abstract (id_abstract),
  KEY idx_review_reviewer (id_reviewer),
  CONSTRAINT fk_review_abstract FOREIGN KEY (id_abstract)
    REFERENCES abstract_submission (id_abstract) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_review_reviewer FOREIGN KEY (id_reviewer)
    REFERENCES user_account (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================================
-- D. PEMBAYARAN DAN DOKUMEN
-- =====================================================================

-- 11. registration_fee
CREATE TABLE registration_fee (
  id_fee             INT            NOT NULL AUTO_INCREMENT,
  id_event           INT            NOT NULL,
  id_period          INT            NOT NULL,
  registration_type  ENUM('Student','Regular','Committee') NOT NULL,
  jenis_peserta      ENUM('Presenter Oral','Presenter Poster','Listener') NOT NULL,
  attendance_mode    ENUM('Daring','Luring') NOT NULL,
  amount             DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  discount           DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id_fee),
  UNIQUE KEY uq_fee_kombinasi (id_event, id_period, registration_type, jenis_peserta, attendance_mode),
  KEY idx_fee_period (id_period),
  CONSTRAINT fk_fee_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_fee_period FOREIGN KEY (id_period)
    REFERENCES registration_period (id_period) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_fee_amount CHECK (amount >= 0),
  CONSTRAINT chk_fee_discount CHECK (discount >= 0)
) ENGINE=InnoDB;

-- 12. invoice
CREATE TABLE invoice (
  id_invoice           INT            NOT NULL AUTO_INCREMENT,
  id_registration      INT            NOT NULL,
  id_fee               INT            NOT NULL,
  nomor_invoice        VARCHAR(50)    NOT NULL,
  tanggal_invoice      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Batas bayar 7 hari sejak invoice terbit: diisi backend = DATE(tanggal_invoice) + 7 hari.
  tanggal_jatuh_tempo  DATE           NOT NULL,
  total_tagihan        DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  payment_qr_info      VARCHAR(255)   NULL,
  -- [TUNGGU] Status bila lewat 1 minggu belum bayar (mis. tambah 'Kedaluwarsa').
  -- [TUNGGU] Bukti bayar ditolak: apakah kembali ke 'Menunggu Pembayaran' agar bisa diunggah ulang?
  status_pembayaran    ENUM('Menunggu Pembayaran','Menunggu Verifikasi','Terverifikasi','Ditolak')
                       NOT NULL DEFAULT 'Menunggu Pembayaran',
  PRIMARY KEY (id_invoice),
  UNIQUE KEY uq_invoice_nomor (nomor_invoice),
  UNIQUE KEY uq_invoice_reg (id_registration),       -- relasi 1 : 1 (asumsi rancangan awal)
  KEY idx_invoice_fee (id_fee),
  CONSTRAINT fk_invoice_reg FOREIGN KEY (id_registration)
    REFERENCES registration (id_registration) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_invoice_fee FOREIGN KEY (id_fee)
    REFERENCES registration_fee (id_fee) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_invoice_total CHECK (total_tagihan >= 0),
  CONSTRAINT chk_invoice_jatuh_tempo CHECK (tanggal_jatuh_tempo >= DATE(tanggal_invoice))
) ENGINE=InnoDB;

-- 13. payment_proof
CREATE TABLE payment_proof (
  id_payment_proof  INT            NOT NULL AUTO_INCREMENT,
  id_invoice        INT            NOT NULL,
  file_bukti        VARCHAR(255)   NOT NULL,
  tanggal_upload    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  nominal_transfer  DECIMAL(12,2)  NULL,
  catatan_peserta   TEXT           NULL,
  PRIMARY KEY (id_payment_proof),
  KEY idx_proof_invoice (id_invoice),
  CONSTRAINT fk_proof_invoice FOREIGN KEY (id_invoice)
    REFERENCES invoice (id_invoice) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_proof_nominal CHECK (nominal_transfer IS NULL OR nominal_transfer >= 0)
) ENGINE=InnoDB;

-- 14. payment_verification
CREATE TABLE payment_verification (
  id_verification     INT       NOT NULL AUTO_INCREMENT,
  id_payment_proof    INT       NOT NULL,
  id_treasurer        INT       NOT NULL COMMENT 'user_account dengan role Bendahara',
  tanggal_verifikasi  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  hasil_verifikasi    ENUM('Valid','Tidak Valid') NOT NULL,
  alasan_penolakan    TEXT      NULL,
  PRIMARY KEY (id_verification),
  KEY idx_verif_proof (id_payment_proof),
  KEY idx_verif_treasurer (id_treasurer),
  CONSTRAINT fk_verif_proof FOREIGN KEY (id_payment_proof)
    REFERENCES payment_proof (id_payment_proof) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_verif_treasurer FOREIGN KEY (id_treasurer)
    REFERENCES user_account (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 14a. signatory (TABEL BARU: tanda tangan & stempel panitia)
--      LOA & surat penolakan -> Ketua; invoice & receipt -> Bendahara.
--      Kolom file hanya menyimpan path. Simpan file di luar folder publik dan
--      sajikan lewat endpoint yang memeriksa login dan role.
CREATE TABLE signatory (
  id_signatory       INT           NOT NULL AUTO_INCREMENT,
  id_event           INT           NOT NULL,
  jabatan            ENUM('Ketua','Bendahara') NOT NULL,
  nama               VARCHAR(100)  NOT NULL,
  file_tanda_tangan  VARCHAR(255)  NULL,
  file_stempel       VARCHAR(255)  NULL,
  PRIMARY KEY (id_signatory),
  KEY idx_signatory_event (id_event),
  CONSTRAINT fk_signatory_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 15. document_template
CREATE TABLE document_template (
  id_template      INT           NOT NULL AUTO_INCREMENT,
  id_event         INT           NOT NULL,
  id_signatory     INT           NULL COMMENT 'Penandatangan dokumen; NULL bila tidak ada (mis. sertifikat belum ditetapkan)',
  nama_template    VARCHAR(100)  NOT NULL,
  jenis_dokumen    ENUM('Abstrak','LOA','Penolakan','Invoice','Receipt','Sertifikat') NOT NULL,
  file_template    VARCHAR(255)  NOT NULL,
  tanggal_upload   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status_template  ENUM('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  PRIMARY KEY (id_template),
  KEY idx_template_event (id_event),
  KEY idx_template_signatory (id_signatory),
  CONSTRAINT fk_template_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_template_signatory FOREIGN KEY (id_signatory)
    REFERENCES signatory (id_signatory) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 16. issued_document
CREATE TABLE issued_document (
  id_document      INT           NOT NULL AUTO_INCREMENT,
  id_registration  INT           NOT NULL,
  id_template      INT           NOT NULL,
  id_signatory     INT           NULL COMMENT 'Penandatangan yang dipakai saat dokumen diterbitkan (arsip)',
  -- [TUNGGU] Apakah Listener mendapat LOA? Bila ya, tidak ada perubahan struktur;
  --          dokumen LOA Listener cukup diterbitkan lewat tabel ini.
  jenis_dokumen    ENUM('Abstrak','LOA','Penolakan','Invoice','Receipt','Sertifikat') NOT NULL,
  nomor_dokumen    VARCHAR(50)   NULL,
  file_document    VARCHAR(255)  NOT NULL,
  tanggal_terbit   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_document),
  UNIQUE KEY uq_document_nomor (nomor_dokumen),
  KEY idx_doc_reg (id_registration),
  KEY idx_doc_template (id_template),
  KEY idx_doc_signatory (id_signatory),
  CONSTRAINT fk_doc_reg FOREIGN KEY (id_registration)
    REFERENCES registration (id_registration) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_doc_template FOREIGN KEY (id_template)
    REFERENCES document_template (id_template) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_doc_signatory FOREIGN KEY (id_signatory)
    REFERENCES signatory (id_signatory) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================================
-- E. KEHADIRAN DAN BERKAS PESERTA
-- =====================================================================

-- 17. attendance
CREATE TABLE attendance (
  id_attendance     INT           NOT NULL AUTO_INCREMENT,
  id_registration   INT           NOT NULL,
  -- [TUNGGU] Absensi daring: bila peserta daring tidak memakai QR, qr_code dibuat NULL.
  qr_code           VARCHAR(255)  NOT NULL,
  tanggal_generate  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tanggal_check_in  DATETIME      NULL,
  -- [TUNGGU] Bila panitia yang memindai QR dan perlu dicatat: tambah id_scanner (FK ke user_account).
  status_kehadiran  ENUM('Tidak Hadir','Hadir') NOT NULL DEFAULT 'Tidak Hadir',
  PRIMARY KEY (id_attendance),
  UNIQUE KEY uq_attendance_reg (id_registration),    -- relasi 1 : 1 (asumsi rancangan awal)
  UNIQUE KEY uq_attendance_qr (qr_code),
  CONSTRAINT fk_attendance_reg FOREIGN KEY (id_registration)
    REFERENCES registration (id_registration) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 18. participant_file
CREATE TABLE participant_file (
  id_file          INT           NOT NULL AUTO_INCREMENT,
  id_registration  INT           NOT NULL,
  jenis_file       ENUM('Presentasi','Artikel') NOT NULL,
  nama_file        VARCHAR(255)  NOT NULL,
  file_path        VARCHAR(255)  NOT NULL,
  tanggal_upload   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status_file      ENUM('Diupload','Valid','Tidak Valid') NOT NULL DEFAULT 'Diupload',
  PRIMARY KEY (id_file),
  KEY idx_pfile_reg (id_registration),
  CONSTRAINT fk_pfile_reg FOREIGN KEY (id_registration)
    REFERENCES registration (id_registration) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================================
-- F. INFORMASI DAN KOMUNIKASI
-- =====================================================================

-- 19. speaker
CREATE TABLE speaker (
  id_speaker    INT           NOT NULL AUTO_INCREMENT,
  id_event      INT           NOT NULL,
  nama_speaker  VARCHAR(100)  NOT NULL,
  institusi     VARCHAR(150)  NULL,
  negara        VARCHAR(100)  NULL,
  foto          VARCHAR(255)  NULL,
  deskripsi     TEXT          NULL,
  PRIMARY KEY (id_speaker),
  KEY idx_speaker_event (id_event),
  CONSTRAINT fk_speaker_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- 20. event_schedule
CREATE TABLE event_schedule (
  id_schedule    INT           NOT NULL AUTO_INCREMENT,
  id_event       INT           NOT NULL,
  nama_kegiatan  VARCHAR(150)  NOT NULL,
  tanggal        DATE          NOT NULL,
  waktu_mulai    TIME          NOT NULL,
  waktu_selesai  TIME          NULL,
  lokasi         VARCHAR(150)  NULL,
  deskripsi      TEXT          NULL,
  PRIMARY KEY (id_schedule),
  KEY idx_schedule_event (id_event),
  CONSTRAINT fk_schedule_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- 21. announcement
CREATE TABLE announcement (
  id_announcement    INT           NOT NULL AUTO_INCREMENT,
  id_event           INT           NOT NULL,
  id_author          INT           NOT NULL COMMENT 'user_account panitia pembuat pengumuman',
  judul              VARCHAR(150)  NOT NULL,
  isi_pengumuman     TEXT          NOT NULL,
  tanggal_publikasi  DATETIME      NULL,
  status             ENUM('Draft','Dipublikasikan') NOT NULL DEFAULT 'Draft',
  PRIMARY KEY (id_announcement),
  KEY idx_ann_event (id_event),
  KEY idx_ann_author (id_author),
  CONSTRAINT fk_ann_event FOREIGN KEY (id_event)
    REFERENCES event (id_event) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_ann_author FOREIGN KEY (id_author)
    REFERENCES user_account (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 22. invoice_detail
CREATE TABLE invoice_detail (
  id_invoice_detail  INT            NOT NULL AUTO_INCREMENT,
  id_invoice         INT            NOT NULL,
  -- [TUNGGU] Aturan pengembalian: publikasi bisa dikembalikan, seminar tidak.
  --          Bila perlu dihitung otomatis, jadikan jenis_biaya ENUM('Seminar','Publikasi','Add-on').
  jenis_biaya        VARCHAR(100)   NOT NULL,
  deskripsi          VARCHAR(255)   NULL,
  jumlah             INT            NOT NULL DEFAULT 1,
  harga_satuan       DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  subtotal           DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id_invoice_detail),
  KEY idx_invdetail_invoice (id_invoice),
  CONSTRAINT fk_invdetail_invoice FOREIGN KEY (id_invoice)
    REFERENCES invoice (id_invoice) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_invdetail_jumlah CHECK (jumlah > 0),
  CONSTRAINT chk_invdetail_harga CHECK (harga_satuan >= 0 AND subtotal >= 0)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- [TUNGGU] Pengembalian uang: bila Bendahara harus mencatat refund di sistem,
--          buat tabel refund (id_refund, id_invoice, nominal, alasan, status,
--          id_treasurer, tanggal_refund, file_bukti). Bila hanya aturan yang
--          ditampilkan di UI, tabel ini tidak diperlukan.

-- =====================================================================
-- DATA AWAL: empat peran sistem (sesuai pembagian hak akses)
-- =====================================================================
INSERT INTO role (nama_role, deskripsi) VALUES
('Peserta',     'Mengelola profil, pendaftaran, abstrak, bukti pembayaran, dan berkas miliknya sendiri; mengunduh invoice, receipt, LOA, dan sertifikat.'),
('Sekretariat', 'Mengelola informasi kegiatan, periode dan data pendaftaran, layanan tambahan, seleksi abstrak, template dokumen, kehadiran, berkas peserta, pengumuman, serta rekapitulasi dan ekspor data.'),
('Bendahara',   'Mengelola tarif pendaftaran, memeriksa bukti pembayaran, menetapkan hasil verifikasi valid/tidak valid, dan memastikan receipt terbit setelah pembayaran valid.'),
('Admin',       'Mengelola akun, hak akses, dan konfigurasi teknis sistem; memantau data lintas modul.');