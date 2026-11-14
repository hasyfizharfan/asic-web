<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbstractRequest;
use App\Models\AbstractSubmission;
use App\Models\Registration;
use App\Models\UserAccount;
use App\Support\PermissionMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AbstractController extends Controller
{
    /**
     * Menampilkan data abstrak dari suatu pendaftaran (KF-15, KF-17).
     */
    #[OA\Get(
        path: '/api/registrations/{id_registration}/abstract',
        summary: 'Detail Naskah Abstrak Peserta',
        description: 'Mendapatkan data naskah abstrak yang telah diajukan pada pendaftaran tertentu.',
        tags: ['Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_registration',
                in: 'path',
                required: true,
                description: 'ID Pendaftaran',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data naskah abstrak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'file_url', type: 'string', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Naskah abstrak belum diajukan'),
        ]
    )]
    public function show(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant.userAccount', 'abstractSubmission'])
            ->findOrFail($id_registration);

        /** @var UserAccount $user */
        $user = Auth::user();

        $isOwner = $registration->participant?->id_user === $user->id_user;
        $canReviewAny = app(PermissionMatrix::class)->allows($user, 'view_abstracts');
        if (! $isOwner && ! $canReviewAny) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat data abstrak ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $abstract = $registration->abstractSubmission;

        if (! $abstract) {
            return response()->json([
                'success' => false,
                'message' => 'Naskah abstrak belum dikirimkan untuk pendaftaran ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        $fileUrl = $abstract->file_abstrak && Storage::disk('public')->exists($abstract->file_abstrak)
            ? Storage::disk('public')->url($abstract->file_abstrak)
            : null;

        return response()->json([
            'success' => true,
            'data' => $abstract,
            'file_url' => $fileUrl,
        ], Response::HTTP_OK);
    }

    /**
     * Mengunggah naskah abstrak untuk Presenter Oral/Poster (KF-15).
     */
    #[OA\Post(
        path: '/api/registrations/{id_registration}/abstract',
        summary: 'Unggah Naskah Abstrak Baru',
        description: 'Mengunggah dokumen abstrak format PDF/DOCX untuk pendaftaran peserta kategori Presenter.',
        tags: ['Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_registration',
                in: 'path',
                required: true,
                description: 'ID Pendaftaran',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Berkas naskah abstrak dan metadata presentasi',
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['judul_abstrak', 'scope', 'metode_presentasi', 'file_abstrak'],
                    properties: [
                        new OA\Property(property: 'judul_abstrak', type: 'string', example: 'Smart Agriculture IoT Implementation in West Sumatra'),
                        new OA\Property(property: 'scope', type: 'string', enum: ['CP', 'PB', 'PP', 'SM', 'SE', 'OT'], example: 'CP'),
                        new OA\Property(property: 'metode_presentasi', type: 'string', enum: ['Oral', 'Poster'], example: 'Oral'),
                        new OA\Property(property: 'file_abstrak', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Naskah abstrak berhasil diunggah',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'file_url', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 422, description: 'Kategori Listener tidak boleh upload abstrak atau file tidak valid'),
        ]
    )]
    public function store(StoreAbstractRequest $request, int $id_registration): JsonResponse|RedirectResponse
    {
        $registration = Registration::with(['participant.userAccount', 'abstractSubmission'])
            ->findOrFail($id_registration);

        /** @var UserAccount $user */
        $user = Auth::user();

        $isOwner = $registration->participant?->id_user === $user->id_user;
        $canManageAny = app(PermissionMatrix::class)->allows($user, 'manage_registrations');
        if (! $isOwner && ! $canManageAny) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunggah abstrak pada pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($registration->participation_type === 'Listener') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta kategori Listener tidak perlu mengirimkan naskah abstrak.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validated();
        $file = $request->file('file_abstrak');

        $filePath = $file->store('abstracts', 'public');

        $existingAbstract = $registration->abstractSubmission;
        if ($existingAbstract && $existingAbstract->file_abstrak) {
            Storage::disk('public')->delete($existingAbstract->file_abstrak);
        }

        $abstract = AbstractSubmission::updateOrCreate(
            ['id_registration' => $registration->id_registration],
            [
                'judul_abstrak' => $validated['judul_abstrak'],
                'scope' => $validated['scope'] ?? null,
                'metode_presentasi' => $validated['metode_presentasi'],
                'file_abstrak' => $filePath,
                'tanggal_submit' => now(),
                'status_abstrak' => 'Diajukan',
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Naskah abstrak berhasil diunggah dengan status Diajukan.',
                'data' => $abstract,
                'file_url' => Storage::disk('public')->url($filePath),
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('api.registrations.show', $id_registration)
            ->with('success', 'Naskah abstrak berhasil diunggah.');
    }

    /**
     * Mengunduh berkas naskah abstrak.
     */
    #[OA\Get(
        path: '/api/registrations/{id_registration}/abstract/download',
        summary: 'Unduh Berkas Naskah Abstrak',
        description: 'Mengunduh file fisik naskah abstrak peserta.',
        tags: ['Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_registration',
                in: 'path',
                required: true,
                description: 'ID Pendaftaran',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File naskah abstrak berhasil diunduh',
                content: new OA\MediaType(mediaType: 'application/octet-stream')
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Berkas tidak ditemukan'),
        ]
    )]
    public function download(int $id_registration): BinaryFileResponse|JsonResponse
    {
        $registration = Registration::with(['participant.userAccount', 'abstractSubmission'])
            ->findOrFail($id_registration);

        /** @var UserAccount $user */
        $user = Auth::user();

        $isOwner = $registration->participant?->id_user === $user->id_user;
        $canReviewAny = app(PermissionMatrix::class)->allows($user, 'view_abstracts');
        if (! $isOwner && ! $canReviewAny) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunduh naskah abstrak ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $abstract = $registration->abstractSubmission;
        if (! $abstract || ! $abstract->file_abstrak || ! Storage::disk('public')->exists($abstract->file_abstrak)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas naskah abstrak tidak ditemukan di server.',
            ], Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->download($abstract->file_abstrak);
    }
}
