-- =====================================================================
--  Database : db_asicweb
--  Sistem   : Website Seminar ASIC (registrasi, abstrak, pembayaran,
--             dokumen, kehadiran)
--  Sumber   : Rancangan ERD dan hak akses (22 tabel)
--  Engine   : MySQL 8.0.16+ / MariaDB 10.2+ (InnoDB, utf8mb4)
--
--
-- =====================================================================

DROP DATABASE IF EXISTS db_asicweb;
CREATE DATABASE db_asicweb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_asicweb;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `role` (
  `id_role` int PRIMARY KEY AUTO_INCREMENT,
  `nama_role` varchar(30),
  `deskripsi` text
);

CREATE TABLE `user_account` (
  `id_user` int PRIMARY KEY AUTO_INCREMENT,
  `id_role` int NOT NULL,
  `email` varchar(100),
  `password` varchar(255),
  `status_akun` ENUM ('Aktif', 'Nonaktif'),
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `participant` (
  `id_participant` int PRIMARY KEY AUTO_INCREMENT,
  `id_user` int UNIQUE NOT NULL,
  `prefix` varchar(10),
  `first_name` varchar(50),
  `last_name` varchar(50),
  `passport_no` varchar(30),
  `job_title` varchar(100),
  `phone` varchar(20),
  `institution` varchar(150),
  `address` text,
  `city` varchar(100),
  `country` varchar(100),
  `postcode` varchar(15)
);

CREATE TABLE `event` (
  `id_event` int PRIMARY KEY AUTO_INCREMENT,
  `nama_event` varchar(150),
  `tema` varchar(255),
  `deskripsi` text,
  `tanggal_mulai` date,
  `tanggal_selesai` date,
  `lokasi` varchar(150),
  `mode_kegiatan` ENUM ('Hybrid', 'Daring', 'Luring'),
  `status_event` ENUM ('Draft', 'Aktif', 'Selesai', 'Arsip')
);

CREATE TABLE `registration_period` (
  `id_period` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `nama_periode` varchar(50),
  `tanggal_mulai` date,
  `tanggal_selesai` date,
  `status_periode` ENUM ('Aktif', 'Tidak Aktif')
);

CREATE TABLE `registration` (
  `id_registration` int PRIMARY KEY AUTO_INCREMENT,
  `id_participant` int NOT NULL,
  `id_event` int NOT NULL,
  `id_period` int NOT NULL,
  `registration_type` ENUM ('Student', 'Regular', 'Committee'),
  `participation_type` ENUM ('Oral Presenter', 'Poster Presenter', 'Listener'),
  `attendance_mode` ENUM ('Daring', 'Luring'),
  `publication_option` ENUM ('Prosiding', 'Jurnal', 'Tidak'),
  `registration_date` datetime,
  `registration_status` ENUM ('Terdaftar', 'Dibatalkan')
);

CREATE TABLE `add_on` (
  `id_add_on` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `nama_add_on` varchar(100),
  `deskripsi` text,
  `harga` decimal(12,2),
  `status` ENUM ('Aktif', 'Tidak Aktif')
);

CREATE TABLE `registration_add_on` (
  `id_registration` int NOT NULL,
  `id_add_on` int NOT NULL,
  `jumlah` int,
  `subtotal` decimal(12,2),
  PRIMARY KEY (`id_registration`, `id_add_on`)
);

CREATE TABLE `event_timeline` (
  `id_timeline` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `nama_tahapan` varchar(100),
  `tanggal_mulai` date,
  `tanggal_selesai` date,
  `deskripsi` text
);

CREATE TABLE `abstract_submission` (
  `id_abstract` int PRIMARY KEY AUTO_INCREMENT,
  `id_registration` int UNIQUE NOT NULL,
  `judul_abstrak` varchar(255),
  `metode_presentasi` ENUM ('Oral', 'Poster'),
  `file_abstrak` varchar(255),
  `tanggal_submit` datetime,
  `status_abstrak` ENUM ('Diajukan', 'Diterima', 'Ditolak')
);

CREATE TABLE `abstract_review` (
  `id_review` int PRIMARY KEY AUTO_INCREMENT,
  `id_abstract` int NOT NULL,
  `id_reviewer` int NOT NULL,
  `tanggal_review` datetime,
  `hasil_review` ENUM ('Diterima', 'Ditolak'),
  `catatan_review` text
);

CREATE TABLE `registration_fee` (
  `id_fee` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `id_period` int NOT NULL,
  `registration_type` ENUM ('Student', 'Regular', 'Committee'),
  `attendance_mode` ENUM ('Daring', 'Luring'),
  `amount` decimal(12,2),
  `discount` decimal(12,2)
);

CREATE TABLE `invoice` (
  `id_invoice` int PRIMARY KEY AUTO_INCREMENT,
  `id_registration` int UNIQUE NOT NULL,
  `id_fee` int NOT NULL,
  `nomor_invoice` varchar(50),
  `tanggal_invoice` datetime,
  `tanggal_jatuh_tempo` date,
  `total_tagihan` decimal(12,2),
  `payment_qr_info` varchar(255),
  `status_pembayaran` ENUM ('Menunggu Pembayaran', 'Menunggu Verifikasi', 'Terverifikasi', 'Ditolak')
);

CREATE TABLE `invoice_detail` (
  `id_invoice_detail` int PRIMARY KEY AUTO_INCREMENT,
  `id_invoice` int NOT NULL,
  `jenis_biaya` varchar(100),
  `deskripsi` varchar(255),
  `jumlah` int,
  `harga_satuan` decimal(12,2),
  `subtotal` decimal(12,2)
);

CREATE TABLE `payment_proof` (
  `id_payment_proof` int PRIMARY KEY AUTO_INCREMENT,
  `id_invoice` int NOT NULL,
  `file_bukti` varchar(255),
  `tanggal_upload` datetime,
  `nominal_transfer` decimal(12,2),
  `catatan_peserta` text
);

CREATE TABLE `payment_verification` (
  `id_verification` int PRIMARY KEY AUTO_INCREMENT,
  `id_payment_proof` int NOT NULL,
  `id_treasurer` int NOT NULL,
  `tanggal_verifikasi` datetime,
  `hasil_verifikasi` ENUM ('Valid', 'Tidak Valid'),
  `alasan_penolakan` text
);

CREATE TABLE `document_template` (
  `id_template` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `nama_template` varchar(100),
  `jenis_dokumen` ENUM ('Abstrak', 'LOA', 'Penolakan', 'Invoice', 'Receipt', 'Sertifikat'),
  `file_template` varchar(255),
  `tanggal_upload` datetime,
  `status_template` ENUM ('Aktif', 'Tidak Aktif')
);

CREATE TABLE `issued_document` (
  `id_document` int PRIMARY KEY AUTO_INCREMENT,
  `id_registration` int NOT NULL,
  `id_template` int NOT NULL,
  `jenis_dokumen` ENUM ('Abstrak', 'LOA', 'Penolakan', 'Invoice', 'Receipt', 'Sertifikat'),
  `nomor_dokumen` varchar(50),
  `file_document` varchar(255),
  `tanggal_terbit` datetime
);

CREATE TABLE `attendance` (
  `id_attendance` int PRIMARY KEY AUTO_INCREMENT,
  `id_registration` int UNIQUE NOT NULL,
  `qr_code` varchar(255),
  `tanggal_generate` datetime,
  `tanggal_check_in` datetime,
  `status_kehadiran` ENUM ('Tidak Hadir', 'Hadir')
);

CREATE TABLE `participant_file` (
  `id_file` int PRIMARY KEY AUTO_INCREMENT,
  `id_registration` int NOT NULL,
  `jenis_file` ENUM ('Presentasi', 'Artikel'),
  `nama_file` varchar(255),
  `file_path` varchar(255),
  `tanggal_upload` datetime,
  `status_file` ENUM ('Diupload', 'Valid', 'Tidak Valid')
);

CREATE TABLE `speaker` (
  `id_speaker` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `nama_speaker` varchar(100),
  `institusi` varchar(150),
  `negara` varchar(100),
  `foto` varchar(255),
  `deskripsi` text
);

CREATE TABLE `event_schedule` (
  `id_schedule` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `nama_kegiatan` varchar(150),
  `tanggal` date,
  `waktu_mulai` time,
  `waktu_selesai` time,
  `lokasi` varchar(150),
  `deskripsi` text
);

CREATE TABLE `announcement` (
  `id_announcement` int PRIMARY KEY AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `id_author` int NOT NULL,
  `judul` varchar(150),
  `isi_pengumuman` text,
  `tanggal_publikasi` datetime,
  `status` ENUM ('Draft', 'Dipublikasikan')
);

ALTER TABLE `role` COMMENT = 'Peserta, Sekretariat, Bendahara, Tim Abstrak, Admin';

ALTER TABLE `registration_period` COMMENT = 'Contoh nama periode: Early Bird, Regular, Late';

ALTER TABLE `event_timeline` COMMENT = 'Contoh: Pengumpulan Abstrak, Pengumuman Seleksi, Pembayaran, Pengumpulan Artikel, Pengumpulan Presentasi';

ALTER TABLE `abstract_review` COMMENT = 'Reviewer merupakan user dengan role Tim Abstrak';

ALTER TABLE `registration_fee` COMMENT = 'Tarif dibedakan berdasarkan periode, kategori peserta, dan mode kehadiran';

ALTER TABLE `invoice` COMMENT = 'Tanggal jatuh tempo merupakan batas pembayaran individual peserta';

ALTER TABLE `payment_verification` COMMENT = 'Pemeriksaan pembayaran dilakukan oleh user dengan role Bendahara';

ALTER TABLE `user_account` ADD FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);

ALTER TABLE `participant` ADD FOREIGN KEY (`id_user`) REFERENCES `user_account` (`id_user`);

ALTER TABLE `registration_period` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `registration` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `registration` ADD FOREIGN KEY (`id_participant`) REFERENCES `participant` (`id_participant`);

ALTER TABLE `registration` ADD FOREIGN KEY (`id_period`) REFERENCES `registration_period` (`id_period`);

ALTER TABLE `add_on` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `registration_add_on` ADD FOREIGN KEY (`id_registration`) REFERENCES `registration` (`id_registration`);

ALTER TABLE `registration_add_on` ADD FOREIGN KEY (`id_add_on`) REFERENCES `add_on` (`id_add_on`);

ALTER TABLE `event_timeline` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `abstract_submission` ADD FOREIGN KEY (`id_registration`) REFERENCES `registration` (`id_registration`);

ALTER TABLE `abstract_review` ADD FOREIGN KEY (`id_abstract`) REFERENCES `abstract_submission` (`id_abstract`);

ALTER TABLE `abstract_review` ADD FOREIGN KEY (`id_reviewer`) REFERENCES `user_account` (`id_user`);

ALTER TABLE `registration_fee` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `registration_fee` ADD FOREIGN KEY (`id_period`) REFERENCES `registration_period` (`id_period`);

ALTER TABLE `invoice` ADD FOREIGN KEY (`id_fee`) REFERENCES `registration_fee` (`id_fee`);

ALTER TABLE `invoice` ADD FOREIGN KEY (`id_registration`) REFERENCES `registration` (`id_registration`);

ALTER TABLE `invoice_detail` ADD FOREIGN KEY (`id_invoice`) REFERENCES `invoice` (`id_invoice`);

ALTER TABLE `payment_proof` ADD FOREIGN KEY (`id_invoice`) REFERENCES `invoice` (`id_invoice`);

ALTER TABLE `payment_verification` ADD FOREIGN KEY (`id_payment_proof`) REFERENCES `payment_proof` (`id_payment_proof`);

ALTER TABLE `payment_verification` ADD FOREIGN KEY (`id_treasurer`) REFERENCES `user_account` (`id_user`);

ALTER TABLE `document_template` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `issued_document` ADD FOREIGN KEY (`id_registration`) REFERENCES `registration` (`id_registration`);

ALTER TABLE `issued_document` ADD FOREIGN KEY (`id_template`) REFERENCES `document_template` (`id_template`);

ALTER TABLE `attendance` ADD FOREIGN KEY (`id_registration`) REFERENCES `registration` (`id_registration`);

ALTER TABLE `participant_file` ADD FOREIGN KEY (`id_registration`) REFERENCES `registration` (`id_registration`);

ALTER TABLE `speaker` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `event_schedule` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `announcement` ADD FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

ALTER TABLE `announcement` ADD FOREIGN KEY (`id_author`) REFERENCES `user_account` (`id_user`);
