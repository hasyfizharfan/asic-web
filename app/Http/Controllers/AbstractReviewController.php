<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewAbstractRequest;
use App\Http\Resources\AbstractSubmissionResource;
use App\Models\AbstractReview;
use App\Models\AbstractSubmission;
use App\Models\Role;
use App\Models\UserAccount;
use App\Services\AbstractDecisionProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                description: 'Filter berdasarkan status abstrak (Diajukan, Diterima, Ditolak)',
                schema: new OA\Schema(type: 'string', example: 'Diajukan')
            ),
            new OA\Parameter(
                name: 'scope',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan scope / bidang topik',
                schema: new OA\Schema(type: 'string', example: 'Agronomy')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar naskah abstrak berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak (khusus Tim Abstrak)'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isReviewer = $user?->role?->nama_role === Role::ROLE_TIM_ABSTRAK;
        $query = AbstractSubmission::query();
        if ($isReviewer) {
            $query->with(['registration', 'reviews' => fn ($q) => $q->orderBy('tanggal_review')]);
        } else {
            $query->with(['registration.participant.userAccount', 'latestReview.reviewer']);
        }
        if ($user?->role?->nama_role === Role::ROLE_PESERTA) {
            $query->whereHas('registration', fn ($q) => $q->where('id_participant', $user->participant?->id_participant ?? 0));
        }

        if ($request->filled('status_abstrak')) {
            $query->where('status_abstrak', $request->string('status_abstrak'));
        }

        if ($request->filled('scope')) {
            $query->where('scope', $request->string('scope'));
        }

        if ($request->filled('id_event')) {
            $query->whereHas('registration', fn ($q) => $q->where('id_event', $request->integer('id_event')));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('judul_abstrak', 'like', '%'.$search.'%');
                if (ctype_digit($search)) {
                    $q->orWhere('id_abstract', (int) $search);
                }
            });
        }

        if ($isReviewer && $request->has('reviewed_by_me')) {
            $reviewed = filter_var($request->query('reviewed_by_me'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($reviewed !== null) {
                $query->{$reviewed ? 'whereHas' : 'whereDoesntHave'}('reviews', fn ($q) => $q->where('id_reviewer', $user->id_user));
            }
        }

        $submissions = $query->orderByDesc('tanggal_submit')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        $page = $submissions->toArray();
        $page['data'] = collect($submissions->items())->map(fn ($item) => (new AbstractSubmissionResource($item))->resolve($request));
        $stats = $isReviewer ? [
            'total' => AbstractSubmission::count(),
            'pending' => AbstractSubmission::where('status_abstrak', 'Diajukan')->count(),
            'reviewed_by_me' => AbstractSubmission::whereHas('reviews', fn ($q) => $q->where('id_reviewer', $user->id_user))->distinct('id_abstract')->count('id_abstract'),
        ] : null;

        return response()->json([
            'success' => true,
            'message' => 'Daftar naskah abstrak masuk berhasil dimuat.',
            'data' => $page,
            ...($isReviewer ? ['stats' => $stats, 'scopes' => config('abstract-scopes', [])] : []),
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan detail spesifik naskah abstrak beserta riwayat review.
     */
    #[OA\Get(
        path: '/api/tim-abstrak/submissions/{id_abstract}',
        summary: 'Detail Naskah Abstrak dan Riwayat Review',
        description: 'Mendapatkan metadata abstrak dan seluruh riwayat keputusan review sesuai hak akses pengguna.',
        tags: ['Review Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_abstract',
                in: 'path',
                required: true,
                description: 'ID Pengajuan Abstrak',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail naskah abstrak berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Naskah abstrak tidak ditemukan'),
        ]
    )]
    public function show(Request $request, int $id_abstract): JsonResponse
    {
        $isReviewer = $request->user()?->role?->nama_role === Role::ROLE_TIM_ABSTRAK;
        $query = AbstractSubmission::with(['registration.event', 'reviews' => fn ($q) => $q->orderBy('tanggal_review')]);
        if (! $isReviewer) {
            $query->with(['registration.participant.userAccount', 'latestReview.reviewer']);
        }
        $abstract = $query->findOrFail($id_abstract);
        if ($request->user()?->role?->nama_role === Role::ROLE_PESERTA) {
            abort_unless($abstract->registration?->id_participant === $request->user()->participant?->id_participant, Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => (new AbstractSubmissionResource($abstract))->resolve($request),
        ], Response::HTTP_OK);
    }

    public function download(Request $request, int $id_abstract)
    {
        $abstract = AbstractSubmission::with('registration')->findOrFail($id_abstract);
        if ($request->user()?->role?->nama_role === Role::ROLE_PESERTA) {
            abort_unless($abstract->registration?->id_participant === $request->user()->participant?->id_participant, Response::HTTP_FORBIDDEN);
        }
        abort_unless($abstract->file_abstrak && Storage::disk('public')->exists($abstract->file_abstrak), Response::HTTP_NOT_FOUND);
        $extension = Str::lower(pathinfo($abstract->file_abstrak, PATHINFO_EXTENSION));
        abort_unless(in_array($extension, ['pdf', 'doc', 'docx'], true), Response::HTTP_NOT_FOUND);

        return Storage::disk('public')->download($abstract->file_abstrak, 'abstract-'.$abstract->id_abstract.'.'.$extension);
    }

    /**
     * Memproses penilaian dan review seleksi naskah abstrak oleh Tim Abstrak (KF-18, KF-19).
     */
    #[OA\Post(
        path: '/api/tim-abstrak/submissions/{id_abstract}/review',
        summary: 'Kirim Keputusan Review Abstrak',
        description: 'Menyimpan keputusan final seleksi abstrak (Diterima atau Ditolak) beserta catatan evaluasi reviewer.',
        tags: ['Review Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_abstract',
                in: 'path',
                required: true,
                description: 'ID Pengajuan Abstrak',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['hasil_review'],
                properties: [
                    new OA\Property(property: 'hasil_review', type: 'string', enum: ['Diterima', 'Ditolak'], example: 'Diterima'),
                    new OA\Property(property: 'catatan_review', type: 'string', example: 'Abstrak memenuhi kriteria konferensi. Silakan persiapkan slide presentasi.'),
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
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Status hasil review tidak valid'),
        ]
    )]
    public function review(ReviewAbstractRequest $request, int $id_abstract): JsonResponse
    {
        /** @var UserAccount $user */
        $user = Auth::user();
        $validated = $request->validated();

        // Transaksi + lock agar dua reviewer tidak memutus abstrak yang sama bersamaan.
        $outcome = DB::transaction(function () use ($id_abstract, $user, $validated) {
            $abstract = AbstractSubmission::with('registration.participant')
                ->whereKey($id_abstract)->lockForUpdate()->first();

            if (! $abstract) {
                return ['error' => 404, 'message' => 'Abstrak tidak ditemukan.'];
            }
            if ($abstract->registration?->registration_status !== 'Terdaftar') {
                return ['error' => 422, 'message' => 'Registrasi pemilik abstrak tidak berstatus Terdaftar sehingga tidak dapat direview.'];
            }
            if ($abstract->status_abstrak !== 'Diajukan') {
                return ['error' => 409, 'message' => "Abstrak sudah diputuskan ({$abstract->status_abstrak}) dan tidak dapat direview ulang."];
            }

            $review = AbstractReview::create([
                'id_abstract' => $abstract->id_abstract,
                'id_reviewer' => $user->id_user,
                'tanggal_review' => now(),
                'hasil_review' => $validated['hasil_review'],
                'catatan_review' => $validated['catatan_review'] ?? null,
            ]);
            $abstract->update(['status_abstrak' => $validated['hasil_review']]);

            return ['abstract' => $abstract, 'review' => $review];
        });

        if (isset($outcome['error'])) {
            return response()->json(['success' => false, 'message' => $outcome['message']], $outcome['error']);
        }

        /** @var AbstractSubmission $abstract */
        $abstract = $outcome['abstract'];
        $review = $outcome['review'];
        Log::info('abstract.reviewed', ['id_abstract' => $abstract->id_abstract, 'reviewer' => $user->id_user, 'hasil' => $review->hasil_review]);

        // Tindak lanjut (invoice/LOA/penolakan) di luar transaksi keputusan; kegagalan tidak membatalkan keputusan.
        $followUp = app(AbstractDecisionProcessor::class)->process($abstract->fresh());

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
                'documents' => collect($followUp['documents'])->map(fn ($d) => $d ? [
                    'id_document' => $d->id_document, 'jenis_dokumen' => $d->jenis_dokumen, 'nomor_dokumen' => $d->nomor_dokumen,
                ] : null),
                'invoice' => $followUp['invoice'],
                'warnings' => $followUp['warnings'],
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Mencoba ulang penerbitan dokumen (LOA/penolakan/invoice) untuk abstrak yang sudah diputuskan.
     * Idempoten: dokumen yang sudah terbit tidak dibuat ganda.
     */
    #[OA\Post(
        path: '/api/tim-abstrak/submissions/{id_abstract}/issue-documents',
        summary: 'Terbitkan Ulang/Susulan Dokumen Hasil Seleksi',
        description: 'Mengeksekusi ulang penerbitan LOA/surat penolakan/invoice bagi abstrak yang sudah diputuskan. Tidak membuat dokumen ganda.',
        tags: ['Review Abstrak'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_abstract', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Dokumen diproses'),
            new OA\Response(response: 409, description: 'Abstrak belum diputuskan'),
        ]
    )]
    public function issueDocuments(int $id_abstract): JsonResponse
    {
        $abstract = AbstractSubmission::find($id_abstract);
        if (! $abstract) {
            return response()->json(['success' => false, 'message' => 'Abstrak tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }
        if ($abstract->status_abstrak === 'Diajukan') {
            return response()->json(['success' => false, 'message' => 'Abstrak belum diputuskan.'], Response::HTTP_CONFLICT);
        }

        $followUp = app(AbstractDecisionProcessor::class)->process($abstract);

        return response()->json([
            'success' => true,
            'message' => 'Penerbitan dokumen diproses.',
            'data' => [
                'documents' => collect($followUp['documents'])->map(fn ($d) => $d ? ['id_document' => $d->id_document, 'jenis_dokumen' => $d->jenis_dokumen, 'nomor_dokumen' => $d->nomor_dokumen] : null),
                'invoice' => $followUp['invoice'],
                'warnings' => $followUp['warnings'],
            ],
        ], Response::HTTP_OK);
    }
}
