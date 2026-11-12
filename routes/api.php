<?php

use App\Http\Controllers\AbstractController;
use App\Http\Controllers\AbstractReviewController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DigitalSignatureController;
use App\Http\Controllers\EventSessionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ParticipantFileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. PUBLIC ROUTES (KF-08: Registrasi, Login, dan Reset Kata Sandi)
// =========================================================================
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::match(['get', 'post'], '/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('api.demo.login');
Route::match(['get', 'post'], '/auth/sso/unand', [AuthController::class, 'ssoUnand'])->name('api.auth.sso.unand');
Route::post('/password/forgot-username', [AuthController::class, 'forgotUsername'])->name('api.password.forgot-username');
Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('api.password.email');
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLinkEmail'])->name('api.password.forgot');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('api.password.update');

// =========================================================================
// 2. AUTHENTICATED ROUTES (Terproteksi Middleware auth:sanctum)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    // Logout Akun & Info Profil
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // -----------------------------------------------------------------
    // MODUL M-03: PENDAFTARAN PESERTA & ADD-ON (KF-10)
    // -----------------------------------------------------------------
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('api.registrations.index');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('api.registrations.store');
    Route::get('/registrations/{id_registration}', [RegistrationController::class, 'show'])->name('api.registrations.show');

    // -----------------------------------------------------------------
    // MODUL M-04: PENGIRIMAN ABSTRAK OLEH PESERTA (KF-15, KF-17 & Revisi 3)
    // -----------------------------------------------------------------
    Route::get('/registrations/{id_registration}/abstract', [AbstractController::class, 'show'])->name('api.registrations.abstract.show');
    Route::post('/registrations/{id_registration}/abstract', [AbstractController::class, 'store'])->name('api.registrations.abstract.store');
    Route::get('/registrations/{id_registration}/abstract/download', [AbstractController::class, 'download'])->name('api.registrations.abstract.download');

    // -----------------------------------------------------------------
    // MODUL M-04: BERKAS PRESENTASI & ARTIKEL LENGKAP (KF-16, KF-17)
    // -----------------------------------------------------------------
    Route::get('/registrations/{id_registration}/files', [ParticipantFileController::class, 'index'])->name('api.registrations.files.index');
    Route::post('/registrations/{id_registration}/files', [ParticipantFileController::class, 'store'])->name('api.registrations.files.store');
    Route::get('/participant-files/{id_file}', [ParticipantFileController::class, 'show'])->name('api.participant-files.show');
    Route::get('/participant-files/{id_file}/download', [ParticipantFileController::class, 'download'])->name('api.participant-files.download');

    // -----------------------------------------------------------------
    // MODUL M-03 & REVISI 1: SESI KEGIATAN & QR SESI
    // -----------------------------------------------------------------
    Route::get('/events/{id_event}/sessions', [EventSessionController::class, 'index'])->name('api.sessions.index');
    Route::get('/sessions/{id_session}/qr', [EventSessionController::class, 'showQr'])->name('api.sessions.qr');
    Route::post('/sessions/{id_session}/attendance', [AttendanceController::class, 'recordAttendance'])->name('api.sessions.attendance.record');
    Route::get('/registrations/{id_registration}/sessions', [AttendanceController::class, 'participantSessions'])->name('api.registrations.sessions');

    Route::middleware('role:Sekretariat,Admin')->group(function () {
        Route::post('/sessions', [EventSessionController::class, 'store'])->name('api.sessions.store');
        Route::get('/sessions/{id_session}/attendees', [EventSessionController::class, 'attendees'])->name('api.sessions.attendees');
        Route::patch('/attendance/{id_attendance}/presentation', [AttendanceController::class, 'updatePresentationStatus'])->name('api.attendance.presentation');
    });

    // -----------------------------------------------------------------
    // MODUL M-06 & REVISI 2: INVOICE, KEDALUWARSA & PERPANJANGAN
    // -----------------------------------------------------------------
    Route::get('/invoices/{id_invoice}', [InvoiceController::class, 'show'])->name('api.invoices.show');

    Route::middleware('role:Bendahara,Admin')->group(function () {
        Route::post('/invoices/{id_invoice}/extend', [InvoiceController::class, 'extendDueDate'])->name('api.invoices.extend');
        Route::patch('/invoices/{id_invoice}/expire', [InvoiceController::class, 'markAsExpired'])->name('api.invoices.expire');
    });

    // -----------------------------------------------------------------
    // REVISI 6: MODUL TANDA TANGAN & STEMPEL BERBASIS PERAN
    // -----------------------------------------------------------------
    Route::get('/digital-signatures', [DigitalSignatureController::class, 'index'])->name('api.digital-signatures.index');
    Route::get('/digital-signatures/role/{id_role}', [DigitalSignatureController::class, 'showByRole'])->name('api.digital-signatures.by-role');
    Route::post('/digital-signatures', [DigitalSignatureController::class, 'store'])->name('api.digital-signatures.store');

    // -----------------------------------------------------------------
    // REVISI 7: HAK AKSES EKSKLUSIF TIM ABSTRAK (SELEKSI & REVIEW)
    // -----------------------------------------------------------------
    Route::middleware('role:Tim Abstrak')->prefix('tim-abstrak')->name('api.tim-abstrak.')->group(function () {
        Route::get('/submissions', [AbstractReviewController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{id_abstract}', [AbstractReviewController::class, 'show'])->name('submissions.show');
        Route::post('/submissions/{id_abstract}/review', [AbstractReviewController::class, 'review'])->name('submissions.review');
    });

    // =================================================================
    // 3. ADMIN ONLY ROUTES (KF-09: Manajemen Akun & Hak Akses Sistem)
    // =================================================================
    Route::middleware('role:Admin')->prefix('admin')->name('api.admin.')->group(function () {
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
