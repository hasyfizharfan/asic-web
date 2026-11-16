<?php

return [
    // Password akun admin awal; wajib diisi eksplisit sebelum AdminUserSeeder dijalankan.
    'admin_default_password' => env('ADMIN_DEFAULT_PASSWORD'),

    // Lama jatuh tempo invoice (hari) sejak terbit — PRD §6: 1 minggu.
    'invoice_due_days' => 7,

    // Aktif karena respons API reviewer menyamarkan seluruh identitas penulis.
    'blind_review_active' => true,

    // Path/URL gambar QR pembayaran statis dari panitia (KF-23). Diisi lewat .env.
    'payment_qr_info' => env('ASIC_PAYMENT_QR_INFO'),

    // Disk privat penyimpanan dokumen terbit (LOA, penolakan, invoice, receipt).
    'documents_disk' => env('ASIC_DOCUMENTS_DISK', 'local'),

    // Edisi seminar, dipakai di nomor surat: 001/CP/4th ASIC/2027.
    'edition' => env('ASIC_EDITION', '4th'),

    // Kode scope abstrak untuk nomor surat (format template panitia).
    'scope_codes' => [
        'CP' => 'Crop Production',
        'PB' => 'Plant Breeding',
        'PP' => 'Plant Protection and Food Safety',
        'SM' => 'Soil Management',
        'SE' => 'Socio-Economic of Agriculture',
        'OT' => 'Other Topics related to Agriculture',
    ],

    // Kop surat. Data kontak/rekening/nama penanda tangan sengaja lewat .env
    // agar data pribadi panitia tidak masuk repositori publik.
    'letterhead' => [
        'title' => env('ASIC_LETTERHEAD_TITLE', '4th AGRIFOOD SYSTEM INTERNATIONAL CONFERENCE (ASIC 2027)'),
        'subtitle' => env('ASIC_LETTERHEAD_SUBTITLE', 'FACULTY OF AGRICULTURE, UNIVERSITAS ANDALAS'),
        'address' => env('ASIC_LETTERHEAD_ADDRESS', 'Faculty of Agriculture, Limau Manis, Padang, West Sumatra, Indonesia 25163'),
        'contact' => env('ASIC_LETTERHEAD_CONTACT'),
        // Path gambar logo di disk "public" (opsional), mis. "logos/unand.png".
        'logo_left' => env('ASIC_LOGO_LEFT'),
        'logo_right' => env('ASIC_LOGO_RIGHT'),
    ],
    'city' => env('ASIC_DOCUMENT_CITY', 'Padang'),

    // Penanda tangan: LoA & surat penolakan oleh Ketua (peran Admin),
    // invoice & receipt oleh Bendahara. Gambar ttd/stempel diambil dari modul Tanda Tangan Digital.
    'signers' => [
        'chair' => ['name' => env('ASIC_CHAIR_NAME', 'Chair of the Organizing Committee'), 'title' => 'Chair Person'],
        'treasurer' => ['name' => env('ASIC_TREASURER_NAME', 'Treasurer'), 'title' => 'Treasurer'],
    ],

    // Informasi pembayaran yang dicetak di LoA dan invoice. Kosong = baris tidak ditampilkan.
    'payment' => [
        'bank_name' => env('ASIC_BANK_NAME'),
        'account_name' => env('ASIC_BANK_ACCOUNT_NAME'),
        'account_no' => env('ASIC_BANK_ACCOUNT_NO'),
        'swift_code' => env('ASIC_BANK_SWIFT'),
        'branch' => env('ASIC_BANK_BRANCH'),
        'international_note' => env('ASIC_PAYMENT_INTERNATIONAL_NOTE', 'International participants can transfer using PayPal/Wise/Flip.'),
    ],
];
