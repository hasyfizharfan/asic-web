<?php

return [
    // Lama jatuh tempo invoice (hari) sejak terbit — PRD §6: 1 minggu.
    'invoice_due_days' => 7,

    // Path/URL gambar QR pembayaran statis dari panitia (KF-23). Diisi lewat .env.
    'payment_qr_info' => env('ASIC_PAYMENT_QR_INFO'),

    // Disk privat penyimpanan dokumen terbit (LOA, penolakan, invoice, receipt).
    'documents_disk' => env('ASIC_DOCUMENTS_DISK', 'local'),
];
