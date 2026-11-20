<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicContentResource;
use App\Models\EventContent;
use App\Services\PublicContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/** API publik konten (tanpa login): hanya Dipublikasikan, event Aktif atau Selesai, bahasa aktif. */
class PublicContentController extends Controller
{
    private const TYPES = ['content', 'important-dates', 'faqs', 'contacts', 'photos', 'sponsorship-packages'];

    public function __construct(private readonly PublicContent $content) {}

    #[OA\Get(path: '/api/public/events/{id_event}/{content_type}', summary: 'Konten publik terpublikasi per event', description: 'content_type: content (filter section), important-dates, faqs, contacts, photos, sponsorship-packages. Teks mengikuti bahasa aktif; bila kosong memakai bahasa lain.', tags: ['Publik'],
        parameters: [
            new OA\Parameter(name: 'id_event', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'content_type', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: self::TYPES)),
            new OA\Parameter(name: 'section', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Daftar konten'), new OA\Response(response: 404, description: 'Event tidak publik')])]
    public function index(Request $request, int $id_event): JsonResponse
    {
        $type = (string) $request->route()->defaults['content_type'];
        $event = $this->content->publicEvent($id_event);
        abort_unless($event, Response::HTTP_NOT_FOUND);
        $request->validate(['section' => ['sometimes', Rule::in(EventContent::SECTIONS)]]);

        $items = match ($type) {
            'content' => $this->content->blocks($event, $request->filled('section') ? [$request->string('section')->toString()] : null),
            'important-dates' => $this->content->importantDates($event),
            'faqs' => $this->content->faqs($event),
            'contacts' => $this->content->contacts($event),
            'photos' => $this->content->photos($event),
            'sponsorship-packages' => $this->content->packages($event),
        };

        return response()->json(['success' => true, 'data' => PublicContentResource::collection($items)->resolve()]);
    }
}
