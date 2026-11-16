<?php

use App\Http\Controllers\AbstractController;
use App\Http\Controllers\AbstractReviewController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommunicationChannelController;
use App\Http\Controllers\DigitalSignatureController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventScheduleController;
use App\Http\Controllers\EventSessionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MyNotificationController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\ParticipantFileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\PaymentVerificationController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RegistrationFeeController;
use App\Http\Controllers\RegistrationSearchController;
use App\Http\Controllers\ReregistrationController;
use App\Http\Controllers\SpeakerController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. PUBLIC ROUTES (KF-08: Registrasi, Login, dan Reset Kata Sandi)
// =========================================================================
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/password/forgot-username', [AuthController::class, 'forgotUsername'])->name('api.password.forgot-username');
Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('api.password.email');
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLinkEmail'])->name('api.password.forgot');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('api.password.update');

// Tarif pendaftaran (read-only publik: hanya event & periode Aktif)
Route::get('/public/registration-fees', [RegistrationFeeController::class, 'publicIndex'])->name('api.public.registration-fees.index');

// Konten publik read-only (KF-01..05): hanya event Aktif/Selesai
Route::get('/public/events', [EventController::class, 'publicIndex'])->name('api.public.events.index');
Route::get('/public/events/{id_event}', [EventController::class, 'publicShow'])->whereNumber('id_event')->name('api.public.events.show');
Route::get('/public/events/{id_event}/speakers', [SpeakerController::class, 'publicIndex'])->whereNumber('id_event')->name('api.public.speakers.index');
Route::get('/public/events/{id_event}/schedules', [EventScheduleController::class, 'publicIndex'])->whereNumber('id_event')->name('api.public.schedules.index');

// =========================================================================
// 2. AUTHENTICATED ROUTES
// =========================================================================
Route::middleware(['auth:sanctum'])->group(function () {
    // Logout Akun & Info Profil
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::put('/me/profile', [MyProfileController::class, 'update'])->middleware('permission:access_participant_portal')->name('api.me.profile.update');
    Route::put('/me/password', [MyProfileController::class, 'updatePassword'])->name('api.me.password.update');
    // KF-30: notifikasi dalam situs milik pengguna sendiri
    Route::get('/me/notifications', [MyNotificationController::class, 'index'])->name('api.me.notifications.index');
    Route::get('/me/notifications/unread-count', [MyNotificationController::class, 'unreadCount'])->name('api.me.notifications.unread-count');
    Route::patch('/me/notifications/{id}/read', [MyNotificationController::class, 'markRead'])->whereUuid('id')->name('api.me.notifications.read');
    Route::post('/me/notifications/read-all', [MyNotificationController::class, 'readAll'])->name('api.me.notifications.read-all');

    // -----------------------------------------------------------------
    // MODUL M-03: PENDAFTARAN PESERTA & ADD-ON (KF-10)
    // -----------------------------------------------------------------
    Route::get('/registrations', [RegistrationController::class, 'index'])
        ->middleware('permission:manage_registrations,view_only')
        ->name('api.registrations.index');
    Route::get('/registrations/search', RegistrationSearchController::class)
        ->middleware('permission:manage_registrations')
        ->name('api.registrations.search');
    Route::post('/registrations', [RegistrationController::class, 'store'])
        ->middleware('permission:pay_registration')
        ->name('api.registrations.store');
    Route::get('/registrations/{id_registration}', [RegistrationController::class, 'show'])
        ->middleware('permission:manage_registrations,view_only')
        ->name('api.registrations.show');

    // -----------------------------------------------------------------
    // MODUL M-04: PENGIRIMAN ABSTRAK OLEH PESERTA (KF-15, KF-17 & Revisi 3)
    // -----------------------------------------------------------------
    Route::get('/registrations/{id_registration}/abstract', [AbstractController::class, 'show'])
        ->middleware('permission:view_abstracts,view_only')
        ->name('api.registrations.abstract.show');
    Route::post('/registrations/{id_registration}/abstract', [AbstractController::class, 'store'])
        ->middleware('permission:submit_abstract')
        ->name('api.registrations.abstract.store');
    Route::get('/registrations/{id_registration}/abstract/download', [AbstractController::class, 'download'])
        ->middleware('permission:view_abstracts,view_only')
        ->name('api.registrations.abstract.download');

    // -----------------------------------------------------------------
    // MODUL M-04: BERKAS PRESENTASI & ARTIKEL LENGKAP (KF-16, KF-17)
    // -----------------------------------------------------------------
    Route::get('/registrations/{id_registration}/files', [ParticipantFileController::class, 'index'])
        ->middleware('permission:manage_participant_files,view_only')
        ->name('api.registrations.files.index');
    Route::post('/registrations/{id_registration}/files', [ParticipantFileController::class, 'store'])
        ->middleware('permission:upload_participant_files')
        ->name('api.registrations.files.store');
    Route::get('/participant-files/{id_file}', [ParticipantFileController::class, 'show'])
        ->middleware('permission:manage_participant_files,view_only')
        ->name('api.participant-files.show');
    Route::get('/participant-files/{id_file}/download', [ParticipantFileController::class, 'download'])
        ->middleware('permission:manage_participant_files,view_only')
        ->name('api.participant-files.download');

    // -----------------------------------------------------------------
    // MODUL M-03 & REVISI 1: SESI KEGIATAN & QR SESI
    // -----------------------------------------------------------------
    Route::get('/events/{id_event}/sessions', [EventSessionController::class, 'index'])
        ->middleware('permission:manage_sessions,view_only')
        ->name('api.sessions.index');
    Route::get('/sessions/{id_session}/qr', [EventSessionController::class, 'showQr'])
        ->middleware('permission:manage_sessions,view_only')
        ->name('api.sessions.qr');
    Route::get('/registrations/{id_registration}/sessions', [AttendanceController::class, 'participantSessions'])
        ->middleware('permission:scan_attendance_qr,view_only')
        ->name('api.registrations.sessions');
    Route::get('/registrations/{id_registration}/attendance-qr', [AttendanceController::class, 'attendanceQr'])
        ->middleware('permission:scan_attendance_qr,view_only')
        ->name('api.registrations.attendance-qr');

    Route::middleware('permission:scan_attendance_qr')->group(function () {
        Route::post('/sessions', [EventSessionController::class, 'store'])->name('api.sessions.store');
        Route::get('/sessions/{id_session}/attendees', [EventSessionController::class, 'attendees'])->name('api.sessions.attendees');
        Route::post('/sessions/{id_session}/attendance', [AttendanceController::class, 'recordAttendance'])->name('api.sessions.attendance.record');
        Route::post('/sessions/{id_session}/attendance/scan', [AttendanceController::class, 'scanParticipantQr'])->name('api.sessions.attendance.scan');
        // KF-12: registrasi ulang luring hari pertama (terpisah dari kehadiran sesi KF-14)
        Route::post('/events/{id_event}/reregistration/scan', [ReregistrationController::class, 'scan'])
            ->whereNumber('id_event')->name('api.events.reregistration.scan');
        Route::post('/events/{id_event}/reregistration', [ReregistrationController::class, 'store'])
            ->whereNumber('id_event')->name('api.events.reregistration.store');
        Route::patch('/attendance/{id_attendance}/presentation', [AttendanceController::class, 'updatePresentationStatus'])->name('api.attendance.presentation');
    });
    Route::post('/attendance/scan', [AttendanceController::class, 'submitSessionQr'])
        ->name('api.attendance.scan-self');

    // -----------------------------------------------------------------
    // MODUL M-06 & REVISI 2: INVOICE, KEDALUWARSA & PERPANJANGAN
    // -----------------------------------------------------------------
    Route::get('/invoices/{id_invoice}', [InvoiceController::class, 'show'])
        ->middleware('permission:pay_registration,view_only')
        ->name('api.invoices.show');

    Route::middleware('permission:verify_payment')->group(function () {
        Route::post('/invoices/{id_invoice}/extend', [InvoiceController::class, 'extendDueDate'])->name('api.invoices.extend');
        Route::patch('/invoices/{id_invoice}/expire', [InvoiceController::class, 'markAsExpired'])->name('api.invoices.expire');
    });

    // -----------------------------------------------------------------
    // TASK 1: PENGELOLAAN TARIF PENDAFTARAN (permission: manage_tariffs)
    // -----------------------------------------------------------------
    Route::get('/registration-fees', [RegistrationFeeController::class, 'index'])
        ->middleware('permission:manage_tariffs,view_only')
        ->name('api.registration-fees.index');
    Route::get('/registration-fees/{id_fee}', [RegistrationFeeController::class, 'show'])
        ->whereNumber('id_fee')
        ->middleware('permission:manage_tariffs,view_only')
        ->name('api.registration-fees.show');
    Route::middleware('permission:manage_tariffs')->group(function () {
        Route::post('/registration-fees', [RegistrationFeeController::class, 'store'])->name('api.registration-fees.store');
        Route::patch('/registration-fees/{id_fee}', [RegistrationFeeController::class, 'update'])->whereNumber('id_fee')->name('api.registration-fees.update');
        Route::delete('/registration-fees/{id_fee}', [RegistrationFeeController::class, 'destroy'])->whereNumber('id_fee')->name('api.registration-fees.destroy');
    });

    // -----------------------------------------------------------------
    // TASK 2/3: DOKUMEN TERBIT (LOA, Surat Penolakan, Invoice, Receipt)
    // Otorisasi per dokumen (pemilik registrasi atau staf berizin) dilakukan di controller.
    // -----------------------------------------------------------------
    Route::get('/registrations/{id_registration}/documents', [DocumentController::class, 'index'])
        ->whereNumber('id_registration')->name('api.registrations.documents');
    Route::get('/documents/{id_document}/download', [DocumentController::class, 'download'])
        ->whereNumber('id_document')->name('api.documents.download');
    Route::post('/tim-abstrak/submissions/{id_abstract}/issue-documents', [AbstractReviewController::class, 'issueDocuments'])
        ->whereNumber('id_abstract')->middleware('permission:issue_documents')->name('api.tim-abstrak.submissions.issue-documents');

    // -----------------------------------------------------------------
    // TASK 5: BUKTI & VERIFIKASI PEMBAYARAN
    // -----------------------------------------------------------------
    Route::post('/invoices/{id_invoice}/payment-proofs', [PaymentProofController::class, 'store'])
        ->whereNumber('id_invoice')->middleware('permission:pay_registration')->name('api.invoices.payment-proofs.store');
    Route::middleware('permission:verify_payment,view_only')->group(function () {
        Route::get('/payments', [PaymentVerificationController::class, 'index'])->name('api.payments.index');
        Route::get('/payments/{id_invoice}', [PaymentVerificationController::class, 'show'])->whereNumber('id_invoice')->name('api.payments.show');
        Route::get('/payment-proofs/{id_payment_proof}/file', [PaymentVerificationController::class, 'proofFile'])->whereNumber('id_payment_proof')->name('api.payment-proofs.file');
    });
    Route::post('/payment-proofs/{id_payment_proof}/verify', [PaymentVerificationController::class, 'verify'])
        ->whereNumber('id_payment_proof')->middleware('permission:verify_payment')->name('api.payment-proofs.verify');

    // -----------------------------------------------------------------
    // TASK 6-8: KONTEN WEBSITE, PENGUMUMAN, KANAL KOMUNIKASI (manage_website_content)
    // -----------------------------------------------------------------
    Route::get('/announcements', [AnnouncementController::class, 'visibleIndex'])->name('api.announcements.index');
    Route::get('/communication-channels', [CommunicationChannelController::class, 'visibleIndex'])->name('api.communication-channels.index');

    Route::prefix('content')->name('api.content.')->group(function () {
        Route::middleware('permission:manage_website_content,view_only')->group(function () {
            Route::get('events', [EventController::class, 'index'])->name('events.index');
            Route::get('events/{id_event}', [EventController::class, 'show'])->whereNumber('id_event')->name('events.show');
            Route::get('speakers', [SpeakerController::class, 'index'])->name('speakers.index');
            Route::get('speakers/{id_speaker}', [SpeakerController::class, 'show'])->whereNumber('id_speaker')->name('speakers.show');
            Route::get('schedules', [EventScheduleController::class, 'index'])->name('schedules.index');
            Route::get('schedules/{id_schedule}', [EventScheduleController::class, 'show'])->whereNumber('id_schedule')->name('schedules.show');
            Route::get('announcements', [AnnouncementController::class, 'manageIndex'])->name('announcements.index');
            Route::get('announcements/{id_announcement}', [AnnouncementController::class, 'show'])->whereNumber('id_announcement')->name('announcements.show');
            Route::get('communication-channels', [CommunicationChannelController::class, 'manageIndex'])->name('channels.index');
            Route::get('communication-channels/{id_channel}', [CommunicationChannelController::class, 'show'])->whereNumber('id_channel')->name('channels.show');
        });
        Route::middleware('permission:manage_website_content')->group(function () {
            Route::post('events', [EventController::class, 'store'])->name('events.store');
            Route::patch('events/{id_event}', [EventController::class, 'update'])->whereNumber('id_event')->name('events.update');
            Route::delete('events/{id_event}', [EventController::class, 'destroy'])->whereNumber('id_event')->name('events.destroy');
            Route::post('speakers', [SpeakerController::class, 'store'])->name('speakers.store');
            Route::match(['post', 'patch'], 'speakers/{id_speaker}', [SpeakerController::class, 'update'])->whereNumber('id_speaker')->name('speakers.update');
            Route::delete('speakers/{id_speaker}', [SpeakerController::class, 'destroy'])->whereNumber('id_speaker')->name('speakers.destroy');
            Route::post('schedules', [EventScheduleController::class, 'store'])->name('schedules.store');
            Route::patch('schedules/{id_schedule}', [EventScheduleController::class, 'update'])->whereNumber('id_schedule')->name('schedules.update');
            Route::delete('schedules/{id_schedule}', [EventScheduleController::class, 'destroy'])->whereNumber('id_schedule')->name('schedules.destroy');
            Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::patch('announcements/{id_announcement}', [AnnouncementController::class, 'update'])->whereNumber('id_announcement')->name('announcements.update');
            Route::delete('announcements/{id_announcement}', [AnnouncementController::class, 'destroy'])->whereNumber('id_announcement')->name('announcements.destroy');
            Route::post('communication-channels', [CommunicationChannelController::class, 'store'])->name('channels.store');
            Route::patch('communication-channels/{id_channel}', [CommunicationChannelController::class, 'update'])->whereNumber('id_channel')->name('channels.update');
            Route::delete('communication-channels/{id_channel}', [CommunicationChannelController::class, 'destroy'])->whereNumber('id_channel')->name('channels.destroy');
        });
    });

    // -----------------------------------------------------------------
    // TASK 9-10: REKAPITULASI & EKSPOR EXCEL (izin existing, tanpa mengubah matriks hak akses)
    //   registrasi/peserta  -> manage_registrations (full)  : Sekretariat, Admin
    //   abstrak             -> review_abstracts (view_only) : Sekretariat, Tim Abstrak, Admin
    //   pembayaran/invoice  -> verify_payment (view_only)   : Sekretariat, Bendahara, Admin
    // -----------------------------------------------------------------
    Route::prefix('recap')->name('api.recap.')->group(function () {
        Route::get('registrations', [RecapController::class, 'registrations'])->middleware('permission:manage_registrations')->name('registrations');
        Route::get('participants', [RecapController::class, 'participants'])->middleware('permission:manage_registrations')->name('participants');
        Route::get('abstracts', [RecapController::class, 'abstracts'])->middleware('permission:review_abstracts,view_only')->name('abstracts');
        Route::get('invoices', [RecapController::class, 'invoices'])->middleware('permission:verify_payment,view_only')->name('invoices');
        Route::get('payments', [RecapController::class, 'payments'])->middleware('permission:verify_payment,view_only')->name('payments');
        Route::get('attendance', [RecapController::class, 'attendance'])->middleware('permission:scan_attendance_qr,view_only')->name('attendance');
    });
    Route::prefix('exports')->name('api.exports.')->group(function () {
        Route::get('participants', [ExportController::class, 'participants'])->middleware('permission:manage_registrations')->name('participants');
        Route::get('abstracts', [ExportController::class, 'abstracts'])->middleware('permission:review_abstracts,view_only')->name('abstracts');
        Route::get('payments', [ExportController::class, 'payments'])->middleware('permission:verify_payment,view_only')->name('payments');
        Route::get('invoices', [ExportController::class, 'invoices'])->middleware('permission:verify_payment,view_only')->name('invoices');
        Route::get('recap', [ExportController::class, 'recap'])->middleware(['permission:manage_registrations', 'permission:verify_payment,view_only', 'permission:review_abstracts,view_only'])->name('recap');
    });

    // -----------------------------------------------------------------
    // REVISI 6: MODUL TANDA TANGAN & STEMPEL BERBASIS PERAN
    // -----------------------------------------------------------------
    Route::get('/digital-signatures', [DigitalSignatureController::class, 'index'])
        ->middleware('permission:manage_digital_signatures,view_only')
        ->name('api.digital-signatures.index');
    Route::get('/digital-signatures/role/{id_role}', [DigitalSignatureController::class, 'showByRole'])
        ->middleware('permission:manage_digital_signatures,view_only')
        ->name('api.digital-signatures.by-role');
    Route::get('/digital-signatures/role/{id_role}/files/{file_type}', [DigitalSignatureController::class, 'file'])
        ->whereIn('file_type', ['ttd', 'stempel'])
        ->middleware('permission:manage_digital_signatures,view_only')
        ->name('api.digital-signatures.files');
    Route::post('/digital-signatures', [DigitalSignatureController::class, 'store'])
        ->middleware('permission:manage_digital_signatures')
        ->name('api.digital-signatures.store');

    // -----------------------------------------------------------------
    // REVISI 7: HAK AKSES EKSKLUSIF TIM ABSTRAK (SELEKSI & REVIEW)
    // -----------------------------------------------------------------
    Route::prefix('tim-abstrak')->name('api.tim-abstrak.')->group(function () {
        Route::get('/submissions', [AbstractReviewController::class, 'index'])
            ->middleware('permission:review_abstracts,view_only')
            ->name('submissions.index');
        Route::get('/submissions/{id_abstract}', [AbstractReviewController::class, 'show'])
            ->whereNumber('id_abstract')->middleware('permission:review_abstracts,view_only')
            ->name('submissions.show');
        Route::get('/submissions/{id_abstract}/file', [AbstractReviewController::class, 'download'])
            ->whereNumber('id_abstract')->middleware('permission:review_abstracts')->name('submissions.file');
        Route::post('/submissions/{id_abstract}/review', [AbstractReviewController::class, 'review'])
            ->whereNumber('id_abstract')->middleware('permission:accept_reject')
            ->name('submissions.review');
    });

    // =================================================================
    // 3. ADMIN ONLY ROUTES (KF-09: Manajemen Akun & Hak Akses Sistem)
    // =================================================================
    Route::middleware('permission:manage_users')->prefix('admin')->name('api.admin.')->group(function () {
        Route::post('users/batch-action', [UserManagementController::class, 'batchAction'])->name('users.batch-action');
        Route::get('users/export-csv', [UserManagementController::class, 'exportCsv'])->name('users.export-csv');

        Route::resource('users', UserManagementController::class)->parameters([
            'users' => 'id_user',
        ]);

        Route::patch('users/{id_user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions.index');
        Route::post('roles-permissions', [RolePermissionController::class, 'save'])->name('roles-permissions.save');
        Route::post('roles-permissions/reset', [RolePermissionController::class, 'reset'])->name('roles-permissions.reset');
        Route::get('roles-permissions/export', [RolePermissionController::class, 'export'])->name('roles-permissions.export');
        Route::get('roles-permissions/audit-log', [RolePermissionController::class, 'auditLog'])->name('roles-permissions.audit-log');
    });
});
