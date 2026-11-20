<?php

namespace App\Services;

use App\Models\AbstractSubmission;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventSchedule;
use App\Models\Registration;
use App\Models\Speaker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/** KF-29: arsip seminar. Hanya event Selesai/Arsip, hanya angka agregat (tanpa data pribadi). */
class ArchiveService
{
    public const STATUSES = ['Selesai', 'Arsip'];

    public function query(?string $search = null, ?int $year = null, array $statuses = self::STATUSES): Builder
    {
        return Event::query()->whereIn('status_event', $statuses)
            ->when($search, fn ($q) => $q->where(fn ($w) => $w->where('nama_event', 'like', '%'.$search.'%')->orWhere('tema', 'like', '%'.$search.'%')))
            ->when($year, fn ($q) => $q->whereYear('tanggal_mulai', $year))
            ->orderByDesc('tanggal_mulai');
    }

    public function paginate(?string $search, ?int $year, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query($search, $year, ['Arsip'])->paginate($perPage)->withQueryString();
    }

    /** @return list<int> */
    public function years(): array
    {
        return Event::query()->where('status_event', 'Arsip')->pluck('tanggal_mulai')->filter()
            ->map(fn ($d) => (int) substr((string) $d, 0, 4))->unique()->sortDesc()->values()->all();
    }

    /** @return array{registrations: int, presenters: int, accepted_abstracts: int, attendees: int} */
    public function stats(Event $event): array
    {
        $registrations = Registration::where('id_event', $event->id_event)->where('registration_status', 'Terdaftar');

        return [
            'registrations' => (clone $registrations)->count(),
            'presenters' => (clone $registrations)->whereIn('participation_type', ['Oral Presenter', 'Poster Presenter'])->count(),
            'accepted_abstracts' => AbstractSubmission::where('status_abstrak', 'Diterima')->whereIn('id_registration', (clone $registrations)->select('id_registration'))->count(),
            'attendees' => Attendance::where('status_kehadiran', 'Hadir')->whereIn('id_registration', (clone $registrations)->select('id_registration'))->distinct('id_registration')->count('id_registration'),
        ];
    }

    /** Ringkasan publik satu edisi: tanpa data pribadi peserta. */
    public function detail(Event $event): array
    {
        return [
            'id_event' => $event->id_event,
            'nama_event' => $event->nama_event,
            'tema' => $event->tema,
            'deskripsi' => $event->deskripsi,
            'tanggal_mulai' => $event->tanggal_mulai,
            'tanggal_selesai' => $event->tanggal_selesai,
            'lokasi' => $event->lokasi,
            'jumlah_peserta' => $this->stats($event)['registrations'],
            'speakers' => Speaker::where('id_event', $event->id_event)->orderBy('nama_speaker')->get(['nama_speaker', 'institusi', 'negara'])->toArray(),
            'schedules' => EventSchedule::where('id_event', $event->id_event)->orderBy('tanggal')->orderBy('waktu_mulai')->get(['nama_kegiatan', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'lokasi'])->toArray(),
            'photos' => EventPhoto::where('id_event', $event->id_event)->where('status', 'Dipublikasikan')->orderBy('urutan')->get()
                ->map(fn (EventPhoto $p) => ['url' => Storage::disk('public')->url($p->file_path), 'caption' => $p->localized('caption')])->all(),
        ];
    }
}
