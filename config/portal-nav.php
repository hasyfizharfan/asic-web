<?php

return [
    'participant' => [
        ['label' => 'asic-nav.dashboard', 'route' => 'portal.participant.dashboard', 'icon' => 'space_dashboard', 'permission' => 'access_participant_portal'],
        ['label' => 'asic-participant.reg_form_title', 'route' => 'portal.participant.registration', 'icon' => 'how_to_reg', 'permission' => 'pay_registration'],
        ['label' => 'asic-nav.my_abstract', 'route' => 'portal.participant.abstract', 'icon' => 'description', 'permission' => 'submit_abstract'],
        ['label' => 'asic-nav.full_paper', 'route' => 'portal.participant.full-paper', 'icon' => 'upload_file', 'permission' => 'upload_participant_files'],
        ['label' => 'asic-nav.payment', 'route' => 'portal.participant.payment.checkout', 'icon' => 'payments', 'permission' => 'pay_registration'],
        ['label' => 'asic-nav.documents', 'route' => 'portal.participant.documents', 'icon' => 'folder', 'permission' => 'manage_participant_files'],
        ['label' => 'asic-nav.attendance_qr', 'route' => 'portal.participant.attendance', 'icon' => 'qr_code_2', 'permission' => 'scan_attendance_qr'],
        ['label' => 'asic-participant.refund_title', 'route' => 'portal.participant.refund', 'icon' => 'currency_exchange', 'permission' => 'access_participant_portal'],
        ['label' => 'asic-nav.certificate', 'route' => 'portal.participant.certificate', 'icon' => 'workspace_premium', 'permission' => 'access_participant_portal'],
        ['label' => 'asic-nav.notifications', 'route' => 'portal.participant.notifications', 'icon' => 'notifications', 'permission' => 'access_participant_portal'],
        ['label' => 'asic-nav.profile', 'route' => 'portal.participant.profile', 'icon' => 'account_circle', 'permission' => 'access_participant_portal'],
    ],
    'secretariat' => [
        ['label' => 'asic-nav.dashboard', 'route' => 'portal.secretariat.dashboard', 'icon' => 'space_dashboard', 'permission' => 'access_secretariat_portal'],
        ['label' => 'asic-nav.abstracts', 'route' => 'portal.secretariat.abstracts.index', 'icon' => 'description', 'permission' => 'view_abstracts'],
        ['label' => 'asic-nav.reregistration', 'route' => 'portal.secretariat.reregistration', 'icon' => 'qr_code_scanner', 'permission' => 'manage_registrations'],
        ['label' => 'asic-nav.attendance_presentation', 'route' => 'portal.secretariat.attendance', 'icon' => 'how_to_reg', 'permission' => 'scan_attendance_qr'],
        ['label' => 'asic-nav.recap', 'route' => 'portal.secretariat.recap', 'icon' => 'bar_chart', 'permission' => 'manage_registrations'],
        ['label' => 'asic-nav.announcements', 'route' => 'portal.secretariat.announcements', 'icon' => 'campaign', 'permission' => 'manage_website_content'],
        ['label' => 'asic-nav.website_content', 'route' => 'portal.secretariat.content', 'icon' => 'public', 'permission' => 'manage_website_content'],
    ],
    'treasurer' => [
        ['label' => 'asic-nav.tariffs', 'route' => 'portal.treasurer.tariffs', 'icon' => 'price_change', 'permission' => 'manage_tariffs'],
        ['label' => 'asic-nav.payment_queue', 'route' => 'portal.treasurer.payments.index', 'icon' => 'receipt_long', 'permission' => 'verify_payment'],
        ['label' => 'asic-nav.refund_requests', 'route' => 'portal.treasurer.refunds', 'icon' => 'currency_exchange', 'permission' => 'verify_payment'],
    ],
    'reviewer' => [
        ['label' => 'asic-nav.dashboard', 'route' => 'portal.reviewer.dashboard', 'icon' => 'space_dashboard', 'permission' => 'access_reviewer_portal'],
        ['label' => 'asic-reviewer.queue_title', 'route' => 'portal.reviewer.queue', 'icon' => 'rate_review', 'permission' => 'review_abstracts'],
        ['label' => 'asic-reviewer.profile_title', 'route' => 'portal.reviewer.profile', 'icon' => 'account_circle', 'permission' => 'review_abstracts'],
    ],
    'admin' => [
        ['label' => 'asic-nav.overview', 'route' => 'portal.admin.overview', 'icon' => 'dashboard', 'permission' => 'access_admin_portal'],
        ['label' => 'asic-nav.users_roles', 'route' => 'portal.admin.users', 'icon' => 'group', 'permission' => 'manage_users'],
        ['label' => 'asic-nav.permissions', 'route' => 'portal.admin.permissions', 'icon' => 'verified_user', 'permission' => 'manage_users'],
        ['label' => 'asic-nav.document_templates', 'route' => 'portal.admin.templates', 'icon' => 'description', 'permission' => 'manage_document_templates'],
        ['label' => 'asic-nav.website_settings', 'route' => 'portal.admin.settings', 'icon' => 'settings', 'permission' => 'manage_website_content'],
        ['label' => 'asic-nav.archive', 'route' => 'portal.admin.archive', 'icon' => 'inventory_2', 'permission' => 'manage_users'],
    ],
];
