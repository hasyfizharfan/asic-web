<?php

use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Portal\AdminController;
use App\Http\Controllers\Portal\ParticipantController;
use App\Http\Controllers\Portal\ReviewerController;
use App\Http\Controllers\Portal\SecretariatController;
use App\Http\Controllers\Portal\TreasurerController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicArchiveController;
use App\Http\Controllers\PublicDownloadController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortalController::class, 'index'])->name('portal.landing');
// Fase 1: halaman publik (server-rendered, dua bahasa)
foreach (PublicPageController::PAGES as $publicPage) {
    Route::get('/'.$publicPage, [PublicPageController::class, 'show'])->defaults('page', $publicPage)->name('public.'.$publicPage);
}

// Fase 2: unduhan template publik (KF-06) dan arsip seminar (KF-29)
Route::get('/downloads', [PublicDownloadController::class, 'page'])->name('public.downloads');
Route::get('/downloads/{id_template}', [PublicDownloadController::class, 'file'])->whereNumber('id_template')->name('public.downloads.file');
Route::get('/archive', [PublicArchiveController::class, 'page'])->name('public.archive');
Route::get('/archive/{id_event}', [PublicArchiveController::class, 'detailPage'])->whereNumber('id_event')->name('public.archive.show');

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
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->whereNumber('id')->middleware(['signed', 'throttle:10,1'])->name('verification.verify');
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
        Route::get('/', [SecretariatController::class, 'dashboard']);
        Route::get('/dashboard', [SecretariatController::class, 'dashboard'])->name('dashboard');
        Route::get('/abstracts', [SecretariatController::class, 'abstracts'])->name('abstracts.index');
        Route::get('/abstracts/{id}', [SecretariatController::class, 'showAbstract'])->whereNumber('id')->name('abstracts.show');
        Route::get('/reregistration', [SecretariatController::class, 'reregistration'])->name('reregistration');
        Route::get('/attendance', [SecretariatController::class, 'attendance'])->name('attendance');
        Route::get('/recap', [SecretariatController::class, 'recap'])->name('recap');
        Route::get('/announcements', [SecretariatController::class, 'announcements'])->name('announcements');
        Route::get('/content', [SecretariatController::class, 'content'])->name('content');
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
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/overview', [AdminController::class, 'overview'])->name('overview');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/permissions', [AdminController::class, 'permissions'])->name('permissions');
        Route::get('/signatures', [AdminController::class, 'signatures'])->middleware('permission:manage_digital_signatures,view_only')->name('signatures');
        Route::get('/templates', [AdminController::class, 'templates'])->name('templates');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('/content', [AdminController::class, 'content'])->middleware('permission:manage_website_content,view_only')->name('content');
        Route::get('/archive', [AdminController::class, 'archive'])->name('archive');
    });
});

// Frontend prototype routes are isolated from authentication and real portal routes.
if (app()->environment('local') && file_exists(__DIR__.'/fe-preview.php')) {
    require __DIR__.'/fe-preview.php';
}
