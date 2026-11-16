<?php

return [
    'dashboard_title' => 'Participant Dashboard',
    'welcome_back' => 'Welcome back, :name',
    'role_oral' => 'Oral Presenter',
    'role_poster' => 'Poster Presenter',
    'role_listener' => 'Conference Listener / Attendee',
    'reg_id' => 'Registration ID',
    'institution' => 'Institution',
    'presentation_mode' => 'Presentation Mode',
    'onsite' => 'On-site (Padang)',
    'online' => 'Virtual / Online',

    // Action cards
    'abstract_card_title' => 'Abstract Submission',
    'payment_card_title' => 'Conference Payment',
    'attendance_card_title' => 'Attendance Check-in',
    'certificate_card_title' => 'Official Certificate',
    'full_paper_card_title' => 'Camera-Ready Paper',

    // Registration
    'reg_form_title' => 'Participant Registration Form',
    'reg_form_desc' => 'Fill in your personal information and conference participation details.',
    'ticket_type_legend' => 'Select Participation Type',
    'category_legend' => 'Participant Category & Nationality',
    'personal_info' => 'Personal & Affiliation Information',
    'full_name' => 'Full Name (with Academic Titles)',
    'email' => 'Email Address',
    'phone' => 'WhatsApp / Mobile Number',
    'country' => 'Country of Origin',
    'department' => 'Department / Study Program',
    'city' => 'City',

    // Abstract Section in Reg Form
    'abstract_info' => 'Abstract Manuscript Submission',
    'abstract_title' => 'Abstract Title',
    'topic' => 'Topic / Research Track',
    'authors' => 'Author(s) & Affiliation',
    'abstract_body' => 'Abstract Text',
    'keywords' => 'Keywords (3–5 terms)',
    'upload_abstract_doc' => 'Upload Abstract Document (Optional .docx/.pdf)',

    // Listener Section
    'field_trip_title' => 'Minangkabau Agricultural Excursion (Field Trip)',
    'field_trip_desc' => 'Optional full-day tour to traditional highland farming systems in Bukittinggi & Harau Valley.',
    'field_trip_fee' => 'Additional IDR 450.000 / USD 40',

    // Payment
    'checkout_title' => 'Payment Checkout',
    'invoice_summary' => 'Invoice Summary',
    'fee_amount' => 'Registration Fee',
    'tax_amount' => 'Administration & Gateway Fee',
    'total_amount' => 'Total Amount Due',
    'pay_with_qris' => 'Pay via QRIS / VA',
    'pay_with_paypal' => 'Pay via PayPal (USD)',
    'pay_with_bank' => 'Manual Bank Transfer',
    'upload_proof' => 'Upload Payment Proof',
    'proof_hint' => 'Upload screenshot or PDF receipt from your bank (max 5 MB)',

    // Refund
    'refund_title' => 'Refund Request Submission',
    'refund_desc' => 'Submit a refund request in case of cancellation. Subject to conference refund terms.',
    'refund_policy' => 'Cancellations requested before the deadline incur a 20% administrative handling fee. Refunds are disbursed within 14 business days.',
    'bank_name' => 'Bank Name',
    'account_number' => 'Account Number',
    'account_holder' => 'Account Holder Name',
    'refund_reason' => 'Reason for Cancellation',

    // Documents
    'documents_title' => 'Official Documents & Downloads',
    'documents_desc' => 'Access and download official letters, receipts, and name tags for ASIC 2027.',
    'doc_loa' => 'Letter of Acceptance (LoA)',
    'doc_loa_desc' => 'Official acceptance letter issued by the scientific committee.',
    'doc_invoice' => 'Proforma Invoice',
    'doc_invoice_desc' => 'Billing statement for payment processing and institution reimbursement.',
    'doc_receipt' => 'Official Payment Receipt',
    'doc_receipt_desc' => 'Proof of settled registration fee signed by ASIC treasurer.',
    'doc_idcard' => 'Participant ID Badge',
    'doc_idcard_desc' => 'Printable name badge for access to conference halls and catering.',

    // Full Paper
    'full_paper_title' => 'Full Paper & Supplementary Files',
    'full_paper_desc' => 'Submit your camera-ready manuscript for publication in conference proceedings.',
    'template_download' => 'Download ASIC Manuscript Template (.DOCX)',
    'version' => 'Submission Version',

    // Certificate
    'cert_title' => 'Certificate of Participation & Presentation',
    'cert_desc' => 'Official verified certificate with digital credential barcode.',
    'cert_locked_title' => 'Certificate Currently Locked',
    'cert_locked_desc' => 'Certificates become available after your presentation is logged by the session chair and attendance is verified.',
    'download_pdf' => 'Download Certificate (PDF)',
    'verify_online' => 'Verify Credential Online',
    'flow' => [
        'invoice_after_acceptance' => 'The invoice is issued after the abstract is accepted.',
        'invoice_unavailable' => 'The invoice is not available yet.',
        'no_fee_currency' => 'No active event or registration fee is available for :currency.',
        'fee_unavailable' => 'Fee not available',
        'abstract_payment_note' => 'Submit your abstract first. The invoice and LoA are issued after the Abstract Team accepts it.',
    ],

    'attendance_qr' => [
        'title' => 'Attendance',
        'heading' => 'Attendance and session QR',
        'subtitle' => 'Your personal QR is used for re-registration and attendance of on-site participants. Per-session attendance is recorded with the session QR token.',
        'loading' => 'Loading QR and session history…',
        'load_failed' => 'Failed to load registrations.',
        'no_registration' => 'No active registration yet.',
        'qr_failed' => 'Failed to load the participant QR.',
        'personal_qr' => 'Personal attendance QR',
        'registration' => 'Registration',
        'online_note' => 'You are registered as an online participant. Attendance is recorded with the session QR token shown by the committee during the session.',
        'not_ready' => 'The QR becomes active after your payment is verified.',
        'purpose_note' => 'This QR is for re-registration and attendance, not for payment. Do not share it with others.',
        'download_png' => 'Download PNG',
        'copy_token' => 'Copy token',
        'token_fallback' => 'Backup token (if the QR cannot be scanned)',
        'session_checkin' => 'Session check-in',
        'session_note' => 'Enter the QR token shown in the session that is currently running.',
        'session_token' => 'Session QR token',
        'checkin' => 'Check-in',
        'checking' => 'Checking…',
        'checkin_failed' => 'Check-in failed.',
    ],
];
