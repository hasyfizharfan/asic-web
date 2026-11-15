<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Models\CommunicationChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/** Tautan kanal komunikasi WhatsApp/Google Group (KF-32). */
class CommunicationChannelController extends Controller
{
    use ApiResponses;

    private function rules(bool $partial): array
    {
        $p = $partial ? ['sometimes'] : [];

        return [
            'id_event' => [...$p, 'required', 'integer', 'exists:event,id_event'],
            'nama_kanal' => [...$p, 'required', 'string', 'max:100'],
            'jenis_kanal' => [...$p, 'required', Rule::in(CommunicationChannel::TYPES)],
            // Hanya http/https agar tidak ada skema berbahaya (javascript:, data:) yang ikut tampil di frontend.
            'url' => [...$p, 'required', 'url:http,https', 'max:500'],
            'status' => [...$p, 'required', Rule::in(['Aktif', 'Nonaktif'])],
        ];
    }

    public function manageIndex(Request $request): JsonResponse
    {
        $items = CommunicationChannel::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderBy('id_channel')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return $this->page('Daftar kanal komunikasi berhasil dimuat.', $items, $items->getCollection());
    }

    public function show(int $id_channel): JsonResponse
    {
        $c = CommunicationChannel::find($id_channel);

        return $c ? $this->ok('Detail kanal berhasil dimuat.', $c) : $this->fail('Kanal tidak ditemukan.', Response::HTTP_NOT_FOUND);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->ok('Kanal komunikasi berhasil ditambahkan.', CommunicationChannel::create($request->validate($this->rules(false)))->fresh(), Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id_channel): JsonResponse
    {
        $c = CommunicationChannel::find($id_channel);
        if (! $c) {
            return $this->fail('Kanal tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }
        $c->update($request->validate($this->rules(true)));

        return $this->ok('Kanal komunikasi berhasil diperbarui.', $c->fresh());
    }

    public function destroy(int $id_channel): JsonResponse
    {
        $c = CommunicationChannel::find($id_channel);
        if (! $c) {
            return $this->fail('Kanal tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }
        $c->delete();

        return $this->ok('Kanal komunikasi berhasil dihapus.');
    }

    /** Untuk peserta/panitia yang login: hanya kanal Aktif. */
    public function visibleIndex(Request $request): JsonResponse
    {
        return $this->ok('Daftar kanal komunikasi berhasil dimuat.', CommunicationChannel::where('status', 'Aktif')
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->orderBy('id_channel')->get(['id_channel', 'id_event', 'nama_kanal', 'jenis_kanal', 'url']));
    }
}
