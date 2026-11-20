<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use App\Services\PublicContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** KF-06: unduhan publik template abstrak, artikel, presentasi, dan panduan (hanya Aktif, event Aktif/Selesai). */
class PublicDownloadController extends Controller
{
    public function __construct(private readonly PublicContent $content, private readonly DocumentService $documents) {}

    private function query(?int $idEvent)
    {
        return DocumentTemplate::query()->whereIn('jenis_dokumen', DocumentTemplate::DOWNLOADS)->where('status_template', 'Aktif')
            ->whereHas('event', fn ($q) => $q->whereIn('status_event', PublicContent::PUBLIC_EVENT_STATUSES))
            ->when($idEvent, fn ($q) => $q->where('id_event', $idEvent))
            ->orderBy('jenis_dokumen')->orderBy('subjenis')->orderBy('bahasa');
    }

    #[OA\Get(path: '/api/public/downloads', summary: 'Daftar berkas template publik', tags: ['Publik'],
        parameters: [new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Daftar berkas Aktif')])]
    public function index(Request $request): JsonResponse
    {
        $request->validate(['id_event' => ['sometimes', 'integer']]);
        $rows = $this->query($request->filled('id_event') ? $request->integer('id_event') : null)->get();

        return response()->json(['success' => true, 'data' => collect(DocumentTemplateResource::collection($rows)->resolve())
            ->map(fn ($row) => array_intersect_key($row, array_flip(['id_template', 'id_event', 'jenis_dokumen', 'nama_template', 'subjenis', 'bahasa', 'versi', 'ukuran_file'])) + ['url' => route('public.downloads.file', $row['id_template'])])->all()]);
    }

    #[OA\Get(path: '/downloads/{id_template}', summary: 'Unduh berkas template publik', tags: ['Publik'],
        parameters: [new OA\Parameter(name: 'id_template', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Berkas'), new OA\Response(response: 404, description: 'Tidak tersedia')])]
    public function file(int $id_template): StreamedResponse
    {
        $template = $this->query(null)->whereKey($id_template)->first();
        abort_unless($template, Response::HTTP_NOT_FOUND);

        return DocumentTemplateController::stream($template, $this->documents->disk());
    }

    public function page(): View
    {
        $event = $this->content->activeEvent();
        $items = $event ? $this->query($event->id_event)->get() : collect();

        return view('public.downloads', ['event' => $event, 'groups' => $items->groupBy('jenis_dokumen')]);
    }
}
