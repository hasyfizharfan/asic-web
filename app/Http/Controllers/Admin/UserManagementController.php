<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Admin\UserManagementResource;
use App\Models\AuditLog;
use App\Models\Participant;
use App\Models\Role;
use App\Models\UserAccount;
use App\Support\PermissionMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    /**
     * Menampilkan dashboard User & Role Management lengkap (KF-09).
     */
    #[OA\Get(
        path: '/api/admin/users',
        summary: 'Daftar Pengguna dan Metrik Tata Kelola Akun',
        description: 'Menampilkan data pengguna terpaginasi, metrik ringkasan peran, dan matriks tata kelola (Khusus Admin).',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'role', in: 'query', required: false, description: 'Filter nama peran', schema: new OA\Schema(type: 'string', example: 'Peserta')),
            new OA\Parameter(name: 'id_role', in: 'query', required: false, description: 'Filter ID peran', schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filter status akun (active / inactive)', schema: new OA\Schema(type: 'string', example: 'active')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Pencarian multifield (nama, email, instansi, dll)', schema: new OA\Schema(type: 'string', example: 'Andalas')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman (default: 15)', schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data pengguna dan tata kelola akun berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'summary', type: 'object'),
                        new OA\Property(property: 'governance_matrix', type: 'object'),
                        new OA\Property(property: 'available_roles', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'users', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'pagination', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak (khusus Admin)'),
        ]
    )]
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $query = UserAccount::with(['role', 'participant']);

        if ($request->filled('role')) {
            $roleParam = strtolower(trim($request->string('role')));
            $query->whereHas('role', function ($q) use ($roleParam) {
                $q->where(function ($sub) use ($roleParam) {
                    $sub->where('nama_role', 'like', "%{$roleParam}%")
                        ->orWhere('id_role', is_numeric($roleParam) ? (int) $roleParam : 0);
                });
            });
        } elseif ($request->filled('id_role')) {
            $query->where('id_role', $request->integer('id_role'));
        }

        if ($request->filled('status')) {
            $statusParam = strtolower(trim($request->string('status')));
            $statusDb = in_array($statusParam, ['active', 'aktif'], true) ? 'Aktif' : 'Nonaktif';
            $query->where('status_akun', $statusDb);
        } elseif ($request->filled('status_akun')) {
            $query->where('status_akun', $request->string('status_akun'));
        }

        if ($request->filled('search')) {
            $search = '%'.trim($request->string('search')).'%';
            $driver = DB::connection()->getDriverName();
            $fullName = match ($driver) {
                'mysql' => "CONCAT(COALESCE(prefix, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))",
                'sqlite' => "COALESCE(prefix, '') || ' ' || COALESCE(first_name, '') || ' ' || COALESCE(last_name, '')",
                default => "COALESCE(prefix, '') || ' ' || COALESCE(first_name, '') || ' ' || COALESCE(last_name, '')",
            };
            $query->where(function ($q) use ($search, $fullName) {
                $q->where('email', 'like', $search)
                    ->orWhereHas('participant', function ($p) use ($search) {
                        $p->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('institution', 'like', $search)
                            ->orWhere('passport_no', 'like', $search)
                            ->orWhere('job_title', 'like', $search)
                            ->orWhere('phone', 'like', $search);
                    })->orWhereHas('participant', fn ($p) => $p->whereRaw("TRIM({$fullName}) LIKE ?", [$search]));
            });
        }

        $perPage = $request->integer('per_page', 15);
        $users = $query->orderBy('id_user', 'desc')->paginate($perPage);

        $summary = $this->buildSummaryMetrics();
        $governanceMatrix = $this->buildGovernanceMatrix();
        $roles = Role::all();

        if ($request->expectsJson()) {
            $resourceData = UserManagementResource::collection($users)->response()->getData(true);

            return response()->json([
                'success' => true,
                'message' => __('asic-api.messages.data_user_role_management_berhasil_dimuat'),
                'summary' => $summary,
                'governance_matrix' => $governanceMatrix,
                'available_roles' => $roles,
                'users' => $resourceData['data'] ?? [],
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                    'total_identities_synced' => $users->total(),
                ],
                'data' => $resourceData,
            ], Response::HTTP_OK);
        }

        return redirect()->route('portal.admin.users');
    }

    /**
     * Menampilkan formulir penambahan / opsi role untuk invite user baru.
     */
    #[OA\Get(
        path: '/api/admin/users/create',
        summary: 'Opsi Peran untuk Tambah Pengguna Baru',
        description: 'Mendapatkan daftar seluruh peran (roles) yang tersedia untuk membuat akun baru.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar peran tersedia',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'available_roles', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
        ]
    )]
    public function create(): JsonResponse
    {
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'available_roles' => $roles,
        ], Response::HTTP_OK);
    }

    /**
     * Menyimpan akun pengguna baru atau memproses modal "+ Invite New User" (KF-09).
     */
    #[OA\Post(
        path: '/api/admin/users',
        summary: 'Buat Akun Pengguna / Staf Baru',
        description: 'Membuat akun kepanitiaan atau peserta baru oleh Administrator.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_role', 'email'],
                properties: [
                    new OA\Property(property: 'id_role', type: 'integer', example: 2),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'staff.sec@asic.unand.ac.id'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', nullable: true, example: 'Secret123#'),
                    new OA\Property(property: 'status_akun', type: 'string', enum: ['Aktif', 'Nonaktif'], example: 'Aktif'),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Dr. Jane Doe'),
                    new OA\Property(property: 'nip', type: 'string', nullable: true, example: '198501012010121001'),
                    new OA\Property(property: 'phone', type: 'string', example: '+62811223344'),
                    new OA\Property(property: 'affiliation', type: 'string', example: 'Fakultas Pertanian UNAND'),
                    new OA\Property(property: 'job_title', type: 'string', example: 'Dosen / Peneliti'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Akun berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal (email sudah terdaftar dll)'),
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $rawPassword = ! empty($validated['password'])
                ? $validated['password']
                : 'Asic2026#'.Str::random(6);

            $userAccount = UserAccount::create([
                'id_role' => $validated['id_role'],
                'email' => strtolower(trim($validated['email'])),
                'password' => Hash::make($rawPassword),
                'status_akun' => $validated['status_akun'] ?? 'Aktif',
            ]);

            $parsedName = $this->parseName(
                rawName: $validated['full_name'] ?? $validated['name'] ?? null,
                rawPrefix: $validated['prefix'] ?? null,
                rawFirstName: $validated['first_name'] ?? null,
                rawLastName: $validated['last_name'] ?? null,
                fallback: explode('@', $validated['email'])[0]
            );

            Participant::create([
                'id_user' => $userAccount->id_user,
                'prefix' => $parsedName['prefix'],
                'first_name' => $parsedName['first_name'],
                'last_name' => $parsedName['last_name'],
                'passport_no' => $validated['nip'] ?? $validated['passport_no'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'institution' => $validated['affiliation'] ?? $validated['institution'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? 'Indonesia',
                'postcode' => $validated['postcode'] ?? null,
            ]);

            return $userAccount;
        });

        $user->load(['role', 'participant']);
        $this->recordAudit('USER_CREATED', 'Akun pengguna dibuat.', $user->id_user, ['role_id' => $user->id_role, 'status' => $user->status_akun]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('asic-api.messages.akun_pengguna_staf_berhasil_dibuat_dan_undangan_te'),
                'data' => new UserManagementResource($user),
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('portal.admin.users')->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail informasi akun pengguna dan profilnya.
     */
    #[OA\Get(
        path: '/api/admin/users/{id_user}',
        summary: 'Detail Akun Pengguna',
        description: 'Mendapatkan informasi detail akun dan profil pengguna berdasarkan ID.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id_user', in: 'path', required: true, description: 'ID User Account', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail pengguna berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Pengguna tidak ditemukan'),
        ]
    )]
    public function show(int $id_user): JsonResponse
    {
        $user = UserAccount::with(['role', 'participant'])->findOrFail($id_user);

        return response()->json([
            'success' => true,
            'data' => new UserManagementResource($user),
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan formulir edit akun pengguna.
     */
    #[OA\Get(
        path: '/api/admin/users/{id_user}/edit',
        summary: 'Data Formulir Edit Pengguna',
        description: 'Mendapatkan data awal dan daftar peran untuk memodifikasi akun pengguna.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id_user', in: 'path', required: true, description: 'ID User Account', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data edit pengguna berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function edit(int $id_user): JsonResponse
    {
        $user = UserAccount::with(['role', 'participant'])->findOrFail($id_user);
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserManagementResource($user),
                'available_roles' => $roles,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Memperbarui data akun pengguna, peran, dan status akun (KF-09).
     */
    #[OA\Put(
        path: '/api/admin/users/{id_user}',
        summary: 'Perbarui Data Akun Pengguna',
        description: 'Mengubah peran, email, status akun, kata sandi, dan profil pengguna.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id_user', in: 'path', required: true, description: 'ID User Account', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_role', 'email', 'status_akun'],
                properties: [
                    new OA\Property(property: 'id_role', type: 'integer', example: 2),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'staff@asic.unand.ac.id'),
                    new OA\Property(property: 'status_akun', type: 'string', enum: ['Aktif', 'Nonaktif'], example: 'Aktif'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', nullable: true, example: 'NewSecret123#'),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Dr. Jane Doe'),
                    new OA\Property(property: 'nip', type: 'string', nullable: true, example: '198501012010121001'),
                    new OA\Property(property: 'phone', type: 'string', example: '+62811223344'),
                    new OA\Property(property: 'affiliation', type: 'string', example: 'Fakultas Pertanian UNAND'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data akun berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi input gagal'),
        ]
    )]
    public function update(UpdateUserRequest $request, int $id_user): JsonResponse|RedirectResponse
    {
        $user = UserAccount::findOrFail($id_user);
        $validated = $request->validated();

        if ($blocked = $this->guardAdminMutation($request, $user, $validated['id_role'], $validated['status_akun'])) {
            return $blocked;
        }

        DB::transaction(function () use ($user, $validated) {
            $userPayload = [
                'id_role' => $validated['id_role'],
                'email' => strtolower(trim($validated['email'])),
                'status_akun' => $validated['status_akun'],
            ];

            if (! empty($validated['password'])) {
                $userPayload['password'] = Hash::make($validated['password']);
            }

            $user->update($userPayload);

            $parsedName = $this->parseName(
                rawName: $validated['full_name'] ?? $validated['name'] ?? null,
                rawPrefix: $validated['prefix'] ?? null,
                rawFirstName: $validated['first_name'] ?? null,
                rawLastName: $validated['last_name'] ?? null,
                fallback: explode('@', $validated['email'])[0]
            );

            $participantData = array_filter([
                'prefix' => $parsedName['prefix'],
                'first_name' => $parsedName['first_name'],
                'last_name' => $parsedName['last_name'],
                'passport_no' => $validated['nip'] ?? $validated['passport_no'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'institution' => $validated['affiliation'] ?? $validated['institution'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
            ], fn ($val) => ! is_null($val));

            if (! empty($participantData)) {
                $user->participant()->updateOrCreate(
                    ['id_user' => $user->id_user],
                    $participantData
                );
            }

            $this->recordAudit('USER_UPDATED', 'Data akun pengguna diperbarui.', $user->id_user, [
                'role_id' => $user->id_role,
                'status' => $user->status_akun,
            ]);
        });

        $user->load(['role', 'participant']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('asic-api.messages.data_akun_pengguna_berhasil_diperbarui'),
                'data' => new UserManagementResource($user),
            ], Response::HTTP_OK);
        }

        return redirect()->route('portal.admin.users')->with('success', 'Data akun pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus akun pengguna dari sistem.
     */
    #[OA\Delete(
        path: '/api/admin/users/{id_user}',
        summary: 'Hapus Akun Pengguna',
        description: 'Menghapus permanen akun pengguna dan profil terkait (Dilarang menghapus akun sendiri).',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id_user', in: 'path', required: true, description: 'ID User Account', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Akun berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Akun pengguna berhasil dihapus.'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Dilarang menghapus akun sendiri'),
        ]
    )]
    public function destroy(Request $request, int $id_user): JsonResponse|RedirectResponse
    {
        $user = UserAccount::findOrFail($id_user);

        if (Auth::id() === $user->id_user) {
            $errorMessage = 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], Response::HTTP_FORBIDDEN);
            }

            return back()->withErrors(['error' => $errorMessage]);
        }

        if ($this->wouldRemoveLastActiveAdmin([$user->id_user], 'delete')) {
            return $this->mutationBlocked($request, 'Sistem harus memiliki setidaknya satu Admin aktif.');
        }

        DB::transaction(function () use ($user) {
            $this->recordAudit('USER_DELETED', 'Akun pengguna dihapus.', $user->id_user, ['role_id' => $user->id_role]);
            $user->participant()->delete();
            $user->delete();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('asic-api.messages.akun_pengguna_berhasil_dihapus'),
            ], Response::HTTP_OK);
        }

        return redirect()->route('portal.admin.users')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    /**
     * Mengubah status keaktifan akun (Aktif / Nonaktif) secara cepat.
     */
    #[OA\Patch(
        path: '/api/admin/users/{id_user}/toggle-status',
        summary: 'Toggle Status Aktif / Nonaktif Akun',
        description: 'Mengubah status akun pengguna antara Aktif dan Nonaktif secara instan.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id_user', in: 'path', required: true, description: 'ID User Account', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status akun berhasil diubah',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Dilarang menonaktifkan akun sendiri'),
        ]
    )]
    public function toggleStatus(Request $request, int $id_user): JsonResponse|RedirectResponse
    {
        $user = UserAccount::findOrFail($id_user);

        if (Auth::id() === $user->id_user) {
            $errorMessage = 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], Response::HTTP_FORBIDDEN);
            }

            return back()->withErrors(['error' => $errorMessage]);
        }

        $newStatus = ($user->status_akun === 'Aktif') ? 'Nonaktif' : 'Aktif';
        if ($newStatus === 'Nonaktif' && $this->wouldRemoveLastActiveAdmin([$user->id_user], 'deactivate')) {
            return $this->mutationBlocked($request, 'Sistem harus memiliki setidaknya satu Admin aktif.');
        }
        $user->update(['status_akun' => $newStatus]);
        $this->recordAudit('USER_STATUS_TOGGLED', 'Status akun diubah.', $user->id_user, ['status' => $newStatus]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status akun berhasil diubah menjadi {$newStatus}.",
                'data' => [
                    'id_user' => $user->id_user,
                    'status_akun' => $user->status_akun,
                    'status' => $newStatus === 'Aktif' ? 'Active' : 'Inactive',
                ],
            ], Response::HTTP_OK);
        }

        return back()->with('success', "Status akun berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Eksekusi Aksi Massal (Batch Actions) untuk baris yang dipilih via checkbox.
     */
    #[OA\Post(
        path: '/api/admin/users/batch-action',
        summary: 'Aksi Massal Akun Pengguna (Batch Actions)',
        description: 'Mengeksekusi aktivasi massal, penonaktifan massal, ubah role massal, atau hapus massal untuk banyak ID pengguna.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['action', 'user_ids'],
                properties: [
                    new OA\Property(property: 'action', type: 'string', enum: ['activate', 'deactivate', 'delete', 'change_role'], example: 'activate'),
                    new OA\Property(property: 'user_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [2, 3, 4]),
                    new OA\Property(property: 'id_role', type: 'integer', nullable: true, example: 3),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Aksi massal berhasil dijalankan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'affected_count', type: 'integer', example: 3),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Parameter aksi massal tidak valid'),
        ]
    )]
    public function batchAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:activate,deactivate,delete,change_role'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:user_account,id_user'],
            'id_role' => ['required_if:action,change_role', 'nullable', 'integer', 'exists:role,id_role'],
        ]);

        $action = $validated['action'];
        $ids = array_values(array_unique(array_map('intval', $validated['user_ids'])));
        $adminRoleId = Role::where('nama_role', Role::ROLE_ADMIN)->value('id_role');
        $selfIncluded = in_array((int) Auth::id(), $ids, true);
        if ($selfIncluded && in_array($action, ['deactivate', 'delete'], true)) {
            return response()->json(['success' => false, 'message' => __('asic-api.messages.akun_admin_tidak_dapat_menonaktifkan_atau_menghapu')], Response::HTTP_FORBIDDEN);
        }
        if ($selfIncluded && $action === 'change_role' && (int) $validated['id_role'] !== (int) $adminRoleId) {
            return response()->json(['success' => false, 'message' => __('asic-api.messages.akun_admin_tidak_dapat_menurunkan_perannya_sendiri')], Response::HTTP_FORBIDDEN);
        }
        if ($this->wouldRemoveLastActiveAdmin($ids, $action, $validated['id_role'] ?? null)) {
            return response()->json(['success' => false, 'message' => __('asic-api.messages.sistem_harus_memiliki_setidaknya_satu_admin_aktif')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $affectedCount = 0;

        switch ($action) {
            case 'activate':
                $affectedCount = UserAccount::whereIn('id_user', $ids)->update(['status_akun' => 'Aktif']);
                $msg = "{$affectedCount} akun pengguna berhasil diaktifkan.";
                break;
            case 'deactivate':
                $affectedCount = UserAccount::whereIn('id_user', $ids)->update(['status_akun' => 'Nonaktif']);
                $msg = "{$affectedCount} akun pengguna berhasil dinonaktifkan.";
                break;
            case 'change_role':
                $affectedCount = UserAccount::whereIn('id_user', $ids)->update(['id_role' => $validated['id_role']]);
                $msg = "Peran {$affectedCount} akun pengguna berhasil diubah.";
                break;
            case 'delete':
                Participant::whereIn('id_user', $ids)->delete();
                $affectedCount = UserAccount::whereIn('id_user', $ids)->delete();
                $msg = "{$affectedCount} akun pengguna berhasil dihapus.";
                break;
            default:
                $msg = 'Aksi massal tidak dikenali.';
        }

        $this->recordAudit('USER_BATCH_ACTION', 'Aksi massal akun dijalankan.', null, [
            'action' => $action,
            'user_ids' => $ids,
            'affected_count' => $affectedCount,
        ]);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'affected_count' => $affectedCount,
        ], Response::HTTP_OK);
    }

    /**
     * Mengekspor audit log / daftar akun ke format CSV ("Export Audit Log (CSV)").
     */
    #[OA\Get(
        path: '/api/admin/users/export-csv',
        summary: 'Ekspor Data Pengguna ke CSV',
        description: 'Mengunduh rekapitulasi audit akun pengguna dan hak akses dalam format CSV terstruktur.',
        tags: ['Admin - Manajemen Pengguna'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File CSV berhasil diunduh',
                content: new OA\MediaType(mediaType: 'text/csv')
            ),
        ]
    )]
    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="asic-user-role-audit-log-'.date('Y-m-d').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID User', 'Full Name', 'Email', 'Role', 'Affiliation', 'Status', 'Registered At']);

            UserAccount::with(['role', 'participant'])->chunk(100, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id_user,
                        $user->participant?->full_name ?? explode('@', $user->email)[0],
                        $user->email,
                        $user->role?->nama_role ?? '-',
                        $user->participant?->institution ?? '-',
                        $user->status_akun,
                        $user->created_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function guardAdminMutation(Request $request, UserAccount $target, int $newRoleId, string $newStatus): JsonResponse|RedirectResponse|null
    {
        $adminRoleId = (int) Role::where('nama_role', Role::ROLE_ADMIN)->value('id_role');
        $actor = $request->user();
        $self = $actor && (int) $actor->id_user === (int) $target->id_user;

        if ($self && $actor->isAdmin() && ($newRoleId !== $adminRoleId || $newStatus !== 'Aktif')) {
            return $this->mutationBlocked($request, 'Akun Admin tidak dapat menonaktifkan atau menurunkan perannya sendiri.', Response::HTTP_FORBIDDEN);
        }

        if ($target->status_akun === 'Aktif' && (int) $target->id_role === $adminRoleId
            && ($newRoleId !== $adminRoleId || $newStatus !== 'Aktif')
            && $this->wouldRemoveLastActiveAdmin([$target->id_user], 'change_role', $newRoleId, $newStatus)) {
            return $this->mutationBlocked($request, 'Sistem harus memiliki setidaknya satu Admin aktif.');
        }

        return null;
    }

    private function wouldRemoveLastActiveAdmin(array $ids, string $action, ?int $newRoleId = null, ?string $newStatus = null): bool
    {
        $adminRoleId = (int) Role::where('nama_role', Role::ROLE_ADMIN)->value('id_role');
        $removesAdmin = in_array($action, ['delete', 'deactivate'], true)
            || ($action === 'change_role' && (int) $newRoleId !== $adminRoleId)
            || ($newStatus === 'Nonaktif');

        if (! $removesAdmin) {
            return false;
        }

        $affectedActiveAdmins = UserAccount::query()->whereIn('id_user', $ids)->where('id_role', $adminRoleId)->where('status_akun', 'Aktif')->count();

        return $affectedActiveAdmins > 0
            && UserAccount::query()->where('id_role', $adminRoleId)->where('status_akun', 'Aktif')->whereNotIn('id_user', $ids)->count() === 0;
    }

    private function mutationBlocked(Request $request, string $message, int $status = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return back()->withErrors(['error' => $message]);
    }

    private function recordAudit(string $action, string $note, ?int $targetId = null, array $meta = []): void
    {
        $actor = Auth::user();
        unset($meta['password'], $meta['password_confirmation'], $meta['token'], $meta['secret']);
        AuditLog::create([
            'id_user' => $actor?->id_user,
            'user_email' => $actor?->email ?? 'unknown',
            'action' => $action,
            'target_type' => $targetId === null ? 'user_batch' : 'user_account',
            'target_id' => $targetId === null ? null : (string) $targetId,
            'note' => $note,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * Helper untuk menghitung metrik summary dari data aktual.
     */
    protected function buildSummaryMetrics(): array
    {
        $roleCounts = UserAccount::query()->join('role', 'role.id_role', '=', 'user_account.id_role')
            ->select('role.nama_role', DB::raw('count(*) as total'))
            ->groupBy('role.nama_role')->pluck('total', 'nama_role');
        $activeRoles = Role::query()->count();

        return [
            'total_system_users' => [
                'count' => UserAccount::query()->count(),
                'unit' => 'accounts',
                'breakdown' => [
                    'participant' => (int) ($roleCounts[Role::ROLE_PESERTA] ?? 0),
                    'secretariat' => (int) ($roleCounts[Role::ROLE_SEKRETARIAT] ?? 0),
                    'treasurer' => (int) ($roleCounts[Role::ROLE_BENDAHARA] ?? 0),
                    'admin' => (int) ($roleCounts[Role::ROLE_ADMIN] ?? 0),
                    'abstract_team' => (int) ($roleCounts[Role::ROLE_TIM_ABSTRAK] ?? 0),
                ],
            ],
            'active_roles' => ['count' => $activeRoles],
            'permission_capabilities' => count(PermissionMatrix::capabilities()),
        ];
    }

    /**
     * Helper struktur Governance & Permission Matrix (4 Kelompok Operasional).
     */
    protected function buildGovernanceMatrix(): array
    {
        return [
            'roles' => Role::query()->orderBy('id_role')->get(['id_role', 'nama_role'])->map(fn (Role $role) => [
                'id_role' => (string) $role->id_role,
                'name' => $role->nama_role,
            ])->all(),
            'capabilities' => PermissionMatrix::capabilities(),
            'matrix' => app(PermissionMatrix::class)->activeMatrix(),
        ];
    }

    /**
     * Helper memecah nama ke format ERD.
     */
    protected function parseName(
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
        $lastName = ! empty($words) ? implode(' ', $words) : null;

        return [
            'prefix' => $prefix,
            'first_name' => substr($firstName, 0, 50),
            'last_name' => $lastName ? substr($lastName, 0, 50) : null,
        ];
    }
}
