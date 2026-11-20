<?php

namespace App\Http\Middleware;

use App\Models\UserAccount;
use App\Support\PermissionMatrix;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission, string $minimum = 'full_access'): Response
    {
        $user = Auth::user();

        if (! ($user instanceof UserAccount) || ! app(PermissionMatrix::class)->allows($user, $permission, $minimum)) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                // dibuka langsung lewat browser: tampilkan halaman, bukan JSON mentah
                return response()->view('errors.forbidden', [], Response::HTTP_FORBIDDEN);
            }

            return response()->json(['success' => false, 'message' => __('asic-api.forbidden')], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
