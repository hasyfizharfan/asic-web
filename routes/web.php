<?php

use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortalController::class, 'index'])->name('portal.landing');
Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['id', 'en'], true), 404);

    session(['locale' => $locale]);
    app()->setLocale($locale);

    return redirect()->back();
})->name('portal.language');

Route::get('/login', [AuthPageController::class, 'login'])->name('login');
Route::get('/participant/login', [AuthPageController::class, 'login'])->defaults('portal', 'participant')->name('auth.participant.login');
Route::get('/secretariat/login', [AuthPageController::class, 'login'])->defaults('portal', 'secretariat')->name('auth.secretariat.login');
Route::get('/treasurer/login', [AuthPageController::class, 'login'])->defaults('portal', 'treasurer')->name('auth.treasurer.login');
Route::get('/reviewer/login', [AuthPageController::class, 'login'])->defaults('portal', 'reviewer')->name('auth.reviewer.login');
Route::get('/admin/login', [AuthPageController::class, 'login'])->defaults('portal', 'admin')->name('auth.admin.login');
Route::get('/password/reset/{token}', [AuthPageController::class, 'passwordReset'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/portal', [PortalController::class, 'redirectToDashboard'])->name('portal.dashboard');

    Route::prefix('participant')->name('portal.participant.')->middleware('permission:access_participant_portal')->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.participant.dashboard'));
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('secretariat')->name('portal.secretariat.')->middleware('permission:access_secretariat_portal')->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.secretariat.dashboard'));
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('treasurer')->name('portal.treasurer.')->middleware('permission:access_treasurer_portal')->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.treasurer.dashboard'));
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('reviewer')->name('portal.reviewer.')->middleware('permission:access_reviewer_portal')->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.reviewer.dashboard'));
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('admin')->name('portal.admin.')->middleware('permission:access_admin_portal')->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.admin.dashboard'));
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
    });
});

// Frontend prototype routes are isolated from authentication and real portal routes.
if (app()->environment('local') && file_exists(__DIR__.'/fe-preview.php')) {
    require __DIR__.'/fe-preview.php';
}
