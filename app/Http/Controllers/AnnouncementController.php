<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Models\Announcement;
use App\Models\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/** Pengumuman (KF-30). Kelola: manage_website_content. Lihat: semua pengguna login, hanya yang terpublikasi. */
class AnnouncementController extends Controller
{
    use ApiResponses;

    private function rules(bool $partial): array
    {
        $p = $partial ? ['sometimes'] : [];

        return [
            'id_event' => [...$p, 'required', 'integer', 'exists:event,id_event'],
            'judul' => [...$p, 'required', 'string', 'max:150'],
            'isi_pengumuman' => [...$p, 'required', 'string'],
            'status' => [...$p, 'required', Rule::in(['Draft', 'Dipublikasikan'])],
            'tanggal_publikasi' => ['nullable', 'date'],
        ];
    }

    /** Dipublikasikan tanpa tanggal => terbit sekarang. Tanggal masa depan => terjadwal. */
    private function normalize(array $data, ?Announcement $current = null): array
    {
        $status = $data['status'] ?? $current?->status;
        $date = array_key_exists('tanggal_publikasi', $data) ? $data['tanggal_publikasi'] : $current?->tanggal_publikasi;
        if ($status === 'Dipublikasikan' && ! $date) {
            $data['tanggal_publikasi'] = now();
        }

        return $data;
    }

    public function manageIndex(Request $request): JsonResponse
    {
        $items = Announcement::with('author:id_user,email')
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderByDesc('id_announcement')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return $this->page('Daftar pengumuman berhasil dimuat.', $items, $items->getCollection());
    }

    public function show(int $id_announcement): JsonResponse
    {
        $a = Announcement::with('author:id_user,email')->find($id_announcement);

        return $a ? $this->ok(__('asic-api.messages.detail_pengumuman_berhasil_dimuat'), $a) : $this->fail(__('asic-api.messages.pengumuman_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var UserAccount $user */
        $user = Auth::user();
        $data = $this->normalize($request->validate($this->rules(false)));
        $data['id_author'] = $user->id_user;

        return $this->ok(__('asic-api.messages.pengumuman_berhasil_dibuat'), Announcement::create($data)->fresh(), Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id_announcement): JsonResponse
    {
        $a = Announcement::find($id_announcement);
        if (! $a) {
            return $this->fail(__('asic-api.messages.pengumuman_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $a->update($this->normalize($request->validate($this->rules(true)), $a));

        return $this->ok(__('asic-api.messages.pengumuman_berhasil_diperbarui'), $a->fresh());
    }

    public function destroy(int $id_announcement): JsonResponse
    {
        $a = Announcement::find($id_announcement);
        if (! $a) {
            return $this->fail(__('asic-api.messages.pengumuman_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }
        $a->delete();

        return $this->ok(__('asic-api.messages.pengumuman_berhasil_dihapus'));
    }

    /** Untuk peserta/pengguna login: hanya yang terpublikasi dan sudah tiba waktunya. */
    public function visibleIndex(Request $request): JsonResponse
    {
        $items = Announcement::visibleToParticipants()
            ->select(['id_announcement', 'id_event', 'judul', 'isi_pengumuman', 'tanggal_publikasi'])
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->orderByDesc('tanggal_publikasi')->orderByDesc('id_announcement')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return $this->page('Daftar pengumuman berhasil dimuat.', $items, $items->getCollection());
    }
}
