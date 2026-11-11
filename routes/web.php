<?php

use App\Http\Controllers\AbstractController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParticipantFileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// =========================================================================
// 1. PUBLIC ROUTES (KF-08: Registrasi, Login, dan Reset Kata Sandi)
// =========================================================================
Route::middleware('guest')->group(function () {
    // Registrasi Akun Awal Peserta
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    // Autentikasi Login
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Quick Demo Persona Login (Prototype Role Demo pada UI)
    Route::match(['get', 'post'], '/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('demo.login');

    // SSO Universitas Andalas (@unand.ac.id)
    Route::match(['get', 'post'], '/auth/sso/unand', [AuthController::class, 'ssoUnand'])->name('auth.sso.unand');

    // Bantuan Pencarian Username (Forgot Username pada UI)
    Route::post('/password/forgot-username', [AuthController::class, 'forgotUsername'])->name('password.forgot-username');

    // Lupa Kata Sandi (Request Link Reset)
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.forgot');

    // Eksekusi Reset Kata Sandi Baru
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.update');

    // Endpoint/Tampilan Form Reset Kata Sandi Berdasarkan Token
    Route::get('/password/reset/{token}', function (string $token) {
        return response()->json([
            'token' => $token,
            'email' => request('email'),
        ]);
    })->name('password.reset');
});

// =========================================================================
// 2. AUTHENTICATED ROUTES (Terproteksi Middleware Auth)
// =========================================================================
Route::middleware('auth')->group(function () {
    // Logout Akun & Info Profil
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    // ---------------------------------------------------------------------
    // MODUL M-03: PENDAFTARAN PESERTA & ADD-ON (KF-10)
    // ---------------------------------------------------------------------
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/registrations/{id_registration}', [RegistrationController::class, 'show'])->name('registrations.show');

    // ---------------------------------------------------------------------
    // MODUL M-04: PENGIRIMAN ABSTRAK (KF-15, KF-17)
    // ---------------------------------------------------------------------
    Route::get('/registrations/{id_registration}/abstract', [AbstractController::class, 'show'])->name('registrations.abstract.show');
    Route::post('/registrations/{id_registration}/abstract', [AbstractController::class, 'store'])->name('registrations.abstract.store');
    Route::get('/registrations/{id_registration}/abstract/download', [AbstractController::class, 'download'])->name('registrations.abstract.download');

    // ---------------------------------------------------------------------
    // MODUL M-04: BERKAS PRESENTASI & ARTIKEL LENGKAP (KF-16, KF-17)
    // ---------------------------------------------------------------------
    Route::get('/registrations/{id_registration}/files', [ParticipantFileController::class, 'index'])->name('registrations.files.index');
    Route::post('/registrations/{id_registration}/files', [ParticipantFileController::class, 'store'])->name('registrations.files.store');
    Route::get('/participant-files/{id_file}', [ParticipantFileController::class, 'show'])->name('participant-files.show');
    Route::get('/participant-files/{id_file}/download', [ParticipantFileController::class, 'download'])->name('participant-files.download');

    // ---------------------------------------------------------------------
    // MODUL M-03: QR KEHADIRAN PESERTA LURING (KF-13)
    // ---------------------------------------------------------------------
    Route::post('/registrations/{id_registration}/attendance/generate', [AttendanceController::class, 'generate'])->name('registrations.attendance.generate');
    Route::get('/registrations/{id_registration}/attendance/qr', [AttendanceController::class, 'showQr'])->name('registrations.attendance.qr');

    // ---------------------------------------------------------------------
    // MODUL M-03: SCAN QR REGISTRASI ULANG (KF-12, KF-14)
    // Khusus Sekretariat & Admin
    // ---------------------------------------------------------------------
    Route::middleware('role:Sekretariat,Admin')->group(function () {
        Route::post('/attendance/scan', [AttendanceController::class, 'scanQr'])->name('attendance.scan');
    });

    // =====================================================================
    // 3. ADMIN ONLY ROUTES (KF-09: Manajemen Akun & Hak Akses Sistem)
    //    Terproteksi: auth + role:Admin
    // =====================================================================
    Route::middleware('role:Admin')->prefix('admin')->name('admin.')->group(function () {
        // Batch Actions & Export CSV User & Role Management
        Route::post('users/batch-action', [UserManagementController::class, 'batchAction'])->name('users.batch-action');
        Route::get('users/export-csv', [UserManagementController::class, 'exportCsv'])->name('users.export-csv');

        // CRUD Akun Pengguna (user_account & role)
        Route::resource('users', UserManagementController::class)->parameters([
            'users' => 'id_user',
        ]);

        // Toggle Status Keaktifan Akun (Aktif / Nonaktif)
        Route::patch('users/{id_user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        // Roles & Permissions Matrix (KF-09 & UI Matrix)
        Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions.index');
        Route::post('roles-permissions', [RolePermissionController::class, 'save'])->name('roles-permissions.save');
        Route::post('roles-permissions/reset', [RolePermissionController::class, 'reset'])->name('roles-permissions.reset');
        Route::get('roles-permissions/export', [RolePermissionController::class, 'export'])->name('roles-permissions.export');
        Route::get('roles-permissions/audit-log', [RolePermissionController::class, 'auditLog'])->name('roles-permissions.audit-log');
    });
});
