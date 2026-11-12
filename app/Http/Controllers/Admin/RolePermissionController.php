<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRolePermissionMatrixRequest;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionController extends Controller
{
    /**
     * Cache key untuk menyimpan matriks kustom roles & permissions.
     */
    protected const CACHE_KEY = 'asic_acl_matrix_v2';

    /**
     * Menampilkan data lengkap Roles & Permissions Matrix untuk rendering UI.
     */
    #[OA\Get(
        path: '/api/admin/roles-permissions',
        summary: 'Matriks Peran dan Hak Akses (RBAC Matrix)',
        description: 'Mendapatkan data matriks hak akses 5 peran terhadap 10 node kapabilitas sistem konferensi (Admin).',
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
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak (khusus Admin)')
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
        description: 'Memperbarui konfigurasi hak akses matriks peran pada cache persisten dan mencatat jejak audit.',
        tags: ['Admin - Hak Akses'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['matrix'],
                properties: [
                    new OA\Property(property: 'matrix', type: 'object'),
                    new OA\Property(property: 'audit_note', type: 'string', example: 'Updated treasury permissions')
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
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Format matriks tidak valid')
        ]
    )]
    public function save(UpdateRolePermissionMatrixRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $matrix = $validated['matrix'];

        Cache::forever(self::CACHE_KEY, $matrix);

        $this->appendAuditTrail([
            'action' => 'UPDATE_MATRIX',
            'user' => auth()->user()?->email ?? 'admin',
            'note' => $validated['audit_note'] ?? 'Updated via Roles & Permissions Matrix UI',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perubahan konfigurasi Roles & Permissions Matrix berhasil disimpan. Perubahan akan berlaku saat delegasi login ulang.',
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
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function reset(): JsonResponse
    {
        Cache::forget(self::CACHE_KEY);
        $defaultMatrix = $this->getDefaultMatrix();

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
            )
        ]
    )]
    public function export(): JsonResponse
    {
        $exportData = [
            'schema' => 'ASIC-2026-ACL-Matrix',
            'version' => '2.4',
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
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))
                    ]
                )
            )
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
        return Cache::get(self::CACHE_KEY, $this->getDefaultMatrix());
    }

    /**
     * Definisi 10 node kapabilitas.
     */
    protected function getCapabilitiesDefinition(): array
    {
        return [
            [
                'id' => 'submit_abstract',
                'title' => 'Submit abstract',
                'tag' => 'Manuscripts',
                'description' => 'Author manuscript submission, metadata editing, draft updates',
            ],
            [
                'id' => 'review_abstracts',
                'title' => 'Review abstracts',
                'tag' => 'Peer Review',
                'description' => 'Peer review scoring, rubric evaluation, reviewer notes',
            ],
            [
                'id' => 'accept_reject',
                'title' => 'Accept/Reject',
                'tag' => 'Decisions',
                'description' => 'Decision governance, issuing official LOA & rejection letters',
            ],
            [
                'id' => 'pay_registration',
                'title' => 'Pay registration',
                'tag' => 'Treasury',
                'description' => 'Checkout gateways, bank transfer slip upload, PayPal payment',
            ],
            [
                'id' => 'verify_payment',
                'title' => 'Verify payment',
                'tag' => 'Treasury',
                'description' => 'Treasury queue audit, slip validation, webhook reconciliation',
            ],
            [
                'id' => 'manage_tariffs',
                'title' => 'Manage tariffs',
                'tag' => 'Treasury',
                'description' => 'Early bird fee setup, excursion pricing, currency rate locks',
            ],
            [
                'id' => 'scan_attendance_qr',
                'title' => 'Scan attendance QR',
                'tag' => 'Onsite Desk',
                'description' => 'Onsite delegate check-in, badge print trigger, arrival logging',
            ],
            [
                'id' => 'manage_website_content',
                'title' => 'Manage website content',
                'tag' => 'CMS / Portal',
                'description' => 'Public landing page, announcements, keynote speakers CMS',
            ],
            [
                'id' => 'manage_users',
                'title' => 'Manage users',
                'tag' => 'Governance',
                'description' => 'Account creation, committee role assignment, access revocation',
            ],
            [
                'id' => 'manage_document_templates',
                'title' => 'Manage document templates',
                'tag' => 'Publishing',
                'description' => 'Letterhead, Dean signature, LOA & certificate formatting',
            ],
        ];
    }

    /**
     * Definisi peran yang didukung.
     */
    protected function getRolesDefinition(): array
    {
        return [
            [
                'key' => 'participant',
                'name' => 'Participant',
                'indonesian_name' => 'Peserta',
                'subtext' => 'Author / Attendee',
                'badge' => null,
            ],
            [
                'key' => 'secretariat',
                'name' => 'Secretariat',
                'indonesian_name' => 'Sekretariat',
                'subtext' => 'Scientific Desk',
                'badge' => null,
            ],
            [
                'key' => 'treasurer',
                'name' => 'Treasurer',
                'indonesian_name' => 'Bendahara',
                'subtext' => 'Finance Directorate',
                'badge' => null,
            ],
            [
                'key' => 'abstract_team',
                'name' => 'Abstract Team',
                'indonesian_name' => 'Tim Abstrak',
                'subtext' => 'Reviewer Panel (PRD)',
                'badge' => 'PRD KF-18',
            ],
            [
                'key' => 'admin',
                'name' => 'Admin',
                'indonesian_name' => 'Admin',
                'subtext' => 'General Chair & Superuser',
                'badge' => 'Superuser',
            ],
        ];
    }

    /**
     * Nilai default matriks.
     */
    protected function getDefaultMatrix(): array
    {
        return [
            'submit_abstract' => [
                'participant' => 'full_access',
                'secretariat' => 'view_only',
                'treasurer' => 'restricted',
                'abstract_team' => 'view_only',
                'admin' => 'full_access',
            ],
            'review_abstracts' => [
                'participant' => 'restricted',
                'secretariat' => 'full_access',
                'treasurer' => 'restricted',
                'abstract_team' => 'full_access',
                'admin' => 'full_access',
            ],
            'accept_reject' => [
                'participant' => 'restricted',
                'secretariat' => 'full_access',
                'treasurer' => 'restricted',
                'abstract_team' => 'full_access',
                'admin' => 'full_access',
            ],
            'pay_registration' => [
                'participant' => 'full_access',
                'secretariat' => 'restricted',
                'treasurer' => 'view_only',
                'abstract_team' => 'restricted',
                'admin' => 'full_access',
            ],
            'verify_payment' => [
                'participant' => 'restricted',
                'secretariat' => 'view_only',
                'treasurer' => 'full_access',
                'abstract_team' => 'restricted',
                'admin' => 'full_access',
            ],
            'manage_tariffs' => [
                'participant' => 'restricted',
                'secretariat' => 'restricted',
                'treasurer' => 'full_access',
                'abstract_team' => 'restricted',
                'admin' => 'full_access',
            ],
            'scan_attendance_qr' => [
                'participant' => 'restricted',
                'secretariat' => 'full_access',
                'treasurer' => 'view_only',
                'abstract_team' => 'restricted',
                'admin' => 'full_access',
            ],
            'manage_website_content' => [
                'participant' => 'restricted',
                'secretariat' => 'full_access',
                'treasurer' => 'restricted',
                'abstract_team' => 'restricted',
                'admin' => 'full_access',
            ],
            'manage_users' => [
                'participant' => 'restricted',
                'secretariat' => 'restricted',
                'treasurer' => 'restricted',
                'abstract_team' => 'restricted',
                'admin' => 'full_access',
            ],
            'manage_document_templates' => [
                'participant' => 'restricted',
                'secretariat' => 'view_only',
                'treasurer' => 'view_only',
                'abstract_team' => 'restricted',
                'admin' => 'full_access',
            ],
        ];
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
