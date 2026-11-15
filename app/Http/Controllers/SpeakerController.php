<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** Pengelolaan narasumber (KF-02, KF-28). Foto disimpan di disk publik karena tampil di website. */
class SpeakerController extends Controller
{
    use ApiResponses;

    private function rules(bool $partial): array
    {
        $p = $partial ? ['sometimes'] : [];

        return [
            'id_event' => [...$p, 'required', 'integer', 'exists:event,id_event'],
            'nama_speaker' => [...$p, 'required', 'string', 'max:100'],
            'institusi' => ['nullable', 'string', 'max:150'],
            'negara' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function present(Speaker $s): array
    {
        return $s->toArray() + ['foto_url' => $s->foto ? Storage::disk('public')->url($s->foto) : null];
    }

    public function index(Request $request): JsonResponse
    {
        $items = Speaker::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->orderBy('nama_speaker')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return $this->page('Daftar narasumber berhasil dimuat.', $items, $items->getCollection()->map(fn ($s) => $this->present($s)));
    }

    public function show(int $id_speaker): JsonResponse
    {
        $s = Speaker::find($id_speaker);

        return $s ? $this->ok('Detail narasumber berhasil dimuat.', $this->present($s)) : $this->fail('Narasumber tidak ditemukan.', Response::HTTP_NOT_FOUND);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('speakers', 'public');
        }

        return $this->ok('Narasumber berhasil ditambahkan.', $this->present(Speaker::create($data)->fresh()), Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id_speaker): JsonResponse
    {
        $s = Speaker::find($id_speaker);
        if (! $s) {
            return $this->fail('Narasumber tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }
        $data = $request->validate($this->rules(true));
        if ($request->hasFile('foto')) {
            $old = $s->foto;
            $data['foto'] = $request->file('foto')->store('speakers', 'public');
            $old && Storage::disk('public')->delete($old);
        }
        $s->update($data);

        return $this->ok('Narasumber berhasil diperbarui.', $this->present($s->fresh()));
    }

    public function destroy(int $id_speaker): JsonResponse
    {
        $s = Speaker::find($id_speaker);
        if (! $s) {
            return $this->fail('Narasumber tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }
        $foto = $s->foto;
        $s->delete();
        $foto && Storage::disk('public')->delete($foto);

        return $this->ok('Narasumber berhasil dihapus.');
    }

    public function publicIndex(int $id_event): JsonResponse
    {
        if (! Event::whereIn('status_event', ['Aktif', 'Selesai'])->whereKey($id_event)->exists()) {
            return $this->fail('Event tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }

        return $this->ok('Daftar narasumber berhasil dimuat.', Speaker::where('id_event', $id_event)->orderBy('nama_speaker')->get()->map(fn ($s) => $this->present($s)));
    }
}
