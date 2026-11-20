<?php

use App\Console\Commands\MigrateAbstractFilesToPrivateDisk;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([MigrateAbstractFilesToPrivateDisk::class])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // Bahasa persisten dari sesi (KF-31). Untuk api, sesi tersedia lewat statefulApi (Sanctum).
        $middleware->web(append: [SetLocale::class]);
        $middleware->api(append: [SetLocale::class]);
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Batas percobaan (login, daftar, lupa sandi): pesan dua bahasa, bukan "Too Many Attempts."
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson()) {
                $wait = (int) ($e->getHeaders()['Retry-After'] ?? 60);

                return response()->json(['success' => false, 'message' => __('asic-api.too_many_attempts', ['seconds' => $wait])], 429, $e->getHeaders());
            }
        });
    })->create();
