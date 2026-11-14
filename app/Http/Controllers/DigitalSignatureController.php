<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDigitalSignatureRequest;
use App\Models\DigitalSignature;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class DigitalSignatureController extends Controller
{
    /**
     * Menampilkan daftar tanda tangan dan stempel digital per peran.
     */
    #[OA\Get(
        path: '/api/digital-signatures',
        summary: 'Daftar Tanda Tangan dan Stempel Digital Peran',
        description: 'Mendapatkan daftar seluruh tanda tangan digital dan stempel kepanitiaan berdasarkan role.',
        tags: ['Tanda Tangan Digital'],
        security: [['sanctumAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar tanda tangan digital berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $signatures = DigitalSignature::with('role')->get();

        return response()->json([
            'success' => true,
            'data' => $signatures,
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan tanda tangan & stempel untuk suatu peran (Role).
     */
    #[OA\Get(
        path: '/api/digital-signatures/role/{id_role}',
        summary: 'Tanda Tangan dan Stempel Digital Berdasarkan Role',
        description: 'Mendapatkan berkas tanda tangan dan stempel untuk peran kepanitiaan tertentu.',
        tags: ['Tanda Tangan Digital'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_role',
                in: 'path',
                required: true,
                description: 'ID Peran (Role)',
                schema: new OA\Schema(type: 'integer', example: 3)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Berkas tanda tangan digital peran ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'ttd_url', type: 'string'),
                        new OA\Property(property: 'stempel_url', type: 'string', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Berkas belum diunggah untuk peran ini'),
        ]
    )]
    public function showByRole(int $id_role): JsonResponse
    {
        $signature = DigitalSignature::with('role')
            ->where('id_role', $id_role)
            ->latest('tanggal_upload')
            ->first();

        if (! $signature) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas tanda tangan digital dan stempel untuk peran ini belum diunggah.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $signature,
            'ttd_url' => $signature->ttd_url,
            'stempel_url' => $signature->stempel_url,
        ], Response::HTTP_OK);
    }

    /**
     * Mengunggah atau memperbarui tanda tangan dan stempel digital berbasis peran (Poin 6).
     */
    #[OA\Post(
        path: '/api/digital-signatures',
        summary: 'Unggah / Perbarui Tanda Tangan dan Stempel Digital Peran',
        description: 'Menyimpan berkas transparan tanda tangan digital dan stempel kepanitiaan (Khusus pemilik peran atau Admin).',
        tags: ['Tanda Tangan Digital'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['id_role', 'file_ttd'],
                    properties: [
                        new OA\Property(property: 'id_role', type: 'integer', example: 3),
                        new OA\Property(property: 'file_ttd', type: 'string', format: 'binary'),
                        new OA\Property(property: 'file_stempel', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tanda tangan dan stempel digital berhasil disimpan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'ttd_url', type: 'string'),
                        new OA\Property(property: 'stempel_url', type: 'string', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak (bukan pemilik peran atau Admin)'),
        ]
    )]
    public function store(StoreDigitalSignatureRequest $request): JsonResponse
    {
        $targetRoleId = $request->integer('id_role');

        $targetRole = Role::findOrFail($targetRoleId);

        $validated = $request->validated();

        $ttdFile = $request->file('file_ttd');
        $ttdPath = $ttdFile->store('signatures/ttd', 'public');

        $stempelPath = null;
        if ($request->hasFile('file_stempel')) {
            $stempelFile = $request->file('file_stempel');
            $stempelPath = $stempelFile->store('signatures/stempel', 'public');
        }

        $existing = DigitalSignature::where('id_role', $targetRoleId)->first();
        if ($existing) {
            if ($existing->file_ttd) {
                Storage::disk('public')->delete($existing->file_ttd);
            }
            if ($existing->file_stempel && $stempelPath) {
                Storage::disk('public')->delete($existing->file_stempel);
            }
        }

        $signature = DigitalSignature::updateOrCreate(
            ['id_role' => $targetRoleId],
            [
                'file_ttd' => $ttdPath,
                'file_stempel' => $stempelPath ?? ($existing?->file_stempel),
                'tanggal_upload' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Tanda tangan dan stempel digital untuk peran '{$targetRole->nama_role}' berhasil disimpan.",
            'data' => $signature,
            'ttd_url' => Storage::disk('public')->url($ttdPath),
            'stempel_url' => $signature->file_stempel ? Storage::disk('public')->url($signature->file_stempel) : null,
        ], Response::HTTP_OK);
    }
}
