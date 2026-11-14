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
            return response()->json(['success' => false, 'message' => 'Akses ditolak berdasarkan matriks hak akses.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
