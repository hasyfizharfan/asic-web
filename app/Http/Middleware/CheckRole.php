<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Menangani permintaan masuk dan memverifikasi peran (role) pengguna.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Verifikasi apakah pengguna sudah terautentikasi
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->guest(route('login'))->withErrors([
                'email' => 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.',
            ]);
        }

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // 2. Verifikasi status akun pengguna
        if ($user->status_akun !== 'Aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda berstatus Nonaktif. Silakan hubungi Administrator.',
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda berstatus Nonaktif. Silakan hubungi Administrator.',
            ]);
        }

        // 3. Verifikasi peran (role) pengguna terhadap daftar peran yang diizinkan
        $userRole = $user->role?->nama_role;

        if (!$userRole || !in_array($userRole, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses fitur ini.',
                    'required_roles' => $roles,
                    'current_role' => $userRole,
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}

