<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Section 6: Session Driver Guard (Protecting BE Database)
|--------------------------------------------------------------------------
| Laravel 11+ defaults to database session driver. If database/redis/memcached
| is active, block preview execution so dummy sessions do not pollute DB.
*/
if (in_array(config('session.driver'), ['database', 'redis', 'memcached'])) {
    Route::any('/_fe{any}', function () {
        return response('
            <div style="font-family:Segoe UI,Roboto,sans-serif;max-width:620px;margin:80px auto;padding:32px;border:2px solid #ba1a1a;border-radius:20px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,0.08);color:#1b1c1c;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:#ffdad6;color:#ba1a1a;display:grid;place-items:center;font-weight:bold;font-size:20px;">!</div>
                    <h2 style="color:#ba1a1a;margin:0;font-size:20px;">Konfigurasi Session Diperlukan (SESSION_DRIVER)</h2>
                </div>
                <p style="font-size:14px;line-height:1.6;color:#444;">Sesuai aturan isolasi frontend, route preview FE tidak boleh menulis data sesi simulasi ke database backend.</p>
                <p style="font-size:14px;color:#444;">Silakan sesuaikan konfigurasi pada file <code>.env</code> lokal Anda:</p>
                <pre style="background:#f4f4f4;padding:12px 16px;border-radius:10px;font-weight:bold;font-size:14px;color:#005226;border:1px solid #ddd;">SESSION_DRIVER=file</pre>
                <p style="font-size:13px;color:#666;line-height:1.5;">Setelah baris di atas disimpan di <code>.env</code>, silakan muat ulang halaman ini.</p>
            </div>
        ', 503);
    })->where('any', '.*');
    return;
}

// Temporary FE Auth Simulation Middlewares
if (!class_exists('FeParticipantAuthMiddleware')) {
    class FeParticipantAuthMiddleware {
        public function handle($request, \Closure $next) {
            if ($request->has('type') && in_array($request->query('type'), ['oral', 'poster', 'listener', 'presenter'])) {
                $mode = $request->query('type');
                if ($mode === 'presenter') {
                    $mode = 'oral';
                }
                session(['participant_type' => $mode]);
                session()->save();
            }
            if (!session('logged_in_participant')) {
                return redirect()->route('fe.participant.login');
            }
            return $next($request);
        }
    }
}

if (!class_exists('FeReviewerAuthMiddleware')) {
    class FeReviewerAuthMiddleware {
        public function handle($request, \Closure $next) {
            if (!session('logged_in_reviewer')) {
                return redirect()->route('fe.reviewer.login');
            }
            return $next($request);
        }
    }
}

if (!class_exists('FeSecretariatAuthMiddleware')) {
    class FeSecretariatAuthMiddleware {
        public function handle($request, \Closure $next) {
            if (!session('logged_in_secretariat')) {
                return redirect()->route('fe.secretariat.login');
            }
            return $next($request);
        }
    }
}

if (!class_exists('FeTreasurerAuthMiddleware')) {
    class FeTreasurerAuthMiddleware {
        public function handle($request, \Closure $next) {
            if (!session('logged_in_treasurer')) {
                return redirect()->route('fe.treasurer.login');
            }
            return $next($request);
        }
    }
}

if (!class_exists('FeAdminAuthMiddleware')) {
    class FeAdminAuthMiddleware {
        public function handle($request, \Closure $next) {
            if (!session('logged_in_admin')) {
                return redirect()->route('fe.admin.login');
            }
            return $next($request);
        }
    }
}

if (!class_exists('FeLocaleMiddleware')) {
    class FeLocaleMiddleware {
        public function handle($request, \Closure $next) {
            // Locale isolation inside preview group only
            if ($request->has('lang') && in_array($request->query('lang'), ['en', 'id'])) {
                session(['locale' => $request->query('lang')]);
            }
            $locale = session('locale', config('app.locale', 'en'));
            app()->setLocale($locale);
            View::share('currentLocale', $locale);
            return $next($request);
        }
    }
}

// Global dummy conference settings shared to all FE views
View::share('asic', [
    'year' => '2027',
    'edition' => '3rd',
    'full_title' => 'Agrifood System International Conference',
    'short_title' => 'ASIC 2027',
    'theme' => 'Innovations in Resilient and Sustainable Agrifood Systems',
    'dates' => 'August 24–26, 2027',
    'venue' => 'Convention Hall, Universitas Andalas, Padang, Indonesia',
    'city' => 'Padang, West Sumatra',
    'organizer' => 'Faculty of Agriculture, Universitas Andalas',
    'email' => 'asic@agr.unand.ac.id',
    'whatsapp' => '+62 812-3456-7890',
    'phone' => '+62 751 72773',
    'countdown_status' => 'DATES TBA / COUNTDOWN PENDING',
    'fee_student_id' => 'IDR TBA',
    'fee_student_intl' => 'USD TBA',
    'fee_regular_id' => 'IDR TBA',
    'fee_regular_intl' => 'USD TBA',
]);

/*
|--------------------------------------------------------------------------
| View Composers for Portal Navigation & Roles (Prefixed fe.* & asic-*)
|--------------------------------------------------------------------------
*/
View::composer(['participant.*', 'layouts.portal'], function ($view) {
    if (request()->has('type') && in_array(request()->query('type'), ['oral', 'poster', 'listener', 'presenter'])) {
        $mode = request()->query('type');
        if ($mode === 'presenter') {
            $mode = 'oral';
        }
        session(['participant_type' => $mode]);
        session()->save();
    }

    $participantType = session('participant_type', 'oral');
    $isListener = ($participantType === 'listener');

    $participantNav = [
        ['label' => __('asic-nav.dashboard'), 'route' => 'fe.participant.dashboard', 'icon' => 'space_dashboard'],
    ];
    if (!$isListener) {
        $participantNav[] = ['label' => __('asic-nav.my_abstract'), 'route' => 'fe.participant.abstract', 'icon' => 'description'];
    }
    $participantNav[] = ['label' => __('asic-nav.payment'), 'route' => 'fe.participant.payment.checkout', 'active_pattern' => '_fe/participant/payment*', 'icon' => 'payments'];
    $participantNav[] = ['label' => __('asic-nav.documents'), 'route' => 'fe.participant.documents', 'icon' => 'folder'];
    $participantNav[] = ['label' => __('asic-nav.attendance_qr'), 'route' => 'fe.participant.attendance-qr', 'icon' => 'qr_code_2'];
    if (!$isListener) {
        $participantNav[] = ['label' => __('asic-nav.full_paper'), 'route' => 'fe.participant.full-paper', 'icon' => 'upload_file'];
    }
    $participantNav[] = ['label' => __('asic-nav.certificate'), 'route' => 'fe.participant.certificate', 'icon' => 'workspace_premium'];
    $participantNav[] = ['label' => __('asic-nav.notifications'), 'route' => 'fe.participant.notifications', 'icon' => 'notifications', 'badge' => '2'];
    $participantNav[] = ['label' => __('asic-nav.profile'), 'route' => 'fe.participant.profile', 'icon' => 'account_circle'];

    $roleName = match($participantType) {
        'listener' => __('asic-participant.role_listener'),
        'poster' => __('asic-participant.role_poster'),
        default => __('asic-participant.role_oral'),
    };

    $view->with([
        'isListener' => $isListener,
        'participantType' => $participantType,
        'participantNav' => $participantNav,
        'roleName' => $roleName,
    ]);
});

View::share('blindReview', true);

View::composer('reviewer.*', function ($view) {
    $reviewerNav = [
        ['label' => __('asic-reviewer.queue_title') ?? 'Review Queue', 'route' => 'fe.reviewer.queue', 'active_pattern' => '_fe/reviewer*', 'icon' => 'rate_review'],
        ['label' => __('asic-reviewer.profile_title') ?? 'Profile', 'route' => 'fe.reviewer.profile', 'icon' => 'account_circle'],
    ];
    $view->with([
        'reviewerNav' => $reviewerNav,
        'roleName' => __('asic-reviewer.portal_title') ?? 'Abstract Reviewer',
        'userName' => session('reviewer_name', 'Dr. Sarah Jenkins, Ph.D.'),
    ]);
});

View::composer('secretariat.*', function ($view) {
    $secNav = [
        ['label' => __('asic-nav.abstracts'), 'route' => 'fe.secretariat.abstracts.index', 'active_pattern' => '_fe/secretariat/abstracts*', 'icon' => 'description'],
        ['label' => __('asic-nav.reregistration'), 'route' => 'fe.secretariat.reregistration', 'icon' => 'qr_code_scanner'],
        ['label' => __('asic-nav.attendance_presentation'), 'route' => 'fe.secretariat.attendance', 'icon' => 'how_to_reg'],
        ['label' => __('asic-nav.recap'), 'route' => 'fe.secretariat.recap', 'icon' => 'bar_chart'],
        ['label' => __('asic-nav.announcements'), 'route' => 'fe.secretariat.announcements', 'icon' => 'campaign'],
        ['label' => __('asic-nav.website_content'), 'route' => 'fe.secretariat.content', 'icon' => 'public'],
    ];
    $view->with('secNav', $secNav);
});

View::composer('treasurer.*', function ($view) {
    $treasurerNav = [
        ['label' => __('asic-nav.tariffs'), 'route' => 'fe.treasurer.tariffs', 'icon' => 'price_change'],
        ['label' => __('asic-nav.payment_queue'), 'route' => 'fe.treasurer.payments.index', 'active_pattern' => '_fe/treasurer/payments*', 'icon' => 'receipt_long'],
        ['label' => __('asic-nav.refund_requests'), 'route' => 'fe.treasurer.refunds', 'icon' => 'currency_exchange'],
    ];
    $view->with('treasurerNav', $treasurerNav);
});

View::composer('admin.*', function ($view) {
    $adminNav = [
        ['label' => __('asic-nav.overview'), 'route' => 'fe.admin.overview', 'icon' => 'dashboard'],
        ['label' => __('asic-nav.users_roles'), 'route' => 'fe.admin.users', 'icon' => 'group'],
        ['label' => __('asic-nav.permissions'), 'route' => 'fe.admin.permissions', 'icon' => 'verified_user'],
        ['label' => __('asic-nav.document_templates'), 'route' => 'fe.admin.templates', 'icon' => 'description'],
        ['label' => __('asic-nav.website_settings'), 'route' => 'fe.admin.settings', 'icon' => 'settings'],
        ['label' => __('asic-nav.archive'), 'route' => 'fe.admin.archive', 'icon' => 'inventory_2'],
    ];
    $view->with('adminNav', $adminNav);
});

/*
|--------------------------------------------------------------------------
| Section 2 & 3: Master Route Group with /_fe prefix & fe. route names
|--------------------------------------------------------------------------
*/
Route::prefix('_fe')->name('fe.')->middleware(FeLocaleMiddleware::class)->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Landing Page & Utilities
    |--------------------------------------------------------------------------
    */
    Route::get('/', function () {
        return view('public.landing');
    })->name('home');

    Route::get('/lang/{locale}', function ($locale) {
        if (in_array($locale, ['en', 'id'])) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }
        return redirect()->back();
    })->name('lang.switch');

    Route::get('/_ui', function () {
        return view('dev.ui-gallery');
    })->name('dev.ui');

    // Error pages preview (/ _fe / errors / {code})
    Route::get('/errors/{code}', function ($code) {
        $allowedCodes = ['401', '403', '404', '419', '429', '500'];
        if (in_array($code, $allowedCodes) && view()->exists("asic.errors.{$code}")) {
            return response()->view("asic.errors.{$code}", [], (int)$code);
        }
        abort(404);
    })->name('error');

    // Author Template Download (served safely via resource_path)
    Route::get('/templates/author', function () {
        $templatePath = resource_path('private/templates/iop-conference-template.docx');
        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'ASIC-2027-IOP-Author-Template.docx');
        }
        return response('Template file not found in resources/private/templates', 404);
    })->name('template.author');

    /*
    |--------------------------------------------------------------------------
    | Auth Simulation Routes (Login / Logout)
    |--------------------------------------------------------------------------
    */
    Route::match(['get', 'post'], '/participant/login', function () {
        if (request()->isMethod('post') || request()->has('action')) {
            session(['logged_in_participant' => true]);
            if (request()->has('type') && in_array(request('type'), ['oral', 'poster', 'listener'])) {
                session(['participant_type' => request('type')]);
            }
            return redirect()->route('fe.participant.dashboard');
        }
        if (session('logged_in_participant')) {
            return redirect()->route('fe.participant.dashboard');
        }
        return view('auth.participant');
    })->name('participant.login');

    Route::get('/participant/logout', function () {
        session()->forget('logged_in_participant');
        return redirect()->route('fe.participant.login');
    })->name('participant.logout');

    Route::match(['get', 'post'], '/reviewer/login', function () {
        if (request()->isMethod('post') || request()->has('action')) {
            session(['logged_in_reviewer' => true]);
            return redirect()->route('fe.reviewer.queue');
        }
        if (session('logged_in_reviewer')) {
            return redirect()->route('fe.reviewer.queue');
        }
        return view('auth.reviewer');
    })->name('reviewer.login');

    Route::get('/reviewer/logout', function () {
        session()->forget('logged_in_reviewer');
        return redirect()->route('fe.reviewer.login');
    })->name('reviewer.logout');

    Route::match(['get', 'post'], '/secretariat/login', function () {
        if (request()->isMethod('post') || request()->has('action')) {
            session(['logged_in_secretariat' => true]);
            return redirect()->route('fe.secretariat.abstracts.index');
        }
        if (session('logged_in_secretariat')) {
            return redirect()->route('fe.secretariat.abstracts.index');
        }
        return view('auth.secretariat');
    })->name('secretariat.login');

    Route::get('/secretariat/logout', function () {
        session()->forget('logged_in_secretariat');
        return redirect()->route('fe.secretariat.login');
    })->name('secretariat.logout');

    Route::match(['get', 'post'], '/treasurer/login', function () {
        if (request()->isMethod('post') || request()->has('action')) {
            session(['logged_in_treasurer' => true]);
            return redirect()->route('fe.treasurer.tariffs');
        }
        if (session('logged_in_treasurer')) {
            return redirect()->route('fe.treasurer.tariffs');
        }
        return view('auth.treasurer');
    })->name('treasurer.login');

    Route::get('/treasurer/logout', function () {
        session()->forget('logged_in_treasurer');
        return redirect()->route('fe.treasurer.login');
    })->name('treasurer.logout');

    Route::match(['get', 'post'], '/admin/login', function () {
        if (request()->isMethod('post') || request()->has('action')) {
            session(['logged_in_admin' => true]);
            return redirect()->route('fe.admin.overview');
        }
        if (session('logged_in_admin')) {
            return redirect()->route('fe.admin.overview');
        }
        return view('auth.admin');
    })->name('admin.login');

    Route::get('/admin/logout', function () {
        session()->forget('logged_in_admin');
        return redirect()->route('fe.admin.login');
    })->name('admin.logout');

    /*
    |--------------------------------------------------------------------------
    | Portal 1: Participant Routes (Fase 3)
    |--------------------------------------------------------------------------
    */
    Route::prefix('participant')->name('participant.')->middleware(FeParticipantAuthMiddleware::class)->group(function () {
        Route::get('/switch-track/{type}', function ($type) {
            if (in_array($type, ['oral', 'poster', 'listener'])) {
                session(['participant_type' => $type]);
                session()->save();
            }
            return redirect()->back();
        })->name('switch-track');

        Route::get('/dashboard', function () {
            return view('participant.dashboard');
        })->name('dashboard');

        Route::get('/registration', function () {
            return view('participant.registration');
        })->name('registration');

        Route::get('/abstract', function () {
            return view('participant.abstract');
        })->name('abstract');

        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/checkout', function () {
                return view('participant.payment.checkout');
            })->name('checkout');

            Route::get('/paypal', function () {
                return view('participant.payment.paypal');
            })->name('paypal');

            Route::get('/status', function () {
                return view('participant.payment.status');
            })->name('status');
        });

        Route::get('/refund', function () {
            return view('participant.refund');
        })->name('refund');

        Route::get('/documents', function () {
            return view('participant.documents');
        })->name('documents');

        Route::get('/attendance-qr', function () {
            return view('participant.attendance-qr');
        })->name('attendance-qr');

        Route::get('/full-paper', function () {
            return view('participant.full-paper');
        })->name('full-paper');

        Route::get('/certificate', function () {
            return view('participant.certificate');
        })->name('certificate');

        Route::get('/notifications', function () {
            return view('participant.notifications');
        })->name('notifications');

        Route::get('/profile', function () {
            return view('participant.profile');
        })->name('profile');
    });

    /*
    |--------------------------------------------------------------------------
    | Portal: Reviewer Routes (Role Baru: Reviewer Abstrak)
    |--------------------------------------------------------------------------
    */
    Route::prefix('reviewer')->name('reviewer.')->middleware(FeReviewerAuthMiddleware::class)->group(function () {
        Route::get('/queue', function () {
            return view('reviewer.queue');
        })->name('queue');

        Route::get('/abstracts/{id}', function ($id) {
            return view('reviewer.detail', ['id' => $id]);
        })->name('detail');

        Route::post('/abstracts/{id}/review', function ($id) {
            return redirect()->route('fe.reviewer.detail', ['id' => $id, 'submitted' => 1]);
        })->name('submit');

        Route::get('/profile', function () {
            return view('reviewer.profile');
        })->name('profile');
    });

    /*
    |--------------------------------------------------------------------------
    | Portal 2: Secretariat Routes (Fase 4)
    |--------------------------------------------------------------------------
    */
    Route::prefix('secretariat')->name('secretariat.')->middleware(FeSecretariatAuthMiddleware::class)->group(function () {
        Route::get('/abstracts', function () {
            return view('secretariat.abstracts.index');
        })->name('abstracts.index');

        Route::get('/abstracts/{id}', function ($id) {
            return view('secretariat.abstracts.show', ['id' => $id]);
        })->name('abstracts.show');

        Route::get('/reregistration', function () {
            return view('secretariat.reregistration');
        })->name('reregistration');

        Route::get('/attendance', function () {
            return view('secretariat.attendance');
        })->name('attendance');

        Route::get('/recap', function () {
            return view('secretariat.recap');
        })->name('recap');

        Route::get('/announcements', function () {
            return view('secretariat.announcements');
        })->name('announcements');

        Route::get('/content', function () {
            return view('secretariat.content');
        })->name('content');
    });

    /*
    |--------------------------------------------------------------------------
    | Portal 3: Treasurer Routes (Fase 5)
    |--------------------------------------------------------------------------
    */
    Route::prefix('treasurer')->name('treasurer.')->middleware(FeTreasurerAuthMiddleware::class)->group(function () {
        Route::get('/tariffs', function () {
            return view('treasurer.tariffs');
        })->name('tariffs');

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', function () {
                return view('treasurer.payments.index');
            })->name('index');

            Route::get('/{id}', function ($id) {
                return view('treasurer.payments.show', ['id' => $id]);
            })->name('show');
        });

        Route::get('/refunds', function () {
            return view('treasurer.refunds');
        })->name('refunds');

        Route::get('/receipts/preview', function () {
            return view('treasurer.receipts.preview');
        })->name('receipts.preview');
    });

    /*
    |--------------------------------------------------------------------------
    | Portal 4: Admin Routes (Fase 6)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware(FeAdminAuthMiddleware::class)->group(function () {
        Route::get('/overview', function () {
            return view('admin.overview');
        })->name('overview');

        Route::get('/users', function () {
            return view('admin.users');
        })->name('users');

        Route::get('/permissions', function () {
            return view('admin.permissions');
        })->name('permissions');

        Route::get('/templates', function () {
            return view('admin.templates');
        })->name('templates');

        Route::get('/settings', function () {
            return view('admin.settings');
        })->name('settings');

        Route::get('/archive', function () {
            return view('admin.archive');
        })->name('archive');
    });

});
