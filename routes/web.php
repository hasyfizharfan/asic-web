<?php

use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\Portal\ParticipantController;
use App\Http\Controllers\Portal\ReviewerController;
use App\Http\Controllers\Portal\TreasurerController;
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
Route::get('/participant/register', [AuthPageController::class, 'registerParticipant'])->name('auth.participant.register');
Route::get('/secretariat/login', [AuthPageController::class, 'login'])->defaults('portal', 'secretariat')->name('auth.secretariat.login');
Route::get('/treasurer/login', [AuthPageController::class, 'login'])->defaults('portal', 'treasurer')->name('auth.treasurer.login');
Route::get('/reviewer/login', [AuthPageController::class, 'login'])->defaults('portal', 'reviewer')->name('auth.reviewer.login');
Route::get('/admin/login', [AuthPageController::class, 'login'])->defaults('portal', 'admin')->name('auth.admin.login');
Route::get('/password/reset/{token}', [AuthPageController::class, 'passwordReset'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/portal', [PortalController::class, 'redirectToDashboard'])->name('portal.dashboard');

    Route::prefix('participant')->name('portal.participant.')->middleware('permission:access_participant_portal')->group(function (): void {
        Route::get('/', [ParticipantController::class, 'index']);
        Route::get('/dashboard', [ParticipantController::class, 'dashboard'])->name('dashboard');
        Route::get('/registration', [ParticipantController::class, 'registration'])->name('registration');
        Route::get('/abstract', [ParticipantController::class, 'abstract'])->name('abstract');
        Route::get('/payment/checkout', [ParticipantController::class, 'checkout'])->name('payment.checkout');
        Route::get('/payment/status', [ParticipantController::class, 'paymentStatus'])->name('payment.status');
        Route::get('/documents', [ParticipantController::class, 'documents'])->name('documents');
        Route::get('/attendance', [ParticipantController::class, 'attendance'])->name('attendance');
        Route::get('/files/full-paper', [ParticipantController::class, 'fullPaper'])->name('full-paper');
        Route::get('/profile', [ParticipantController::class, 'profile'])->name('profile');
        Route::get('/refund', [ParticipantController::class, 'refund'])->name('refund');
        Route::get('/notifications', [ParticipantController::class, 'notifications'])->name('notifications');
        Route::get('/certificate', [ParticipantController::class, 'certificate'])->name('certificate');
    });

    Route::prefix('secretariat')->name('portal.secretariat.')->middleware('permission:access_secretariat_portal')->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.secretariat.dashboard'));
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::view('/abstracts', 'secretariat.abstracts.index')->name('abstracts.index');
        Route::get('/abstracts/{id}', fn (int $id) => view('secretariat.abstracts.show', ['id' => $id]))->whereNumber('id')->name('abstracts.show');
        Route::view('/reregistration', 'secretariat.reregistration')->name('reregistration');
        Route::view('/attendance', 'secretariat.attendance')->name('attendance');
        Route::view('/recap', 'secretariat.recap')->name('recap');
        Route::view('/announcements', 'secretariat.announcements')->name('announcements');
        Route::view('/content', 'secretariat.content')->name('content');
    });

    Route::prefix('treasurer')->name('portal.treasurer.')->middleware('permission:access_treasurer_portal')->group(function (): void {
        Route::get('/', [TreasurerController::class, 'dashboard']);
        Route::get('/dashboard', [TreasurerController::class, 'dashboard'])->name('dashboard');
        Route::get('/tariffs', [TreasurerController::class, 'tariffs'])->name('tariffs');
        Route::get('/payments', [TreasurerController::class, 'payments'])->name('payments.index');
        Route::get('/payments/{id}', [TreasurerController::class, 'paymentDetails'])->whereNumber('id')->name('payments.show');
        Route::get('/receipts/preview', [TreasurerController::class, 'receipts'])->name('receipts.preview');
        Route::get('/refunds', [TreasurerController::class, 'refunds'])->name('refunds');
    });

    Route::prefix('reviewer')->name('portal.reviewer.')->middleware('permission:access_reviewer_portal')->group(function (): void {
        Route::get('/', [ReviewerController::class, 'dashboard']);
        Route::get('/dashboard', [ReviewerController::class, 'dashboard'])->name('dashboard');
        Route::get('/queue', [ReviewerController::class, 'queue'])->name('queue');
        Route::get('/abstracts/{id}', [ReviewerController::class, 'show'])->whereNumber('id')->name('abstracts.show');
        Route::get('/profile', [ReviewerController::class, 'profile'])->name('profile');
    });

    Route::prefix('admin')->name('portal.admin.')->middleware('permission:access_admin_portal')->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.admin.dashboard'));
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::view('/overview', 'admin.overview')->name('overview');
        Route::view('/users', 'admin.users')->name('users');
        Route::view('/permissions', 'admin.permissions')->name('permissions');
        Route::view('/signatures', 'admin.signatures')->name('signatures');
        Route::view('/templates', 'admin.templates')->name('templates');
        Route::view('/settings', 'admin.settings')->name('settings');
        Route::view('/archive', 'admin.archive')->name('archive');
    });
});

// Frontend prototype routes are isolated from authentication and real portal routes.
if (app()->environment('local') && file_exists(__DIR__.'/fe-preview.php')) {
    require __DIR__.'/fe-preview.php';
}
