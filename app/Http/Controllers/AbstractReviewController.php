<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewAbstractRequest;
use App\Models\AbstractReview;
use App\Models\AbstractSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AbstractReviewController extends Controller
{
    /**
     * Menampilkan daftar seluruh abstrak masuk untuk antrean Tim Abstrak (KF-18 & Revisi 7).
     */
    #[OA\Get(
        path: '/api/tim-abstrak/submissions',
        summary: 'Antrean Naskah Abstrak Masuk',
        description: 'Menampilkan daftar seluruh naskah abstrak peserta yang diajukan untuk dinilai oleh Tim Abstrak.',
        tags: ['Review Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status_abstrak',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan status abstrak (Diajukan, Diterima, Ditolak, Revisi)',
                schema: new OA\Schema(type: 'string', example: 'Diajukan')
            ),
            new OA\Parameter(
                name: 'scope',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan scope / bidang topik',
                schema: new OA\Schema(type: 'string', example: 'Agronomy')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar naskah abstrak berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak (khusus Tim Abstrak)')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = AbstractSubmission::with([
            'registration.participant.userAccount',
            'registration.event',
            'latestReview.reviewer',
        ])->orderBy('tanggal_submit', 'desc');

        if ($request->filled('status_abstrak')) {
            $query->where('status_abstrak', $request->string('status_abstrak'));
        }

        if ($request->filled('scope')) {
            $query->where('scope', $request->string('scope'));
        }

        $submissions = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Daftar naskah abstrak masuk berhasil dimuat.',
            'data' => $submissions,
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan detail spesifik naskah abstrak beserta riwayat review.
     */
    #[OA\Get(
        path: '/api/tim-abstrak/submissions/{id_abstract}',
        summary: 'Detail Naskah Abstrak dan Riwayat Review',
        description: 'Mendapatkan informasi detail naskah abstrak, data peserta author, dan seluruh riwayat review yang pernah dilakukan.',
        tags: ['Review Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_abstract',
                in: 'path',
                required: true,
                description: 'ID Pengajuan Abstrak',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail naskah abstrak berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'file_url', type: 'string', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Naskah abstrak tidak ditemukan')
        ]
    )]
    public function show(int $id_abstract): JsonResponse
    {
        $abstract = AbstractSubmission::with([
            'registration.participant.userAccount',
            'registration.event',
            'reviews.reviewer',
        ])->findOrFail($id_abstract);

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
     * Memproses penilaian dan review seleksi naskah abstrak oleh Tim Abstrak (KF-18, KF-19).
     */
    #[OA\Post(
        path: '/api/tim-abstrak/submissions/{id_abstract}/review',
        summary: 'Kirim Keputusan Review Abstrak',
        description: 'Menyimpan penilaian seleksi abstrak (Diterima, Ditolak, Revisi) beserta catatan evaluasi reviewer.',
        tags: ['Review Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_abstract',
                in: 'path',
                required: true,
                description: 'ID Pengajuan Abstrak',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['hasil_review'],
                properties: [
                    new OA\Property(property: 'hasil_review', type: 'string', enum: ['Diterima', 'Ditolak', 'Revisi'], example: 'Diterima'),
                    new OA\Property(property: 'catatan_review', type: 'string', example: 'Abstrak memenuhi kriteria konferensi. Silakan persiapkan slide presentasi.')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Keputusan review berhasil disimpan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Status hasil review tidak valid')
        ]
    )]
    public function review(ReviewAbstractRequest $request, int $id_abstract): JsonResponse
    {
        $abstract = AbstractSubmission::with('registration.participant')->findOrFail($id_abstract);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();
        $validated = $request->validated();

        $review = AbstractReview::create([
            'id_abstract' => $abstract->id_abstract,
            'id_reviewer' => $user->id_user,
            'tanggal_review' => now(),
            'hasil_review' => $validated['hasil_review'],
            'catatan_review' => $validated['catatan_review'] ?? null,
        ]);

        $abstract->update([
            'status_abstrak' => $validated['hasil_review'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Review abstrak berhasil disimpan dengan hasil '{$validated['hasil_review']}'.",
            'data' => [
                'id_review' => $review->id_review,
                'id_abstract' => $abstract->id_abstract,
                'judul_abstrak' => $abstract->judul_abstrak,
                'scope' => $abstract->scope,
                'status_abstrak' => $abstract->status_abstrak,
                'hasil_review' => $review->hasil_review,
                'catatan_review' => $review->catatan_review,
                'reviewer' => $user->email,
                'tanggal_review' => $review->tanggal_review,
            ],
        ], Response::HTTP_OK);
    }
}
