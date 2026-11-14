<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRolePermissionMatrixRequest;
use App\Models\Role;
use App\Support\PermissionMatrix as PermissionMatrixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionController extends Controller
{
    /**
     * Menampilkan data lengkap Roles & Permissions Matrix untuk rendering UI.
     */
    #[OA\Get(
        path: '/api/admin/roles-permissions',
        summary: 'Matriks Peran dan Hak Akses (RBAC Matrix)',
        description: 'Mendapatkan data matriks hak akses berdasarkan ID lima peran untuk seluruh kapabilitas sistem (Admin).',
        tags: ['Admin - Hak Akses'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data matriks hak akses berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak (khusus Admin)'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $rolesFromDb = Role::all();
        $capabilities = $this->getCapabilitiesDefinition();
        $matrix = $this->getActiveMatrix();

        $summary = [
            'core_roles_count' => $rolesFromDb->count(),
            'core_roles' => $rolesFromDb->pluck('nama_role'),
            'capability_nodes_count' => count($capabilities),
            'last_audit' => [
                'date' => '14 Feb 2026',
                'auditor' => 'Prof. Dr. Ir. Helmi (General Chair)',
                'timestamp' => '2026-02-14T08:00:00+07:00',
            ],
            'identity_provider' => [
                'name' => 'UNAND SSO',
                'protocol' => 'SAML2 / OAuth2 Central Realm',
                'security_level' => 'SSL 256-Bit Encrypted',
            ],
            'compliance' => [
                'status' => 'Certified',
                'block_id' => '#ASIC-SEC-09024D',
                'version' => 'ACL V2.4',
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data Roles & Permissions Matrix berhasil dimuat.',
            'data' => [
                'summary' => $summary,
                'roles' => $this->getRolesDefinition(),
                'capabilities' => $capabilities,
                'matrix' => $matrix,
                'legend' => [
                    'full_access' => 'Full Access (Allowed)',
                    'view_only' => 'View Only',
                    'restricted' => 'Restricted',
                ],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Menyimpan perubahan konfigurasi matriks hak akses (Tombol "Save Changes").
     */
    #[OA\Post(
        path: '/api/admin/roles-permissions',
        summary: 'Simpan Perubahan Matriks Hak Akses',
        description: 'Memperbarui konfigurasi hak akses matriks peran di database dan mencatat jejak audit.',
        tags: ['Admin - Hak Akses'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['matrix'],
                properties: [
                    new OA\Property(property: 'matrix', type: 'object'),
                    new OA\Property(property: 'audit_note', type: 'string', example: 'Updated treasury permissions'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Konfigurasi matriks berhasil disimpan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Format matriks tidak valid'),
        ]
    )]
    public function save(UpdateRolePermissionMatrixRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $matrix = $this->normalizeMatrix($validated['matrix']);

        DB::table('role_permission_matrix')->updateOrInsert(
            ['id' => 1],
            ['matrix' => json_encode($matrix, JSON_THROW_ON_ERROR), 'updated_at' => now()]
        );

        $this->appendAuditTrail([
            'action' => 'UPDATE_MATRIX',
            'user' => auth()->user()?->email ?? 'admin',
            'note' => $validated['audit_note'] ?? 'Updated via Roles & Permissions Matrix UI',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perubahan konfigurasi Roles & Permissions Matrix berhasil disimpan dan langsung berlaku.',
            'data' => [
                'matrix' => $matrix,
                'saved_at' => now()->toIso8601String(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Mereset matriks hak akses ke nilai bawaan (Tombol "Reset to Defaults").
     */
    #[OA\Post(
        path: '/api/admin/roles-permissions/reset',
        summary: 'Reset Matriks Hak Akses ke Bawaan Sistem',
        description: 'Mengembalikan seluruh konfigurasi matriks peran ke pengaturan default awal.',
        tags: ['Admin - Hak Akses'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Matriks berhasil direset ke nilai default',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function reset(): JsonResponse
    {
        $defaultMatrix = $this->getDefaultMatrix();
        DB::table('role_permission_matrix')->updateOrInsert(
            ['id' => 1],
            ['matrix' => json_encode($defaultMatrix, JSON_THROW_ON_ERROR), 'updated_at' => now()]
        );

        $this->appendAuditTrail([
            'action' => 'RESET_TO_DEFAULTS',
            'user' => auth()->user()?->email ?? 'admin',
            'note' => 'Reset matrix to default settings',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Matriks hak akses berhasil dikembalikan ke pengaturan default.',
            'data' => [
                'matrix' => $defaultMatrix,
                'reset_at' => now()->toIso8601String(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Mengekspor matriks hak akses aktif dalam format JSON ("Export ACL Matrix (JSON)").
     */
    #[OA\Get(
        path: '/api/admin/roles-permissions/export',
        summary: 'Ekspor Matriks Hak Akses (JSON)',
        description: 'Mengunduh skema dan nilai aktif matriks hak akses dalam file JSON.',
        tags: ['Admin - Hak Akses'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File JSON matriks berhasil diunduh',
                content: new OA\MediaType(mediaType: 'application/json')
            ),
        ]
    )]
    public function export(): JsonResponse
    {
        $exportData = [
            'schema' => 'ASIC-2026-ACL-Matrix',
            'version' => '3.0',
            'exported_at' => now()->toIso8601String(),
            'exported_by' => auth()->user()?->email ?? 'admin',
            'roles' => $this->getRolesDefinition(),
            'capabilities' => $this->getCapabilitiesDefinition(),
            'matrix' => $this->getActiveMatrix(),
        ];

        return response()->json($exportData, Response::HTTP_OK, [
            'Content-Disposition' => 'attachment; filename="asic-2026-acl-matrix.json"',
        ]);
    }

    /**
     * Mengunduh riwayat audit trail log hak akses ("Download Audit Trail Log").
     */
    #[OA\Get(
        path: '/api/admin/roles-permissions/audit-log',
        summary: 'Unduh Log Audit Trail Perubahan Hak Akses',
        description: 'Mengunduh rekaman jejak audit trail seluruh modifikasi peran dan hak akses sistem.',
        tags: ['Admin - Hak Akses'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Riwayat audit trail berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
        ]
    )]
    public function auditLog(): JsonResponse
    {
        $auditLogs = Cache::get('asic_acl_audit_trail', [
            [
                'action' => 'INITIAL_SYSTEM_SETUP',
                'user' => 'system@asic.unand.ac.id',
                'note' => 'Initial installation of 5 roles RBAC architecture',
                'timestamp' => '2026-02-14T08:00:00+07:00',
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => $auditLogs,
        ], Response::HTTP_OK, [
            'Content-Disposition' => 'attachment; filename="asic-acl-audit-log.json"',
        ]);
    }

    /**
     * Dapatkan matriks hak akses yang aktif.
     */
    protected function getActiveMatrix(): array
    {
        return app(PermissionMatrixService::class)->activeMatrix();
    }

    /**
     * Daftar kapabilitas yang didukung oleh middleware izin dinamis.
     */
    protected function getCapabilitiesDefinition(): array
    {
        return PermissionMatrixService::capabilities();
    }

    /**
     * Definisi peran yang didukung.
     */
    protected function getRolesDefinition(): array
    {
        $englishNames = [
            Role::ROLE_PESERTA => 'Participant',
            Role::ROLE_SEKRETARIAT => 'Secretariat',
            Role::ROLE_BENDAHARA => 'Treasurer',
            Role::ROLE_TIM_ABSTRAK => 'Abstract Team',
            Role::ROLE_ADMIN => 'Admin',
        ];

        return Role::query()->orderBy('id_role')->get()->map(function (Role $role) use ($englishNames): array {
            return [
                'key' => (string) $role->id_role,
                'name' => $englishNames[$role->nama_role] ?? $role->nama_role,
                'indonesian_name' => $role->nama_role,
                'subtext' => $role->deskripsi,
                'badge' => null,
            ];
        })->all();
    }

    /**
     * Nilai default matriks.
     */
    protected function getDefaultMatrix(): array
    {
        return PermissionMatrixService::defaultMatrix();
    }

    /**
     * @param  array<string, array<string, string>>  $matrix
     * @return array<string, array<string, string>>
     */
    protected function normalizeMatrix(array $matrix): array
    {
        $levels = [
            'full_access' => 'full_access',
            'view_only' => 'view_only',
            'restricted' => 'restricted',
            'Full Access' => 'full_access',
            'View Only' => 'view_only',
            'Restricted' => 'restricted',
        ];
        $normalized = [];
        $roleIds = Role::query()->pluck('id_role')->map(fn ($roleId): string => (string) $roleId);

        foreach (PermissionMatrixService::capabilities() as $capability) {
            $permission = $capability['id'];
            foreach ($roleIds as $roleKey) {
                $normalized[$permission][$roleKey] = $levels[$matrix[$permission][$roleKey]];
            }
        }

        return $normalized;
    }

    /**
     * Tambahkan catatan ke audit trail cache.
     */
    protected function appendAuditTrail(array $entry): void
    {
        $logs = Cache::get('asic_acl_audit_trail', []);
        array_unshift($logs, $entry);
        Cache::forever('asic_acl_audit_trail', array_slice($logs, 0, 100));
    }
}
