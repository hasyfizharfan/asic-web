<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/** KF-29: arsip publik edisi seminar berstatus Arsip (tanpa data pribadi). */
class PublicArchiveController extends Controller
{
    public function __construct(private readonly ArchiveService $archive) {}

    private function filters(Request $request): array
    {
        $request->validate(['q' => ['sometimes', 'nullable', 'string', 'max:100'], 'year' => ['sometimes', 'nullable', 'integer', 'min:2000', 'max:2100'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:50']]);

        return [$request->filled('q') ? $request->string('q')->toString() : null, $request->filled('year') ? $request->integer('year') : null];
    }

    #[OA\Get(path: '/api/public/archive', summary: 'Daftar edisi seminar terarsip', tags: ['Publik'],
        parameters: [new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'year', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Daftar edisi')])]
    public function index(Request $request): JsonResponse
    {
        [$q, $year] = $this->filters($request);
        $page = $this->archive->paginate($q, $year, $request->integer('per_page', 12));

        return response()->json(['success' => true,
            'data' => collect($page->items())->map(fn (Event $e) => ['id_event' => $e->id_event, 'nama_event' => $e->nama_event, 'tema' => $e->tema, 'tanggal_mulai' => $e->tanggal_mulai, 'tanggal_selesai' => $e->tanggal_selesai, 'lokasi' => $e->lokasi, 'jumlah_peserta' => $this->archive->stats($e)['registrations']])->all(),
            'pagination' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]]);
    }

    #[OA\Get(path: '/api/public/archive/{id_event}', summary: 'Detail edisi terarsip', tags: ['Publik'],
        parameters: [new OA\Parameter(name: 'id_event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detail'), new OA\Response(response: 404, description: 'Bukan edisi terarsip')])]
    public function show(int $id_event): JsonResponse
    {
        $event = Event::where('status_event', 'Arsip')->findOrFail($id_event);

        return response()->json(['success' => true, 'data' => $this->archive->detail($event)]);
    }

    public function page(Request $request): View
    {
        [$q, $year] = $this->filters($request);

        return view('public.archive', ['events' => $this->archive->paginate($q, $year), 'years' => $this->archive->years(), 'q' => $q, 'year' => $year, 'archive' => $this->archive]);
    }

    public function detailPage(int $id_event): View
    {
        $event = Event::where('status_event', 'Arsip')->find($id_event);
        abort_unless($event, Response::HTTP_NOT_FOUND);

        return view('public.archive-detail', ['detail' => $this->archive->detail($event)]);
    }
}
