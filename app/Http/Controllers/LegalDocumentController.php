<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegalDocumentRequest;
use App\Http\Requests\UpdateLegalDocumentRequest;
use App\Http\Resources\ContentItemResource;
use App\Models\LegalDocument;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * KF-35: syarat & ketentuan, kebijakan privasi, dan etika publikasi berversi.
 * Hanya satu versi Dipublikasikan per jenis; isi yang sudah terbit tidak dapat diubah (buat versi baru).
 */
class LegalDocumentController extends Controller
{
    #[OA\Get(path: '/api/content/legal-documents', summary: 'Daftar versi dokumen hukum', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'jenis', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: LegalDocument::TYPES))],
        responses: [new OA\Response(response: 200, description: 'Daftar versi + jenis yang belum terpublikasi')])]
    public function index(Request $request): JsonResponse
    {
        $rows = LegalDocument::query()
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->string('jenis')->toString()))
            ->orderBy('jenis')->orderByDesc('id_legal')->get();
        $published = LegalDocument::where('status', 'Dipublikasikan')->pluck('jenis')->all();

        return response()->json([
            'success' => true,
            'data' => ContentItemResource::collection($rows)->resolve(),
            'missing_published' => array_values(array_diff(LegalDocument::TYPES, $published)),
        ]);
    }

    #[OA\Get(path: '/api/content/legal-documents/{id_legal}', summary: 'Detail versi', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_legal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Detail')])]
    public function show(int $id_legal): JsonResponse
    {
        return response()->json(['success' => true, 'data' => (new ContentItemResource(LegalDocument::findOrFail($id_legal)))->resolve()]);
    }

    #[OA\Post(path: '/api/content/legal-documents', summary: 'Buat draft versi baru', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Draft tersimpan'), new OA\Response(response: 422, description: 'Validasi gagal (versi ganda per jenis)')])]
    public function store(StoreLegalDocumentRequest $request): JsonResponse
    {
        $doc = LegalDocument::create($request->validated() + ['status' => 'Draft']);

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => (new ContentItemResource($doc->fresh()))->resolve()], Response::HTTP_CREATED);
    }

    #[OA\Patch(path: '/api/content/legal-documents/{id_legal}', summary: 'Ubah draft (versi terbit/terarsip tidak dapat diubah)', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_legal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Diperbarui'), new OA\Response(response: 409, description: 'Versi sudah terbit/terarsip')])]
    public function update(UpdateLegalDocumentRequest $request, int $id_legal): JsonResponse
    {
        $doc = LegalDocument::findOrFail($id_legal);
        if ($doc->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => __('asic-content.api.legal_locked')], Response::HTTP_CONFLICT);
        }
        $doc->update($request->validated());

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => (new ContentItemResource($doc->fresh()))->resolve()]);
    }

    #[OA\Delete(path: '/api/content/legal-documents/{id_legal}', summary: 'Hapus draft', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_legal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Dihapus'), new OA\Response(response: 409, description: 'Hanya draft yang dapat dihapus')])]
    public function destroy(int $id_legal): JsonResponse
    {
        $doc = LegalDocument::findOrFail($id_legal);
        if ($doc->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => __('asic-content.api.legal_locked')], Response::HTTP_CONFLICT);
        }
        $doc->delete();

        return response()->json(['success' => true, 'message' => __('asic-content.api.deleted')]);
    }

    #[OA\Post(path: '/api/content/legal-documents/{id_legal}/publish', summary: 'Publikasikan versi (mengarsipkan versi terbit sebelumnya)', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_legal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terbit'), new OA\Response(response: 409, description: 'Bukan draft'), new OA\Response(response: 422, description: 'Isi kosong')])]
    public function publish(int $id_legal): JsonResponse
    {
        $doc = LegalDocument::findOrFail($id_legal);
        if ($doc->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => __('asic-content.api.legal_locked')], Response::HTTP_CONFLICT);
        }
        if (trim((string) $doc->isi_id) === '' && trim((string) $doc->isi_en) === '') {
            return response()->json(['success' => false, 'message' => __('asic-content.validation.legal_empty')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($doc): void {
            LegalDocument::where('jenis', $doc->jenis)->where('status', 'Dipublikasikan')->lockForUpdate()->update(['status' => 'Diarsipkan']);
            $doc->update(['status' => 'Dipublikasikan']);
        });
        Audit::log('content.legal_published', 'legal_document', $doc->id_legal, null, ['jenis' => $doc->jenis, 'versi' => $doc->versi]);

        return response()->json(['success' => true, 'message' => __('asic-content.api.published'), 'data' => (new ContentItemResource($doc->fresh()))->resolve()]);
    }

    #[OA\Get(path: '/api/public/legal-documents/{jenis}', summary: 'Versi terpublikasi dokumen hukum (publik)', tags: ['Publik'],
        parameters: [new OA\Parameter(name: 'jenis', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: LegalDocument::TYPES))],
        responses: [new OA\Response(response: 200, description: 'Versi terbit'), new OA\Response(response: 404, description: 'Belum dipublikasikan')])]
    public function publicShow(string $jenis): JsonResponse
    {
        abort_unless(in_array($jenis, LegalDocument::TYPES, true), Response::HTTP_NOT_FOUND);
        $doc = LegalDocument::where('jenis', $jenis)->where('status', 'Dipublikasikan')->firstOrFail();

        return response()->json(['success' => true, 'data' => [
            'jenis' => $doc->jenis, 'versi' => $doc->versi, 'berlaku_mulai' => $doc->berlaku_mulai?->toDateString(),
            'judul' => $doc->localized('judul'), 'isi' => $doc->localized('isi'),
        ]]);
    }
}
