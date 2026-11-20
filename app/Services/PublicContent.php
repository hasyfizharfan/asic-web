<?php

namespace App\Services;

use App\Models\CommunicationChannel;
use App\Models\ContactPerson;
use App\Models\Event;
use App\Models\EventContent;
use App\Models\EventPhoto;
use App\Models\EventSponsor;
use App\Models\Faq;
use App\Models\ImportantDate;
use App\Models\LegalDocument;
use App\Models\SponsorshipPackage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Sumber tunggal konten publik: hanya status Dipublikasikan, hanya event Aktif (landing/halaman)
 * atau Aktif/Selesai (API detail). Konten event Draft tidak pernah keluar.
 */
class PublicContent
{
    public const PUBLIC_EVENT_STATUSES = ['Aktif', 'Selesai'];

    public function activeEvent(): ?Event
    {
        if (! Schema::hasTable('event')) {
            return null;
        }

        return Event::query()->where('status_event', 'Aktif')->orderByDesc('tanggal_mulai')->first();
    }

    public function publicEvent(int $idEvent): ?Event
    {
        return Event::query()->whereKey($idEvent)->whereIn('status_event', self::PUBLIC_EVENT_STATUSES)->first();
    }

    /** @return Collection<int, EventContent> */
    public function blocks(?Event $event, ?array $sections = null): Collection
    {
        if (! $event) {
            return new Collection;
        }

        return EventContent::query()->where('id_event', $event->id_event)->where('status', 'Dipublikasikan')
            ->when($sections, fn ($q) => $q->whereIn('section', $sections))
            ->orderBy('urutan')->orderBy('id_content')->get();
    }

    /** @return Collection<int, ImportantDate> */
    public function importantDates(?Event $event): Collection
    {
        return $event ? ImportantDate::query()->where('id_event', $event->id_event)->where('status', 'Dipublikasikan')
            ->orderBy('tanggal_mulai')->orderBy('urutan')->get() : new Collection;
    }

    /** FAQ dan kontak: milik event atau umum (id_event null). */
    public function faqs(?Event $event): Collection
    {
        return Faq::query()->where('status', 'Dipublikasikan')
            ->where(fn ($q) => $q->whereNull('id_event')->when($event, fn ($w) => $w->orWhere('id_event', $event->id_event)))
            ->orderBy('urutan')->orderBy('id_faq')->get();
    }

    public function contacts(?Event $event): Collection
    {
        return ContactPerson::query()->where('status', 'Dipublikasikan')
            ->where(fn ($q) => $q->whereNull('id_event')->when($event, fn ($w) => $w->orWhere('id_event', $event->id_event)))
            ->orderBy('urutan')->orderBy('id_contact')->get();
    }

    /** KF-32: kanal komunikasi aktif (grup WhatsApp/Google Group) untuk pengunjung. */
    public function channels(?Event $event): Collection
    {
        return $event ? CommunicationChannel::where('id_event', $event->id_event)->where('status', 'Aktif')->orderBy('id_channel')->get() : new Collection;
    }

    public function photos(?Event $event): Collection
    {
        return $event ? EventPhoto::query()->where('id_event', $event->id_event)->where('status', 'Dipublikasikan')
            ->orderBy('urutan')->orderBy('id_photo')->get() : new Collection;
    }

    public function sponsors(?Event $event): Collection
    {
        return $event && Schema::hasTable('event_sponsor') ? EventSponsor::query()->where('id_event', $event->id_event)->where('status', 'Dipublikasikan')
            ->orderBy('urutan')->orderBy('id_sponsor')->get() : new Collection;
    }

    public function packages(?Event $event): Collection
    {
        return $event ? SponsorshipPackage::query()->where('id_event', $event->id_event)->where('status', 'Dipublikasikan')
            ->orderBy('urutan')->orderBy('id_package')->get() : new Collection;
    }

    /**
     * Halaman publik yang punya isi (untuk nav/footer: tautan hanya tampil bila ada isi).
     *
     * @return array<string, bool>
     */
    public function availablePages(?Event $event): array
    {
        if (! Schema::hasTable('event_content')) {
            return [];
        }
        $sections = $this->blocks($event)->pluck('section')->unique()->all();
        $published = LegalDocument::where('status', 'Dipublikasikan')->pluck('jenis')->all();
        $has = fn (array $s) => count(array_intersect($s, $sections)) > 0;

        return [
            'venue' => $has(['accommodation', 'pickup']) || (bool) $event?->lokasi,
            'guidelines' => $has(['presentation_oral', 'presentation_poster']),
            'faq' => $this->faqs($event)->isNotEmpty(),
            'contact' => $this->contacts($event)->isNotEmpty() || $this->channels($event)->isNotEmpty(),
            'sponsorship' => $this->packages($event)->isNotEmpty() || $this->sponsors($event)->isNotEmpty() || $has(['sponsorship_intro', 'exhibition_intro']),
            'travel' => $has(['visa', 'travel']),
            'gallery' => $this->photos($event)->isNotEmpty(),
        ] + collect(LegalDocument::TYPES)->mapWithKeys(fn ($jenis) => [$jenis => in_array($jenis, $published, true)])->all();
    }
}
