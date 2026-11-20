<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menentukan bahasa per request: session('locale') (hanya 'id' atau 'en') → config('app.locale').
 * Membuat pilihan /lang/{locale} persisten di request berikutnya, termasuk API (sesi Sanctum).
 */
class SetLocale
{
    public const SUPPORTED = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;
        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = in_array(config('app.locale'), self::SUPPORTED, true) ? config('app.locale') : 'id';
        }
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
