<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantFileRequest;
use App\Models\ParticipantFile;
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

class ParticipantFileController extends Controller
{
    /**
     * Memantau seluruh daftar berkas peserta (Presentasi & Artikel) beserta status validasinya (KF-16, KF-17).
     */
    #[OA\Get(
        path: '/api/registrations/{id_registration}/files',
        summary: 'Daftar Berkas Peserta (PPT / Full Paper)',
        description: 'Mendapatkan daftar seluruh berkas presentasi dan artikel lengkap yang diunggah untuk pendaftaran tertentu.',
        tags: ['Berkas Peserta'],
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
                description: 'Daftar berkas peserta berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
        ]
    )]
    public function index(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant.userAccount', 'participantFiles'])
            ->findOrFail($id_registration);

        /** @var UserAccount $user */
        $user = Auth::user();

        if (! $this->canAccessRegistrationFiles($registration, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk memantau berkas pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $files = $registration->participantFiles->map(function ($file) {
            return [
                'id_file' => $file->id_file,
                'id_registration' => $file->id_registration,
                'jenis_file' => $file->jenis_file,
                'nama_file' => $file->nama_file,
                'status_file' => $file->status_file,
                'tanggal_upload' => $file->tanggal_upload,
                'file_url' => Storage::disk('public')->exists($file->file_path)
                    ? Storage::disk('public')->url($file->file_path)
                    : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $files,
        ], Response::HTTP_OK);
    }

    /**
     * Mengunggah berkas Presentasi (PPT) atau Artikel Lengkap (KF-16).
     */
    #[OA\Post(
        path: '/api/registrations/{id_registration}/files',
        summary: 'Unggah Berkas Presentasi / Artikel Lengkap',
        description: 'Mengunggah file presentasi (PPT/PPTX) atau naskah artikel lengkap (DOC/DOCX/PDF).',
        tags: ['Berkas Peserta'],
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
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['jenis_file', 'file'],
                    properties: [
                        new OA\Property(property: 'jenis_file', type: 'string', enum: ['Presentasi', 'Artikel'], example: 'Presentasi'),
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Berkas berhasil diunggah',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 422, description: 'Format berkas tidak sesuai kriteria'),
        ]
    )]
    public function store(StoreParticipantFileRequest $request, int $id_registration): JsonResponse|RedirectResponse
    {
        $registration = Registration::with(['participant.userAccount'])->findOrFail($id_registration);

        /** @var UserAccount $user */
        $user = Auth::user();

        if (! $this->canAccessRegistrationFiles($registration, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunggah berkas pada pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();
        $filePath = $file->store('participant_files', 'public');

        $participantFile = ParticipantFile::create([
            'id_registration' => $registration->id_registration,
            'jenis_file' => $validated['jenis_file'],
            'nama_file' => $originalName,
            'file_path' => $filePath,
            'tanggal_upload' => now(),
            'status_file' => 'Diupload',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Berkas {$validated['jenis_file']} berhasil diunggah dengan status Diupload.",
                'data' => [
                    'id_file' => $participantFile->id_file,
                    'id_registration' => $participantFile->id_registration,
                    'jenis_file' => $participantFile->jenis_file,
                    'nama_file' => $participantFile->nama_file,
                    'status_file' => $participantFile->status_file,
                    'tanggal_upload' => $participantFile->tanggal_upload,
                    'file_url' => Storage::disk('public')->url($filePath),
                ],
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('api.registrations.files.index', $id_registration)
            ->with('success', "Berkas {$validated['jenis_file']} berhasil diunggah.");
    }

    /**
     * Menampilkan status berkas tertentu (KF-17).
     */
    #[OA\Get(
        path: '/api/participant-files/{id_file}',
        summary: 'Detail Berkas Peserta',
        description: 'Mendapatkan status dan informasi detail berkas tertentu.',
        tags: ['Berkas Peserta'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_file',
                in: 'path',
                required: true,
                description: 'ID Berkas',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail berkas berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Berkas tidak ditemukan'),
        ]
    )]
    public function show(int $id_file): JsonResponse
    {
        $participantFile = ParticipantFile::with('registration.participant.userAccount')
            ->findOrFail($id_file);

        /** @var UserAccount $user */
        $user = Auth::user();

        if (! $participantFile->registration || ! $this->canAccessRegistrationFiles($participantFile->registration, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat berkas ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_file' => $participantFile->id_file,
                'id_registration' => $participantFile->id_registration,
                'jenis_file' => $participantFile->jenis_file,
                'nama_file' => $participantFile->nama_file,
                'status_file' => $participantFile->status_file,
                'tanggal_upload' => $participantFile->tanggal_upload,
                'file_url' => Storage::disk('public')->exists($participantFile->file_path)
                    ? Storage::disk('public')->url($participantFile->file_path)
                    : null,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Mengunduh berkas peserta (Presentasi/Artikel).
     */
    #[OA\Get(
        path: '/api/participant-files/{id_file}/download',
        summary: 'Unduh Berkas Peserta',
        description: 'Mengunduh file fisik berkas presentasi atau artikel lengkap peserta.',
        tags: ['Berkas Peserta'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_file',
                in: 'path',
                required: true,
                description: 'ID Berkas',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File berhasil diunduh',
                content: new OA\MediaType(mediaType: 'application/octet-stream')
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Berkas tidak ditemukan'),
        ]
    )]
    public function download(int $id_file): BinaryFileResponse|JsonResponse
    {
        $participantFile = ParticipantFile::with('registration.participant.userAccount')
            ->findOrFail($id_file);

        /** @var UserAccount $user */
        $user = Auth::user();

        if (! $participantFile->registration || ! $this->canAccessRegistrationFiles($participantFile->registration, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunduh berkas ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! Storage::disk('public')->exists($participantFile->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas tidak ditemukan pada server.',
            ], Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->download(
            $participantFile->file_path,
            $participantFile->nama_file
        );
    }

    private function canAccessRegistrationFiles(Registration $registration, UserAccount $user): bool
    {
        return $registration->participant?->id_user === $user->id_user
            || app(PermissionMatrix::class)->allows($user, 'manage_participant_files');
    }
}
