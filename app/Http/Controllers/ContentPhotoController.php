<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventPhotoRequest;
use App\Http\Requests\UpdateEventPhotoRequest;
use App\Http\Resources\ContentItemResource;
use App\Models\EventPhoto;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/** Galeri foto event (KF-01). Disk public, encode ulang GD agar metadata EXIF terbuang. */
class ContentPhotoController extends Controller
{
    private const MAX_SIDE = 1920;

    #[OA\Get(path: '/api/content/photos', summary: 'Daftar foto galeri', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Daftar foto')])]
    public function index(Request $request): JsonResponse
    {
        $page = EventPhoto::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderBy('urutan')->orderBy('id_photo')
            ->paginate(min(max($request->integer('per_page', 50), 1), 200));

        return response()->json(['success' => true, 'data' => ContentItemResource::collection($page->getCollection())->resolve(),
            'pagination' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]]);
    }

    #[OA\Post(path: '/api/content/photos', summary: 'Unggah foto galeri', description: 'Multipart: file (jpeg/png/webp, maks 5 MB), id_event, caption_id/caption_en (minimal satu, teks alternatif), status, urutan.', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Tersimpan'), new OA\Response(response: 422, description: 'Berkas/teks tidak valid')])]
    public function store(StoreEventPhotoRequest $request): JsonResponse
    {
        $data = $request->safe()->except('file');
        $data['file_path'] = $this->storeImage($request->file('file'), (int) $data['id_event']);
        $photo = EventPhoto::create($data);
        Audit::log('content.photo_uploaded', 'event_photo', $photo->id_photo, null, ['id_event' => $photo->id_event]);

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => (new ContentItemResource($photo->fresh()))->resolve()], Response::HTTP_CREATED);
    }

    #[OA\Patch(path: '/api/content/photos/{id_photo}', summary: 'Ubah keterangan/status/urutan foto', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_photo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Diperbarui')])]
    public function update(UpdateEventPhotoRequest $request, int $id_photo): JsonResponse
    {
        $photo = EventPhoto::findOrFail($id_photo);
        $photo->update($request->validated());

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => (new ContentItemResource($photo->fresh()))->resolve()]);
    }

    #[OA\Delete(path: '/api/content/photos/{id_photo}', summary: 'Hapus foto beserta berkasnya', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_photo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Dihapus')])]
    public function destroy(int $id_photo): JsonResponse
    {
        $photo = EventPhoto::findOrFail($id_photo);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();
        Audit::log('content.photo_deleted', 'event_photo', $id_photo);

        return response()->json(['success' => true, 'message' => __('asic-content.api.deleted')]);
    }

    private function storeImage(UploadedFile $file, int $idEvent): string
    {
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $path = sprintf('gallery/%d/%s.%s', $idEvent, Str::uuid(), $ext);
        $encoded = $this->reencode($file->getRealPath(), $ext);
        if ($encoded === null) {
            Log::warning('gallery.reencode_skipped', ['reason' => 'GD tidak tersedia atau gambar tidak dapat dibaca']);
            Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));
        } else {
            Storage::disk('public')->put($path, $encoded);
        }

        return $path;
    }

    /** Encode ulang lewat GD (membuang EXIF, maks sisi 1920 px). Null bila GD tidak ada. */
    private function reencode(string $source, string $ext): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }
        $image = @imagecreatefromstring((string) file_get_contents($source));
        if ($image === false) {
            return null;
        }
        $w = imagesx($image);
        $h = imagesy($image);
        $scale = min(1, self::MAX_SIDE / max($w, $h));
        if ($scale < 1) {
            $resized = imagescale($image, (int) round($w * $scale), (int) round($h * $scale));
            imagedestroy($image);
            $image = $resized;
        }
        ob_start();
        match ($ext) {
            'png' => imagepng($image, null, 6),
            'webp' => function_exists('imagewebp') ? imagewebp($image, null, 85) : imagepng($image),
            default => imagejpeg($image, null, 85),
        };
        imagedestroy($image);

        return (string) ob_get_clean();
    }
}
