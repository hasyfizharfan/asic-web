<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Participant;
use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserManagementController extends Controller
{
    /**
     * Menampilkan daftar seluruh akun pengguna dan panitia (KF-09).
     *
     * Mendukung pencarian (search) dan filter berdasarkan peran (id_role) serta status_akun.
     */
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $query = UserAccount::with(['role', 'participant']);

        // Filter berdasarkan peran (id_role)
        if ($request->filled('id_role')) {
            $query->where('id_role', $request->integer('id_role'));
        }

        // Filter berdasarkan status akun
        if ($request->filled('status_akun')) {
            $query->where('status_akun', $request->string('status_akun'));
        }

        // Pencarian email atau nama/institusi peserta
        if ($request->filled('search')) {
            $search = '%' . trim($request->string('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', $search)
                    ->orWhereHas('participant', function ($p) use ($search) {
                        $p->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('institution', 'like', $search);
                    });
            });
        }

        $users = $query->orderBy('id_user', 'desc')->paginate(15);
        $roles = Role::all();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users,
                'available_roles' => $roles,
            ], Response::HTTP_OK);
        }

        return response()->view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Menampilkan formulir penambahan akun pengguna baru.
     */
    public function create(): JsonResponse
    {
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'available_roles' => $roles,
        ], Response::HTTP_OK);
    }

    /**
     * Menyimpan akun pengguna baru dan menetapkan peran (id_role) (KF-09).
     */
    public function store(StoreUserRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $userAccount = UserAccount::create([
                'id_role' => $validated['id_role'],
                'email' => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
                'status_akun' => $validated['status_akun'],
            ]);

            // Jika role adalah Peserta atau data profil disertakan, buat profil participant
            $role = Role::find($validated['id_role']);
            $isPeserta = $role && $role->nama_role === Role::ROLE_PESERTA;

            if ($isPeserta || !empty($validated['first_name']) || !empty($validated['institution'])) {
                Participant::create([
                    'id_user' => $userAccount->id_user,
                    'prefix' => $validated['prefix'] ?? null,
                    'first_name' => $validated['first_name'] ?? explode('@', $validated['email'])[0],
                    'last_name' => $validated['last_name'] ?? null,
                    'passport_no' => $validated['passport_no'] ?? null,
                    'job_title' => $validated['job_title'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'institution' => $validated['institution'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'country' => $validated['country'] ?? null,
                    'postcode' => $validated['postcode'] ?? null,
                ]);
            }

            return $userAccount;
        });

        $user->load(['role', 'participant']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Akun pengguna berhasil ditambahkan.',
                'data' => $user,
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail informasi akun pengguna dan profilnya.
     */
    public function show(int $id_user): JsonResponse
    {
        $user = UserAccount::with(['role', 'participant'])->findOrFail($id_user);

        return response()->json([
            'success' => true,
            'data' => $user,
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan formulir edit akun pengguna.
     */
    public function edit(int $id_user): JsonResponse
    {
        $user = UserAccount::with(['role', 'participant'])->findOrFail($id_user);
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'available_roles' => $roles,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Memperbarui data akun pengguna, peran, dan status akun (KF-09).
     */
    public function update(UpdateUserRequest $request, int $id_user): JsonResponse|RedirectResponse
    {
        $user = UserAccount::findOrFail($id_user);
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $userPayload = [
                'id_role' => $validated['id_role'],
                'email' => strtolower(trim($validated['email'])),
                'status_akun' => $validated['status_akun'],
            ];

            // Update password hanya jika diisi
            if (!empty($validated['password'])) {
                $userPayload['password'] = Hash::make($validated['password']);
            }

            $user->update($userPayload);

            // Update atau buat data profil participant
            $participantData = array_filter([
                'prefix' => $validated['prefix'] ?? null,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'passport_no' => $validated['passport_no'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'institution' => $validated['institution'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
            ], fn($val) => !is_null($val));

            if (!empty($participantData)) {
                $user->participant()->updateOrCreate(
                    ['id_user' => $user->id_user],
                    $participantData
                );
            }
        });

        $user->load(['role', 'participant']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data akun pengguna berhasil diperbarui.',
                'data' => $user,
            ], Response::HTTP_OK);
        }

        return redirect()->route('admin.users.index')->with('success', 'Data akun pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus akun pengguna dari sistem.
     */
    public function destroy(Request $request, int $id_user): JsonResponse|RedirectResponse
    {
        $user = UserAccount::findOrFail($id_user);

        // Mencegah admin menghapus akun miliknya sendiri yang sedang aktif digunakan
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

        DB::transaction(function () use ($user) {
            $user->participant()->delete();
            $user->delete();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Akun pengguna berhasil dihapus.',
            ], Response::HTTP_OK);
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    /**
     * Mengubah status keaktifan akun (Aktif / Nonaktif) secara cepat.
     */
    public function toggleStatus(Request $request, int $id_user): JsonResponse|RedirectResponse
    {
        $user = UserAccount::findOrFail($id_user);

        // Mencegah admin menonaktifkan akun miliknya sendiri
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
        $user->update(['status_akun' => $newStatus]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status akun berhasil diubah menjadi {$newStatus}.",
                'data' => [
                    'id_user' => $user->id_user,
                    'status_akun' => $user->status_akun,
                ],
            ], Response::HTTP_OK);
        }

        return back()->with('success', "Status akun berhasil diubah menjadi {$newStatus}.");
    }
}

