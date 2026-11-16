<?php

use App\Console\Commands\MigrateAbstractFilesToPrivateDisk;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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

        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
