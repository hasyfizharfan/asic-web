<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
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
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Registrasi Akun Awal Peserta / Delegasi (KF-08).
     */
    #[OA\Post(
        path: '/api/register',
        summary: 'Registrasi Akun Awal Peserta / Delegasi',
        description: 'Mendaftarkan akun baru ke tabel user_account dengan role Peserta serta membuat data profil participant.',
        tags: ['Autentikasi'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Data formulir registrasi delegasi',
            content: new OA\JsonContent(
                required: ['email', 'password', 'password_confirmation', 'full_name'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'author@univ.ac.id'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Secret123#'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Secret123#'),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Dr. John Doe, M.Sc.'),
                    new OA\Property(property: 'prefix', type: 'string', example: 'Dr.'),
                    new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'phone', type: 'string', example: '+628123456789'),
                    new OA\Property(property: 'affiliation', type: 'string', example: 'Universitas Andalas'),
                    new OA\Property(property: 'country', type: 'string', example: 'Indonesia'),
                    new OA\Property(property: 'delegate_category', type: 'string', example: 'Domestic Presenter')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Akun delegasi berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Akun delegasi berhasil dibuat.'),
                        new OA\Property(property: 'activation_link_required', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validasi input gagal'
            )
        ]
    )]
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
     */
    #[OA\Post(
        path: '/api/login',
        summary: 'Autentikasi dan Login Akun Pengguna',
        description: 'Memverifikasi kredensial email/username dan kata sandi untuk login sistem.',
        tags: ['Autentikasi'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'delegate@asic.unand.ac.id'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'remember', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login berhasil.'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Akun berstatus nonaktif'
            ),
            new OA\Response(
                response: 422,
                description: 'Kredensial tidak valid'
            )
        ]
    )]
    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
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
     * Bantuan Pencarian Identitas Akun (Forgot Username).
     */
    #[OA\Post(
        path: '/api/password/forgot-username',
        summary: 'Bantuan Pencarian Username / Identitas Akun',
        description: 'Mencari petunjuk email terdaftar berdasarkan nomor telepon atau nama peserta.',
        tags: ['Autentikasi'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['query'],
                properties: [
                    new OA\Property(property: 'query', type: 'string', example: 'John Doe')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hasil pencarian diproses',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'count', type: 'integer'),
                        new OA\Property(property: 'hint', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Format kueri tidak valid')
        ]
    )]
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
    #[OA\Post(
        path: '/api/logout',
        summary: 'Logout Akun Pengguna',
        description: 'Mengakhiri sesi aktif pengguna dan menginvalidasi token sesi.',
        tags: ['Autentikasi'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logout berhasil.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Belum terautentikasi')
        ]
    )]
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
    #[OA\Get(
        path: '/api/me',
        summary: 'Profil Pengguna Saat Ini',
        description: 'Mengembalikan informasi akun dan profil peserta yang sedang terautentikasi.',
        tags: ['Autentikasi'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Belum terautentikasi')
        ]
    )]
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
     * Tentukan URL redirect sesuai peran pengguna.
     */
    protected function resolveRedirectUrl(UserAccount $user): string
    {
        $roleName = $user->role?->nama_role;

        return match ($roleName) {
            Role::ROLE_ADMIN => route('api.admin.roles-permissions.index'),
            Role::ROLE_SEKRETARIAT => url('/secretariat/dashboard'),
            Role::ROLE_BENDAHARA => url('/treasurer/dashboard'),
            Role::ROLE_TIM_ABSTRAK => url('/abstract-team/dashboard'),
            default => route('api.registrations.index'),
        };
    }

    /**
     * Helper untuk memecah nama lengkap dan gelar akademik ke dalam kolom ERD.
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
