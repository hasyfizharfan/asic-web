<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dokumentasi API (Swagger) hanya untuk pengembangan. Di production disembunyikan (404) agar peta
 * seluruh endpoint tidak terbuka untuk umum, kecuali sengaja dibuka lewat ASIC_PUBLIC_API_DOCS=true.
 */
class RestrictApiDocs
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && ! config('asic.public_api_docs')) {
            abort(404);
        }

        return $next($request);
    }
}
