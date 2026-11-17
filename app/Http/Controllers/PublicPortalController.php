<?php

namespace App\Http\Controllers;

use App\Models\EventSchedule;
use App\Models\RegistrationFee;
use App\Models\Speaker;
use App\Services\PublicContent;
use App\Support\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/** Landing publik: data event, pembicara, program, tarif, dan konten terpublikasi (tanpa teks pengganti seperti "TBA"). */
class PublicPortalController extends Controller
{
    public function index(PublicContent $content): View
    {
        $event = $content->activeEvent();
        $fees = $event && Schema::hasTable('registration_fee')
            ? RegistrationFee::query()->where('id_event', $event->id_event)->with('registrationPeriod')->get()
            : collect();
        $activeFees = $fees->filter(fn (RegistrationFee $fee) => $fee->registrationPeriod?->status_periode === 'Aktif');

        // Ringkasan: tarif terendah per kategori & mata uang pada periode aktif (IDR = domestik, USD = internasional).
        $feeRows = [];
        foreach (['Student' => 'student', 'Regular' => 'regular'] as $type => $key) {
            foreach ([Currency::IDR => 'domestic', Currency::USD => 'intl'] as $currency => $scope) {
                $matching = $activeFees->filter(fn (RegistrationFee $fee) => $fee->registration_type === $type && ($fee->mata_uang ?? Currency::IDR) === $currency);
                if ($matching->isNotEmpty()) {
                    $feeRows[] = ['label' => __("asic-landing.fee_{$key}_{$scope}"), 'amount' => Currency::format($matching->min(fn ($fee) => (float) $fee->finalAmount()), $currency)];
                }
            }
        }

        $hasContent = Schema::hasTable('event_content');
        $blocks = $hasContent ? $content->blocks($event) : collect();

        return view('public.landing', [
            'event' => $event,
            'feeRows' => $feeRows,
            'feeTable' => $this->feeTable($fees),
            'feeCards' => $this->feeCards($fees),
            'speakers' => $event ? Speaker::query()->where('id_event', $event->id_event)->orderBy('id_speaker')->get() : collect(),
            'programDays' => $event ? $this->programDays(EventSchedule::query()->where('id_event', $event->id_event)->orderBy('tanggal')->orderBy('waktu_mulai')->get()) : collect(),
            'scopeBlocks' => $blocks->where('section', 'scope')->values(),
            'aboutBlocks' => $blocks->where('section', 'about')->values(),
            'publicationBlocks' => $blocks->where('section', 'publication')->values(),
            'facilityBlocks' => $blocks->where('section', 'facilities')->values(),
            'committeeBlocks' => $blocks->where('section', 'committee')->values(),
            'venueBlocks' => $blocks->where('section', 'venue_info')->values(),
            'guideBlocks' => $blocks->whereIn('section', ['presentation_oral', 'presentation_poster'])->values(),
            'importantDates' => $hasContent ? $content->importantDates($event) : collect(),
            'faqs' => $hasContent ? $content->faqs($event)->take(4) : collect(),
            'contacts' => $hasContent ? $content->contacts($event) : collect(),
            'packages' => $hasContent ? $content->packages($event)->take(3) : collect(),
            'photos' => $hasContent ? $content->photos($event)->take(6) : collect(),
            'channels' => $hasContent ? $content->channels($event) : collect(),
            'pages' => $hasContent ? $content->availablePages($event) : [],
            'isDemo' => Schema::hasTable('app_setting') && DB::table('app_setting')->where('key', 'demo_landing')->exists(),
            'asic' => [
                'countdown_status' => $event?->nama_event,
                'countdown_target' => $event?->tanggal_mulai ? Carbon::parse($event->tanggal_mulai)->setTime(8, 0)->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * Tabel tarif lengkap: baris = kategori × mode, kolom = periode; sel berisi tarif terendah per mata uang.
     *
     * @return array{periods: list<array{name: string, active: bool}>, rows: list<array{type: string, mode: string, cells: list<array<string, string>>}>}
     */
    private function feeTable(Collection $fees): array
    {
        $periods = $fees->map(fn (RegistrationFee $f) => $f->registrationPeriod)->filter()->unique('id_period')->sortBy('tanggal_mulai')->values();
        $rows = [];
        foreach ($fees->groupBy(fn (RegistrationFee $f) => $f->registration_type.'|'.$f->attendance_mode)->sortKeys() as $key => $group) {
            [$type, $mode] = explode('|', $key);
            $cells = [];
            foreach ($periods as $period) {
                $cell = [];
                foreach (Currency::ALL as $currency) {
                    $match = $group->filter(fn (RegistrationFee $f) => $f->id_period === $period->id_period && ($f->mata_uang ?? Currency::IDR) === $currency);
                    if ($match->isNotEmpty()) {
                        $cell[$currency] = Currency::format($match->min(fn ($f) => (float) $f->finalAmount()), $currency);
                    }
                }
                $cells[] = $cell;
            }
            $rows[] = ['type' => $type, 'mode' => $mode, 'cells' => $cells];
        }

        return [
            'periods' => $periods->map(fn ($p) => ['name' => $p->nama_periode, 'active' => $p->status_periode === 'Aktif', 'until' => $p->tanggal_selesai])->all(),
            'rows' => $rows,
        ];
    }

    /**
     * Kartu harga: [kategori][mode][mata uang] => periode berlaku (aktif, atau terdekat), harga dasar tanpa publikasi,
     * total dengan prosiding/jurnal, dan harga dasar di periode lain (mis. Regular).
     *
     * @return array<string, array<string, array<string, array<string, mixed>>>>
     */
    private function feeCards(Collection $fees): array
    {
        $cards = [];
        $periods = $fees->map(fn (RegistrationFee $f) => $f->registrationPeriod)->filter()->unique('id_period')->sortBy('tanggal_mulai')->values();
        foreach (['Student', 'Regular'] as $type) {
            foreach (['Luring', 'Daring'] as $mode) {
                foreach (Currency::ALL as $currency) {
                    $group = $fees->filter(fn (RegistrationFee $f) => $f->registration_type === $type && $f->attendance_mode === $mode && ($f->mata_uang ?? Currency::IDR) === $currency);
                    if ($group->isEmpty()) {
                        continue;
                    }
                    $byPeriod = [];
                    foreach ($periods as $period) {
                        $inPeriod = $group->where('id_period', $period->id_period);
                        if ($inPeriod->isEmpty()) {
                            continue;
                        }
                        $price = fn (string $pub) => ($fee = $inPeriod->firstWhere('tujuan_publikasi', $pub)) ? (float) $fee->finalAmount() : null;
                        $base = $price('Tidak') ?? (float) $inPeriod->min(fn ($f) => (float) $f->finalAmount());
                        $byPeriod[] = [
                            'name' => $period->nama_periode, 'active' => $period->status_periode === 'Aktif', 'until' => $period->tanggal_selesai,
                            'base' => Currency::format($base, $currency), 'base_raw' => $base,
                            'proceedings' => ($v = $price('Prosiding')) !== null ? Currency::format($v, $currency) : null,
                            'journal' => ($v = $price('Jurnal')) !== null ? Currency::format($v, $currency) : null,
                        ];
                    }
                    if (! $byPeriod) {
                        continue;
                    }
                    $currentIndex = collect($byPeriod)->search(fn ($p) => $p['active']);
                    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
                    $cards[$type][$mode][$currency] = [
                        'current' => $byPeriod[$currentIndex],
                        'others' => array_values(array_filter($byPeriod, fn ($p, $i) => $i !== $currentIndex, ARRAY_FILTER_USE_BOTH)),
                    ];
                }
            }
        }

        return $cards;
    }

    /** @return Collection<string, Collection<int, EventSchedule>> */
    private function programDays(Collection $items): Collection
    {
        return $items->groupBy(fn (EventSchedule $s) => Carbon::parse($s->tanggal)->toDateString());
    }
}
