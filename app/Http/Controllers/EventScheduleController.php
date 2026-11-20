<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Models\Event;
use App\Models\EventSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pengelolaan program/jadwal acara (KF-05, KF-28). Terpisah dari sesi presensi (event_session). */
class EventScheduleController extends Controller
{
    use ApiResponses;

    private function rules(bool $partial): array
    {
        $p = $partial ? ['sometimes'] : [];

        return [
            'id_event' => [...$p, 'required', 'integer', 'exists:event,id_event'],
            'nama_kegiatan' => [...$p, 'required', 'string', 'max:150'],
            'tanggal' => [...$p, 'required', 'date_format:Y-m-d'],
            'waktu_mulai' => [...$p, 'required', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    private function timeOrder(array $data, ?EventSchedule $current = null): ?JsonResponse
    {
        $start = substr((string) ($data['waktu_mulai'] ?? $current?->waktu_mulai), 0, 5);
        $end = array_key_exists('waktu_selesai', $data) ? $data['waktu_selesai'] : $current?->waktu_selesai;
        if ($start && $end && substr((string) $end, 0, 5) <= $start) {
            return $this->fail(__('asic-api.messages.validasi_gagal'), Response::HTTP_UNPROCESSABLE_ENTITY, [
                'errors' => ['waktu_selesai' => ['Waktu selesai harus setelah waktu mulai.']],
            ]);
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $items = EventSchedule::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->orderBy('tanggal')->orderBy('waktu_mulai')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return $this->page('Daftar jadwal berhasil dimuat.', $items, $items->getCollection());
    }

    public function show(int $id_schedule): JsonResponse
    {
        $s = EventSchedule::find($id_schedule);

        return $s ? $this->ok(__('asic-api.messages.detail_jadwal_berhasil_dimuat'), $s) : $this->fail(__('asic-api.messages.jadwal_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        if ($err = $this->timeOrder($data)) {
            return $err;
        }

        return $this->ok(__('asic-api.messages.jadwal_berhasil_ditambahkan'), EventSchedule::create($data)->fresh(), Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id_schedule): JsonResponse
    {
        $s = EventSchedule::find($id_schedule);
        if (! $s) {
            return $this->fail(__('asic-api.messages.jadwal_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $data = $request->validate($this->rules(true));
        if ($err = $this->timeOrder($data, $s)) {
            return $err;
        }
        $s->update($data);

        return $this->ok(__('asic-api.messages.jadwal_berhasil_diperbarui'), $s->fresh());
    }

    public function destroy(int $id_schedule): JsonResponse
    {
        $s = EventSchedule::find($id_schedule);
        if (! $s) {
            return $this->fail(__('asic-api.messages.jadwal_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $s->delete();

        return $this->ok(__('asic-api.messages.jadwal_berhasil_dihapus'));
    }

    public function publicIndex(int $id_event): JsonResponse
    {
        if (! Event::whereIn('status_event', ['Aktif', 'Selesai'])->whereKey($id_event)->exists()) {
            return $this->fail(__('asic-api.messages.event_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }

        return $this->ok(__('asic-api.messages.jadwal_berhasil_dimuat'), EventSchedule::where('id_event', $id_event)->orderBy('tanggal')->orderBy('waktu_mulai')->get());
    }
}
