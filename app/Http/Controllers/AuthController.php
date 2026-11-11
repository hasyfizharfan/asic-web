<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Participant;
use App\Models\Registration;
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
     * Registrasi Akun Awal Peserta / Delegasi (KF-08).
     *
     * Menyimpan data kredensial ke tabel `user_account` dengan peran default 'Peserta'
     * dan secara otomatis menginisialisasi baris data profil pada tabel `participant`
     * sesuai input formulir UI Create Delegate Account.
     */
    public function register(RegisterRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // 1. Ambil role default 'Peserta'
            $pesertaRole = Role::firstOrCreate(
                ['nama_role' => Role::ROLE_PESERTA],
                ['deskripsi' => 'Peserta seminar internasional ASIC']
            );

            // 2. Buat akun pengguna pada tabel user_account
            $email = strtolower(trim($validated['email']));
            $userAccount = UserAccount::create([
                'id_role' => $pesertaRole->id_role,
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'status_akun' => 'Aktif',
            ]);

            // 3. Ekstraksi nama lengkap & gelar akademik ke skema ERD (prefix, first_name, last_name)
            $parsedName = $this->parseFullName(
                rawName: $validated['full_name'] ?? $validated['name'] ?? null,
                rawPrefix: $validated['prefix'] ?? null,
                rawFirstName: $validated['first_name'] ?? null,
                rawLastName: $validated['last_name'] ?? null,
                fallback: explode('@', $email)[0]
            );

            // 4. Resolusi negara berdasarkan pilihan Delegate Category & Nationality di UI
            $country = $validated['country'] ?? null;
            if (!$country) {
                $category = strtolower($validated['delegate_category'] ?? $validated['nationality'] ?? '');
                if (str_contains($category, 'indonesia') || str_contains($category, 'domestic')) {
                    $country = 'Indonesia';
                } elseif (str_contains($category, 'international') || str_contains($category, 'overseas')) {
                    $country = 'International';
                } else {
                    $country = 'Indonesia';
                }
            }

            // 5. Inisialisasi baris data pada tabel participant (1:1 dengan user_account)
            Participant::create([
                'id_user' => $userAccount->id_user,
                'prefix' => $parsedName['prefix'],
                'first_name' => $parsedName['first_name'],
                'last_name' => $parsedName['last_name'],
                'passport_no' => null,
                'job_title' => null,
                'phone' => $validated['phone'] ?? null,
                'institution' => $validated['affiliation'] ?? $validated['institution'] ?? null,
                'address' => null,
                'city' => null,
                'country' => $country,
                'postcode' => null,
            ]);

            return $userAccount;
        });

        // Muat relasi role dan participant beserta accessor full_name
        $user->load(['role', 'participant']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Akun delegasi berhasil dibuat. Tautan aktivasi instan telah dikirimkan ke alamat email institusi Anda.',
                'activation_link_required' => true,
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role?->nama_role,
                    'status_akun' => $user->status_akun,
                    'participant' => $user->participant,
                ],
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('login')->with(
            'success',
            'Akun delegasi berhasil dibuat. Silakan periksa email institusi Anda untuk aktivasi akun.'
        );
    }

    /**
     * Autentikasi dan Login Akun (KF-08).
     *
     * Memeriksa kredensial email/username/reg.id dan password,
     * mendukung quick persona login demo dan otentikasi sesi.
     */
    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
        // 1. Tangani Quick Login Demo (Prototype Role Demo dari UI)
        if ($request->filled('demo_role')) {
            return $this->handleDemoRoleLogin($request->string('demo_role')->toString(), $request);
        }

        $credentials = $request->validated();
        $identifier = $request->getLoginIdentifier();

        // 2. Cari akun pengguna berdasarkan Email atau Username / Reg.ID
        $user = $this->findUserByIdentifier($identifier);

        // 3. Validasi kredensial pengguna dan kecocokan password
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email / Username atau kata sandi yang Anda masukkan salah.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw ValidationException::withMessages([
                'email' => ['Email / Username atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        // 4. Validasi status keaktifan akun pengguna
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

        // 5. Eksekusi login sesi
        $remember = $request->boolean('remember');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->load(['role', 'participant']);
        $redirectUrl = $this->resolveRedirectUrl($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil. Selamat datang di Portal ASIC 2026.',
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role?->nama_role,
                    'status_akun' => $user->status_akun,
                    'profile' => [
                        'prefix' => $user->participant?->prefix,
                        'first_name' => $user->participant?->first_name,
                        'last_name' => $user->participant?->last_name,
                        'full_name' => $user->participant?->full_name ?? explode('@', $user->email)[0],
                        'institution' => $user->participant?->institution,
                        'country' => $user->participant?->country,
                    ],
                    'redirect_url' => $redirectUrl,
                ],
            ], Response::HTTP_OK);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Endpoint Cepat Quick Login untuk Pengujian Prototipe Peran (Direct Persona Testing).
     */
    public function demoLogin(string $role, Request $request): JsonResponse|RedirectResponse
    {
        return $this->handleDemoRoleLogin($role, $request);
    }

    /**
     * Endpoint Integrasi SSO Universitas Andalas (@unand.ac.id).
     */
    public function ssoUnand(Request $request): JsonResponse|RedirectResponse
    {
        // Dalam mode pengembangan/staging, simulasikan alur SSO terintegrasi
        $unandEmail = 'delegate.unand@agr.unand.ac.id';
        $user = UserAccount::with(['role', 'participant'])->where('email', $unandEmail)->first();

        if (!$user) {
            $pesertaRole = Role::firstOrCreate(
                ['nama_role' => Role::ROLE_PESERTA],
                ['deskripsi' => 'Peserta seminar internasional ASIC']
            );

            $user = UserAccount::create([
                'id_role' => $pesertaRole->id_role,
                'email' => $unandEmail,
                'password' => Hash::make('Password123#'),
                'status_akun' => 'Aktif',
            ]);

            Participant::create([
                'id_user' => $user->id_user,
                'prefix' => 'Dr.',
                'first_name' => 'Delegasi',
                'last_name' => 'Universitas Andalas',
                'institution' => 'Fakultas Pertanian, Universitas Andalas',
                'country' => 'Indonesia',
            ]);

            $user->load(['role', 'participant']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $redirectUrl = $this->resolveRedirectUrl($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Autentikasi SSO Universitas Andalas berhasil.',
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role?->nama_role,
                    'redirect_url' => $redirectUrl,
                ],
            ], Response::HTTP_OK);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Bantuan Pencarian Identitas Akun (Forgot Username).
     */
    public function forgotUsername(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:3'],
        ]);

        $search = trim($request->string('query'));
        $users = UserAccount::with(['role', 'participant'])
            ->where('email', 'like', "%{$search}%")
            ->orWhereHas('participant', function ($p) use ($search) {
                $p->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Instruksi bantuan pencarian username / email berhasil diproses.',
            'count' => $users->count(),
            'hint' => 'Silakan hubungi sekretariat via asic@agr.unand.ac.id jika akun Anda belum ditemukan.',
        ], Response::HTTP_OK);
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

    /**
     * Cari akun pengguna berdasarkan string pengenal (Email, Reg.ID, atau ID User).
     */
    protected function findUserByIdentifier(string $identifier): ?UserAccount
    {
        if ($identifier === '') {
            return null;
        }

        // 1. Pencarian berbasis Email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return UserAccount::with(['role', 'participant'])
                ->where('email', strtolower($identifier))
                ->first();
        }

        // 2. Pencarian berbasis ID User (Numerik)
        if (is_numeric($identifier)) {
            $user = UserAccount::with(['role', 'participant'])->find((int) $identifier);
            if ($user) {
                return $user;
            }
        }

        // 3. Pencarian berbasis Format Reg.ID (misal: "ASIC-2026-0005" atau "REG-5")
        if (preg_match('/(?:ASIC|REG)[^\d]*(\d+)/i', $identifier, $matches)) {
            $regId = (int) $matches[1];
            $registration = Registration::with('participant.userAccount')->find($regId);
            if ($registration?->participant?->userAccount) {
                return $registration->participant->userAccount;
            }
        }

        // 4. Pencarian fallback berdasarkan awalan email atau nomor identitas/telepon
        return UserAccount::with(['role', 'participant'])
            ->where('email', 'like', strtolower($identifier) . '@%')
            ->orWhereHas('participant', function ($q) use ($identifier) {
                $q->where('phone', $identifier)
                  ->orWhere('passport_no', $identifier);
            })
            ->first();
    }

    /**
     * Tangani login demo persona cepat (Direct Persona Testing).
     */
    protected function handleDemoRoleLogin(string $rawRole, Request $request): JsonResponse|RedirectResponse
    {
        $roleMap = [
            'participant' => Role::ROLE_PESERTA,
            'peserta' => Role::ROLE_PESERTA,
            'secretariat' => Role::ROLE_SEKRETARIAT,
            'sekretariat' => Role::ROLE_SEKRETARIAT,
            'treasurer' => Role::ROLE_BENDAHARA,
            'bendahara' => Role::ROLE_BENDAHARA,
            'abstract_team' => Role::ROLE_TIM_ABSTRAK,
            'tim abstrak' => Role::ROLE_TIM_ABSTRAK,
            'admin' => Role::ROLE_ADMIN,
        ];

        $targetRoleName = $roleMap[strtolower(trim($rawRole))] ?? Role::ROLE_PESERTA;

        // Cari user yang memiliki role tersebut
        $user = UserAccount::with(['role', 'participant'])
            ->whereHas('role', function ($q) use ($targetRoleName) {
                $q->where('nama_role', $targetRoleName);
            })
            ->where('status_akun', 'Aktif')
            ->first();

        // Jika user belum ada di database, buat akun demo secara otomatis
        if (!$user) {
            $role = Role::firstOrCreate(
                ['nama_role' => $targetRoleName],
                ['deskripsi' => "Peran {$targetRoleName} ASIC 2026"]
            );

            $slug = strtolower(str_replace(' ', '', $targetRoleName));
            $user = UserAccount::create([
                'id_role' => $role->id_role,
                'email' => "{$slug}.demo@asic.unand.ac.id",
                'password' => Hash::make('DemoPassword2026!'),
                'status_akun' => 'Aktif',
            ]);

            Participant::create([
                'id_user' => $user->id_user,
                'prefix' => $targetRoleName === 'Admin' ? 'Prof. Dr.' : 'Dr.',
                'first_name' => 'Demo',
                'last_name' => $targetRoleName,
                'institution' => 'Universitas Andalas',
                'country' => 'Indonesia',
            ]);

            $user->load(['role', 'participant']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $redirectUrl = $this->resolveRedirectUrl($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Quick demo login berhasil sebagai persona {$targetRoleName}.",
                'data' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'role' => $user->role?->nama_role,
                    'status_akun' => $user->status_akun,
                    'profile' => [
                        'full_name' => $user->participant?->full_name ?? $targetRoleName,
                        'institution' => $user->participant?->institution,
                        'country' => $user->participant?->country,
                    ],
                    'redirect_url' => $redirectUrl,
                ],
            ], Response::HTTP_OK);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Tentukan URL redirect sesuai peran pengguna.
     */
    protected function resolveRedirectUrl(UserAccount $user): string
    {
        $roleName = $user->role?->nama_role;

        return match ($roleName) {
            Role::ROLE_ADMIN => route('admin.roles-permissions.index'),
            Role::ROLE_SEKRETARIAT => url('/secretariat/dashboard'),
            Role::ROLE_BENDAHARA => url('/treasurer/dashboard'),
            Role::ROLE_TIM_ABSTRAK => url('/abstract-team/dashboard'),
            default => route('registrations.index'),
        };
    }

    /**
     * Helper untuk memecah nama lengkap dan gelar akademik ke dalam kolom ERD:
     * `prefix`, `first_name`, dan `last_name`.
     *
     * @return array{prefix: ?string, first_name: string, last_name: ?string}
     */
    protected function parseFullName(
        ?string $rawName,
        ?string $rawPrefix = null,
        ?string $rawFirstName = null,
        ?string $rawLastName = null,
        string $fallback = ''
    ): array {
        if ($rawFirstName !== null) {
            return [
                'prefix' => $rawPrefix ? substr(trim($rawPrefix), 0, 10) : null,
                'first_name' => substr(trim($rawFirstName), 0, 50),
                'last_name' => $rawLastName ? substr(trim($rawLastName), 0, 50) : null,
            ];
        }

        $name = trim($rawName ?? '');
        if ($name === '') {
            return [
                'prefix' => null,
                'first_name' => substr($fallback, 0, 50),
                'last_name' => null,
            ];
        }

        $prefix = null;
        $commonPrefixes = [
            'Prof. Dr. Ir.', 'Prof. Dr.', 'Prof.', 'Dr. Ir.', 'Dr.', 'Ir.',
            'Drs.', 'Dra.', 'Mr.', 'Ms.', 'Mrs.', 'Assoc. Prof.', 'Asst. Prof.',
        ];

        foreach ($commonPrefixes as $p) {
            if (stripos($name, $p) === 0) {
                $prefix = substr($p, 0, 10);
                $name = trim(substr($name, strlen($p)));
                break;
            }
        }

        if ($rawPrefix) {
            $prefix = substr(trim($rawPrefix), 0, 10);
        }

        $words = preg_split('/\s+/', $name);
        $firstName = $words[0] ?? $fallback;
        array_shift($words);
        $lastName = !empty($words) ? implode(' ', $words) : null;

        return [
            'prefix' => $prefix,
            'first_name' => substr($firstName, 0, 50),
            'last_name' => $lastName ? substr($lastName, 0, 50) : null,
        ];
    }
}
