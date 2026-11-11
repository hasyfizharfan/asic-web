<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
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

    // Lupa Kata Sandi (Request Link Reset)
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');

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
    // Logout Akun
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Informasi Profil Pengguna Aktif
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    // =====================================================================
    // 3. ADMIN ONLY ROUTES (KF-09: Manajemen Akun & Hak Akses Sistem)
    //    Terproteksi: auth + role:Admin
    // =====================================================================
    Route::middleware('role:Admin')->prefix('admin')->name('admin.')->group(function () {
        // CRUD Akun Pengguna (user_account & role)
        Route::resource('users', UserManagementController::class)->parameters([
            'users' => 'id_user',
        ]);

        // Toggle Status Keaktifan Akun (Aktif / Nonaktif)
        Route::patch('users/{id_user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
            ->name('users.toggle-status');
    });
});
