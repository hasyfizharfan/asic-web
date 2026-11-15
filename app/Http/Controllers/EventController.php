<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/** Pengelolaan informasi event (KF-01, KF-28) + endpoint publik read-only. */
class EventController extends Controller
{
    use ApiResponses;

    private const MODES = ['Hybrid', 'Daring', 'Luring'];
    private const STATUSES = ['Draft', 'Aktif', 'Selesai', 'Arsip'];

    private function rules(bool $partial): array
    {
        $p = $partial ? ['sometimes'] : [];

        return [
            'nama_event' => [...$p, 'required', 'string', 'max:150'],
            'tema' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_mulai' => [...$p, 'required', 'date_format:Y-m-d'],
            'tanggal_selesai' => [...$p, 'required', 'date_format:Y-m-d'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'mode_kegiatan' => [...$p, 'required', Rule::in(self::MODES)],
            'status_event' => [...$p, 'required', Rule::in(self::STATUSES)],
        ];
    }

    private function datesOrder(array $data, ?Event $event = null): ?JsonResponse
    {
        $start = $data['tanggal_mulai'] ?? $event?->tanggal_mulai;
        $end = $data['tanggal_selesai'] ?? $event?->tanggal_selesai;
        if ($start && $end && (string) $end < (string) $start) {
            return $this->fail('Validasi gagal.', Response::HTTP_UNPROCESSABLE_ENTITY, [
                'errors' => ['tanggal_selesai' => ['Tanggal selesai tidak boleh sebelum tanggal mulai.']],
            ]);
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $events = Event::query()
            ->when($request->filled('status_event'), fn ($q) => $q->where('status_event', $request->string('status_event')->toString()))
            ->orderByDesc('tanggal_mulai')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return $this->page('Daftar event berhasil dimuat.', $events, $events->getCollection());
    }

    public function show(int $id_event): JsonResponse
    {
        $event = Event::find($id_event);

        return $event ? $this->ok('Detail event berhasil dimuat.', $event) : $this->fail('Event tidak ditemukan.', Response::HTTP_NOT_FOUND);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        if ($err = $this->datesOrder($data)) {
            return $err;
        }

        return $this->ok('Event berhasil ditambahkan.', Event::create($data)->fresh(), Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id_event): JsonResponse
    {
        $event = Event::find($id_event);
        if (! $event) {
            return $this->fail('Event tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }
        $data = $request->validate($this->rules(true));
        if ($err = $this->datesOrder($data, $event)) {
            return $err;
        }
        $event->update($data);

        return $this->ok('Event berhasil diperbarui.', $event->fresh());
    }

    public function destroy(int $id_event): JsonResponse
    {
        $event = Event::find($id_event);
        if (! $event) {
            return $this->fail('Event tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }
        try {
            $event->delete();
        } catch (QueryException) {
            return $this->fail('Event sudah memiliki data terkait (periode, tarif, registrasi, dsb.) sehingga tidak dapat dihapus. Ubah status menjadi Arsip.', Response::HTTP_CONFLICT);
        }

        return $this->ok('Event berhasil dihapus.');
    }

    // ---------- Publik (tanpa autentikasi) ----------

    public function publicIndex(): JsonResponse
    {
        return $this->ok('Daftar event berhasil dimuat.', Event::where('status_event', 'Aktif')->orderBy('tanggal_mulai')->get());
    }

    public function publicShow(int $id_event): JsonResponse
    {
        $event = Event::whereIn('status_event', ['Aktif', 'Selesai'])->find($id_event);

        return $event ? $this->ok('Detail event berhasil dimuat.', $event) : $this->fail('Event tidak ditemukan.', Response::HTTP_NOT_FOUND);
    }
}
