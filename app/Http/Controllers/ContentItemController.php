<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentItemRequest;
use App\Http\Requests\ReorderContentRequest;
use App\Http\Resources\ContentItemResource;
use App\Support\Audit;
use App\Support\Content\ContentTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengelolaan konten publik sederhana (Fase 1): event-content, important-dates, faqs, contacts,
 * sponsorship-packages. Satu controller, jenis dari default route `content_type`.
 */
class ContentItemController extends Controller
{
    private const TYPE_ENUM = ['event-content', 'important-dates', 'faqs', 'contacts', 'sponsorship-packages'];

    private function type(Request $request): string
    {
        return (string) $request->route()->defaults['content_type'];
    }

    #[OA\Get(
        path: '/api/content/{content_type}',
        summary: 'Daftar konten publik untuk pengelola',
        description: 'content_type: event-content, important-dates, faqs, contacts, sponsorship-packages. Izin manage_website_content (view_only).',
        tags: ['Konten Publik'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'content_type', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: self::TYPE_ENUM)),
            new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['Draft', 'Dipublikasikan'])),
            new OA\Parameter(name: 'section', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', maximum: 200)),
        ],
        responses: [new OA\Response(response: 200, description: 'Daftar konten'), new OA\Response(response: 403, description: 'Tanpa izin')]
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_event' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::in(ContentTypes::PUBLISH_STATUSES)],
            'section' => ['sometimes', 'string', 'max:40'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ]);
        $def = ContentTypes::get($this->type($request));
        $query = $def['model']::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('section') && $this->type($request) === 'event-content', fn ($q) => $q->where('section', $request->string('section')->toString()));
        foreach ($def['order'] as $column) {
            $query->orderBy($column);
        }
        $page = $query->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => ContentItemResource::collection($page->getCollection())->resolve(),
            'pagination' => ['total' => $page->total(), 'per_page' => $page->perPage(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
        ]);
    }

    #[OA\Get(path: '/api/content/{content_type}/{content_id}', summary: 'Detail konten', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'content_type', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: self::TYPE_ENUM)), new OA\Parameter(name: 'content_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detail'), new OA\Response(response: 404, description: 'Tidak ditemukan')])]
    public function show(Request $request, int $content_id): JsonResponse
    {
        $item = ContentTypes::get($this->type($request))['model']::findOrFail($content_id);

        return response()->json(['success' => true, 'data' => (new ContentItemResource($item))->resolve()]);
    }

    #[OA\Post(path: '/api/content/{content_type}', summary: 'Tambah konten (Draft bawaan)', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'content_type', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: self::TYPE_ENUM))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object', description: 'Kolom sesuai jenis; teks dua bahasa *_id/*_en (minimal satu bahasa); tautan wajib https:// atau mailto:.')),
        responses: [new OA\Response(response: 201, description: 'Tersimpan'), new OA\Response(response: 422, description: 'Validasi gagal'), new OA\Response(response: 403, description: 'Tanpa izin')])]
    public function store(ContentItemRequest $request): JsonResponse
    {
        $type = $this->type($request);
        $item = ContentTypes::get($type)['model']::create($request->validated());
        Audit::log('content.created', $type, $item->getKey());

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => (new ContentItemResource($item->fresh()))->resolve()], Response::HTTP_CREATED);
    }

    #[OA\Patch(path: '/api/content/{content_type}/{content_id}', summary: 'Ubah konten (termasuk status Draft/Dipublikasikan)', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'content_type', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: self::TYPE_ENUM)), new OA\Parameter(name: 'content_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Diperbarui'), new OA\Response(response: 422, description: 'Validasi gagal')])]
    public function update(ContentItemRequest $request, int $content_id): JsonResponse
    {
        $type = $this->type($request);
        $item = ContentTypes::get($type)['model']::findOrFail($content_id);
        $item->update($request->validated());
        Audit::log('content.updated', $type, $item->getKey(), null, ['status' => $item->status]);

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => (new ContentItemResource($item->fresh()))->resolve()]);
    }

    #[OA\Delete(path: '/api/content/{content_type}/{content_id}', summary: 'Hapus konten', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'content_type', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: self::TYPE_ENUM)), new OA\Parameter(name: 'content_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Dihapus')])]
    public function destroy(Request $request, int $content_id): JsonResponse
    {
        $type = $this->type($request);
        $item = ContentTypes::get($type)['model']::findOrFail($content_id);
        $item->delete();
        Audit::log('content.deleted', $type, $content_id);

        return response()->json(['success' => true, 'message' => __('asic-content.api.deleted')]);
    }

    #[OA\Patch(path: '/api/content/{content_type}/reorder', summary: 'Ubah urutan konten', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'content_type', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: self::TYPE_ENUM))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['items'], properties: [new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [new OA\Property(property: 'id', type: 'integer'), new OA\Property(property: 'urutan', type: 'integer')]))])),
        responses: [new OA\Response(response: 200, description: 'Urutan tersimpan')])]
    public function reorder(ReorderContentRequest $request): JsonResponse
    {
        $def = ContentTypes::get($this->type($request));
        DB::transaction(function () use ($request, $def): void {
            foreach ($request->validated('items') as $row) {
                $def['model']::whereKey($row['id'])->update(['urutan' => $row['urutan']]);
            }
        });

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved')]);
    }
}
