<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Participant;
use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Registrasi Akun Awal Peserta (KF-08).
     *
     * Menyimpan data kredensial ke tabel `user_account` dengan peran default 'Peserta'
     * dan secara otomatis menginisialisasi baris data dasar pada tabel `participant`.
     */
    public function register(RegisterRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // Ambil role default 'Peserta'
            $pesertaRole = Role::firstOrCreate(
                ['nama_role' => Role::ROLE_PESERTA],
                ['deskripsi' => 'Peserta seminar internasional ASIC']
            );

            // Buat akun pengguna baru
            $userAccount = UserAccount::create([
                'id_role' => $pesertaRole->id_role,
                'email' => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
                'status_akun' => 'Aktif',
            ]);

            // Inisialisasi baris profil dasar pada tabel participant
            Participant::create([
                'id_user' => $userAccount->id_user,
                'prefix' => $validated['prefix'] ?? null,
                'first_name' => $validated['first_name'] ?? explode('@', $validated['email'])[0],
                'last_name' => $validated['last_name'] ?? null,
                'passport_no' => null,
                'job_title' => null,
                'phone' => $validated['phone'] ?? null,
                'institution' => $validated['institution'] ?? null,
                'address' => null,
                'city' => null,
                'country' => null,
                'postcode' => null,
            ]);

            return $userAccount;
        });

        // Muat relasi untuk respons
        $user->load(['role', 'participant']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrasi akun berhasil. Silakan login ke sistem.',
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role->nama_role,
                    'status_akun' => $user->status_akun,
                    'participant' => $user->participant,
                ],
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan login dengan akun Anda.');
    }

    /**
     * Autentikasi dan Login Akun (KF-08).
     *
     * Memeriksa kredensial email dan password serta memastikan `status_akun` bernilai 'Aktif'.
     */
    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $credentials = $request->validated();
        $email = strtolower(trim($credentials['email']));

        /** @var UserAccount|null $user */
        $user = UserAccount::with('role')->where('email', $email)->first();

        // 1. Validasi kredensial email & password
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau kata sandi yang Anda masukkan salah.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        // 2. Validasi status keaktifan akun
        if (!$user->isActive()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda berstatus Nonaktif. Silakan hubungi Administrator.',
                ], Response::HTTP_FORBIDDEN);
            }

            throw ValidationException::withMessages([
                'email' => ['Akun Anda berstatus Nonaktif. Silakan hubungi Administrator.'],
            ]);
        }

        // 3. Login session
        $remember = $request->boolean('remember');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role?->nama_role,
                    'status_akun' => $user->status_akun,
                ],
            ], Response::HTTP_OK);
        }

        // Redirect sesuai peran pengguna
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.users.index'));
        }

        return redirect()->intended('/');
    }

    /**
     * Logout Akun Pengguna.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.',
            ], Response::HTTP_OK);
        }

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Mendapatkan profil pengguna yang sedang login saat ini.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var UserAccount $user */
        $user = Auth::user();
        $user->load(['role', 'participant']);

        return response()->json([
            'success' => true,
            'data' => [
                'id_user' => $user->id_user,
                'email' => $user->email,
                'status_akun' => $user->status_akun,
                'role' => $user->role?->nama_role,
                'participant' => $user->participant,
            ],
        ], Response::HTTP_OK);
    }
}

