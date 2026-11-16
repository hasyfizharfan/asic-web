<?php

return [
    'dashboard_title' => 'Dasbor Peserta',
    'welcome_back' => 'Selamat datang kembali, :name',
    'role_oral' => 'Pemakalah Oral',
    'role_poster' => 'Pemakalah Poster',
    'role_listener' => 'Peserta Pendengar / Non-Pemakalah',
    'reg_id' => 'ID Pendaftaran',
    'institution' => 'Institusi / Universitas',
    'presentation_mode' => 'Moda Presentasi',
    'onsite' => 'Luring di Lokasi (Padang)',
    'online' => 'Daring (Virtual)',

    // Action cards
    'abstract_card_title' => 'Pengajuan Abstrak',
    'payment_card_title' => 'Pembayaran Registrasi',
    'attendance_card_title' => 'Presensi Kehadiran',
    'certificate_card_title' => 'Sertifikat Resmi',
    'full_paper_card_title' => 'Makalah Lengkap (Camera-Ready)',

    // Registration
    'reg_form_title' => 'Formulir Registrasi Peserta',
    'reg_form_desc' => 'Lengkapi data pribadi dan rincian keikutsertaan Anda dalam konferensi.',
    'ticket_type_legend' => 'Pilih Jenis Partisipasi',
    'category_legend' => 'Kategori Peserta & Kewarganegaraan',
    'personal_info' => 'Informasi Pribadi & Afiliasi',
    'full_name' => 'Nama Lengkap (beserta gelar)',
    'email' => 'Alamat Email',
    'phone' => 'Nomor WhatsApp / HP',
    'country' => 'Negara Asal',
    'department' => 'Jurusan / Program Studi',
    'city' => 'Kota',

    // Abstract Section in Reg Form
    'abstract_info' => 'Pengiriman Naskah Abstrak',
    'abstract_title' => 'Judul Abstrak',
    'topic' => 'Topik / Jalur Penelitian',
    'authors' => 'Penulis & Afiliasi',
    'abstract_body' => 'Isi Abstrak',
    'keywords' => 'Kata Kunci (3–5 istilah)',
    'upload_abstract_doc' => 'Unggah Dokumen Abstrak (Opsional .docx/.pdf)',

    // Listener Section
    'field_trip_title' => 'Ekskursi Pertanian Minangkabau (Field Trip)',
    'field_trip_desc' => 'Paket tur opsional 1 hari mengunjungi sistem pertanian dataran tinggi tradisional di Bukittinggi & Lembah Harau.',
    'field_trip_fee' => 'Biaya tambahan Rp 450.000 / USD 40',

    // Payment
    'checkout_title' => 'Pembayaran Registrasi',
    'invoice_summary' => 'Ringkasan Tagihan',
    'fee_amount' => 'Biaya Pendaftaran',
    'tax_amount' => 'Biaya Administrasi & Gateway',
    'total_amount' => 'Total Pembayaran',
    'pay_with_qris' => 'Bayar via QRIS / VA',
    'pay_with_paypal' => 'Bayar via PayPal (USD)',
    'pay_with_bank' => 'Transfer Bank Manual',
    'upload_proof' => 'Unggah Bukti Bayar',
    'proof_hint' => 'Unggah tangkapan layar atau berkas bukti transfer bank Anda (maks 5 MB)',

    // Refund
    'refund_title' => 'Pengajuan Pengembalian Dana (Refund)',
    'refund_desc' => 'Ajukan permintaan pengembalian biaya pendaftaran sesuai ketentuan panitia.',
    'refund_policy' => 'Pembatalan yang diajukan sebelum batas waktu dikenakan potongan biaya administrasi sebesar 20%. Dana ditransfer dalam 14 hari kerja.',
    'bank_name' => 'Nama Bank',
    'account_number' => 'Nomor Rekening',
    'account_holder' => 'Nama Pemilik Rekening',
    'refund_reason' => 'Alasan Pembatalan',

    // Documents
    'documents_title' => 'Dokumen Resmi & Unduhan',
    'documents_desc' => 'Akses dan unduh surat penerimaan resmi, kuitansi, dan kartu tanda peserta ASIC 2027.',
    'doc_loa' => 'Surat Penerimaan (Letter of Acceptance / LoA)',
    'doc_loa_desc' => 'Surat resmi penerimaan naskah abstrak yang diterbitkan oleh komite ilmiah.',
    'doc_invoice' => 'Faktur Tagihan (Proforma Invoice)',
    'doc_invoice_desc' => 'Bukti rincian biaya pendaftaran untuk pemrosesan pembayaran institusi.',
    'doc_receipt' => 'Kuitansi Pembayaran Resmi',
    'doc_receipt_desc' => 'Bukti lunas pembayaran pendaftaran yang ditandatangani oleh bendahara ASIC.',
    'doc_idcard' => 'Kartu Tanda Peserta (ID Badge)',
    'doc_idcard_desc' => 'Tanda pengenal cetak untuk akses masuk ruangan sidang konferensi dan konsumsi.',

    // Full Paper
    'full_paper_title' => 'Makalah Lengkap & Berkas Lampiran',
    'full_paper_desc' => 'Kirimkan naskah lengkap final (camera-ready) untuk publikasi prosiding konferensi.',
    'template_download' => 'Unduh Template Naskah ASIC (.DOCX)',
    'version' => 'Versi Pengiriman',

    // Certificate
    'cert_title' => 'Sertifikat Partisipasi & Presentasi',
    'cert_desc' => 'Sertifikat resmi terverifikasi dengan barcode kredensial digital.',
    'cert_locked_title' => 'Sertifikat Masih Terkunci',
    'cert_locked_desc' => 'Sertifikat dapat diunduh setelah sesi presentasi Anda selesai dicatat oleh pimpinan sidang dan presensi terverifikasi.',
    'download_pdf' => 'Unduh Sertifikat (PDF)',
    'verify_online' => 'Verifikasi Kredensial Online',
    'flow' => [
        'invoice_after_acceptance' => 'Invoice terbit setelah abstrak diterima.',
        'invoice_unavailable' => 'Invoice belum tersedia.',
        'no_fee_currency' => 'Belum ada event atau tarif pendaftaran aktif untuk mata uang :currency.',
        'fee_unavailable' => 'Tarif tidak tersedia',
        'abstract_payment_note' => 'Kirim abstrak terlebih dahulu. Invoice dan LoA terbit setelah abstrak diterima Tim Abstrak.',
    ],

    'attendance_qr' => [
        'title' => 'Kehadiran',
        'heading' => 'QR kehadiran dan sesi',
        'subtitle' => 'QR pribadi dipakai untuk registrasi ulang dan kehadiran peserta luring. Kehadiran per sesi dicatat dengan token QR sesi.',
        'loading' => 'Memuat QR dan riwayat sesi…',
        'load_failed' => 'Registrasi gagal dimuat.',
        'no_registration' => 'Belum ada registrasi aktif.',
        'qr_failed' => 'QR peserta gagal dimuat.',
        'personal_qr' => 'QR kehadiran pribadi',
        'registration' => 'Registrasi',
        'online_note' => 'Anda terdaftar sebagai peserta Daring. Kehadiran dicatat lewat token QR sesi yang ditampilkan panitia saat sesi berlangsung.',
        'not_ready' => 'QR aktif setelah pembayaran terverifikasi.',
        'purpose_note' => 'QR ini untuk registrasi ulang dan kehadiran, berbeda dengan QR pembayaran. Jangan dibagikan ke orang lain.',
        'download_png' => 'Unduh PNG',
        'copy_token' => 'Salin token',
        'token_fallback' => 'Token cadangan (bila QR tidak terbaca)',
        'session_checkin' => 'Check-in sesi',
        'session_note' => 'Masukkan token QR yang ditampilkan pada sesi yang sedang berlangsung.',
        'session_token' => 'Token QR sesi',
        'checkin' => 'Check-in',
        'checking' => 'Memeriksa…',
        'checkin_failed' => 'Check-in gagal.',
    ],
    'pay_info' => [
        'bank' => 'Bank',
        'account_name' => 'Atas nama',
        'account_no' => 'Nomor rekening',
        'branch' => 'Cabang',
    ],
    'portal' => [
        'registration_no' => 'Registrasi #:id',
        'no_registration' => 'Belum ada registrasi.',
        'payment_status' => ['title' => 'Status pembayaran', 'subtitle' => 'Status berdasarkan invoice pendaftaran Anda.', 'open_invoice' => 'Buka invoice'],
        'profile' => [
            'title' => 'Profil', 'heading' => 'Profil peserta', 'save_profile' => 'Simpan profil', 'change_password' => 'Ganti kata sandi',
            'current_password' => 'Kata sandi saat ini', 'new_password' => 'Kata sandi baru', 'confirm_password' => 'Konfirmasi kata sandi baru', 'update_password' => 'Perbarui kata sandi',
            'profile_saved' => 'Profil diperbarui.', 'profile_failed' => 'Profil gagal diperbarui.', 'password_saved' => 'Kata sandi diperbarui.', 'password_failed' => 'Kata sandi gagal diperbarui.',
            'country_note' => 'Negara menentukan mata uang tagihan (Indonesia: IDR, negara lain: USD) untuk pendaftaran berikutnya.',
            'fields' => ['prefix' => 'Gelar', 'first_name' => 'Nama depan', 'last_name' => 'Nama belakang', 'passport_no' => 'Nomor paspor', 'job_title' => 'Jabatan', 'phone' => 'Telepon', 'institution' => 'Institusi', 'address' => 'Alamat', 'city' => 'Kota', 'country' => 'Negara', 'postcode' => 'Kode pos'],
        ],
        'documents' => [
            'title' => 'Dokumen', 'heading' => 'Dokumen terbit', 'subtitle' => 'Dokumen hanya tersedia untuk pendaftaran yang Anda miliki.', 'loading' => 'Memuat dokumen…',
            'load_failed' => 'Dokumen gagal dimuat.', 'empty' => 'Belum ada dokumen yang diterbitkan.', 'number' => 'Nomor', 'issued' => 'Terbit', 'registration' => 'Registrasi', 'download' => 'Unduh PDF',
        ],
        'dashboard' => [
            'stat_registrations' => 'Registrasi', 'stat_abstracts' => 'Abstrak terkirim', 'stat_invoices' => 'Invoice menunggu', 'stat_documents' => 'Dokumen diterbitkan',
            'my_registrations' => 'Registrasi saya', 'abstract' => 'Abstrak', 'not_submitted' => 'Belum dikirim', 'documents' => 'Dokumen', 'start_registration' => 'Mulai registrasi',
        ],
        'refund' => [
            'title' => 'Pengajuan refund', 'submit' => 'Ajukan refund',
            'unavailable' => 'Belum tersedia. Pengembalian biaya publikasi diproses manual oleh panitia; biaya seminar tidak dapat dikembalikan.',
        ],
    ],
];
