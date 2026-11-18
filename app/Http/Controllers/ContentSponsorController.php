<?php

namespace App\Http\Controllers;

use App\Models\EventSponsor;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logo penyelenggara/pendukung/sponsor/mitra (Konten > Sponsor & mitra). Logo PNG/JPG/WebP saja
 * (SVG ditolak karena bisa memuat skrip), di-encode ulang GD dengan transparansi tetap, maks. 800 px.
 */
class ContentSponsorController extends Controller
{
    private const MAX_SIDE = 800;

    public function index(Request $request): JsonResponse
    {
        $rows = EventSponsor::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->orderByRaw($this->categoryOrder())->orderBy('urutan')->orderBy('id_sponsor')->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, true);
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $this->storeLogo($request->file('logo'), (int) $data['id_event']);
        }
        $data['urutan'] ??= (int) EventSponsor::where('id_event', $data['id_event'])->max('urutan') + 1;
        $sponsor = EventSponsor::create($data);
        Audit::log('content.sponsor_created', 'event_sponsor', $sponsor->id_sponsor);

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => $sponsor->fresh()], Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id_sponsor): JsonResponse
    {
        $sponsor = EventSponsor::findOrFail($id_sponsor);
        $data = $this->validated($request, false);
        if ($request->hasFile('logo')) {
            $old = $sponsor->logo_path;
            $data['logo_path'] = $this->storeLogo($request->file('logo'), $sponsor->id_event);
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $sponsor->update($data);

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => $sponsor->fresh()]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $items = $request->validate(['items' => ['required', 'array', 'max:200'], 'items.*.id' => ['required', 'integer'], 'items.*.urutan' => ['required', 'integer', 'min:0']])['items'];
        foreach ($items as $item) {
            EventSponsor::whereKey($item['id'])->update(['urutan' => $item['urutan']]);
        }

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved')]);
    }

    public function destroy(int $id_sponsor): JsonResponse
    {
        $sponsor = EventSponsor::findOrFail($id_sponsor);
        if ($sponsor->logo_path) {
            Storage::disk('public')->delete($sponsor->logo_path);
        }
        $sponsor->delete();
        Audit::log('content.sponsor_deleted', 'event_sponsor', $id_sponsor);

        return response()->json(['success' => true, 'message' => __('asic-content.api.deleted')]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return $request->validate([
            'id_event' => [$creating ? 'required' : 'prohibited', 'integer', 'exists:event,id_event'],
            'nama' => [$req, 'string', 'max:150'],
            'kategori' => [$req, Rule::in(EventSponsor::CATEGORIES)],
            'tingkat' => ['sometimes', 'nullable', 'string', 'max:50'],
            'url' => ['sometimes', 'nullable', 'url:http,https', 'max:255'],
            'status' => ['sometimes', Rule::in(['Draft', 'Dipublikasikan'])],
            'urutan' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'logo' => ['sometimes', 'nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'logo.mimes' => __('asic-content.validation.logo_type'),
            'logo.max' => __('asic-content.validation.logo_size'),
        ]);
    }

    private function categoryOrder(): string
    {
        $cases = collect(EventSponsor::CATEGORIES)->map(fn ($c, $i) => "WHEN '".str_replace("'", "''", $c)."' THEN ".$i)->implode(' ');

        return "CASE kategori {$cases} ELSE 99 END";
    }

    private function storeLogo(UploadedFile $file, int $idEvent): string
    {
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $path = sprintf('sponsors/%d/%s.%s', $idEvent, Str::uuid(), $ext === 'jpg' ? 'jpg' : 'png');
        $encoded = $this->reencode($file->getRealPath(), $ext);
        if ($encoded === null) {
            abort(422, __('asic-content.validation.logo_unreadable'));
        }
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

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
            $nw = (int) round($w * $scale);
            $nh = (int) round($h * $scale);
            $resized = imagecreatetruecolor($nw, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($image);
            $image = $resized;
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);
        ob_start();
        $ext === 'jpg' ? imagejpeg($image, null, 90) : imagepng($image, null, 6);
        imagedestroy($image);

        return (string) ob_get_clean();
    }
}
