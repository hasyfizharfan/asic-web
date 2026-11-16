<?php

namespace App\Http\Controllers;

use App\Models\RegistrationFee;
use App\Services\PublicContent;
use App\Support\Currency;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/** Landing publik: data event, tarif, dan konten terpublikasi (tanpa teks pengganti seperti "TBA"). */
class PublicPortalController extends Controller
{
    public function index(PublicContent $content): View
    {
        $event = $content->activeEvent();
        $fees = $event && Schema::hasTable('registration_fee')
            ? RegistrationFee::query()->where('id_event', $event->id_event)
                ->whereHas('registrationPeriod', fn ($q) => $q->where('status_periode', 'Aktif'))->get()
            : collect();

        // Tarif terendah per kategori & mata uang (IDR = domestik, USD = internasional). Baris tanpa tarif disembunyikan.
        $feeRows = [];
        foreach (['Student' => 'student', 'Regular' => 'regular'] as $type => $key) {
            foreach ([Currency::IDR => 'domestic', Currency::USD => 'intl'] as $currency => $scope) {
                $matching = $fees->filter(fn (RegistrationFee $fee) => $fee->registration_type === $type && ($fee->mata_uang ?? Currency::IDR) === $currency);
                if ($matching->isNotEmpty()) {
                    $feeRows[] = ['label' => __("asic-landing.fee_{$key}_{$scope}"), 'amount' => Currency::format($matching->min(fn ($fee) => (float) $fee->finalAmount()), $currency)];
                }
            }
        }

        $hasContent = Schema::hasTable('event_content');

        return view('public.landing', [
            'event' => $event,
            'feeRows' => $feeRows,
            'scopeBlocks' => $hasContent ? $content->blocks($event, ['scope']) : collect(),
            'importantDates' => $hasContent ? $content->importantDates($event) : collect(),
            'faqs' => $hasContent ? $content->faqs($event)->take(4) : collect(),
            'contacts' => $hasContent ? $content->contacts($event) : collect(),
            'packages' => $hasContent ? $content->packages($event)->take(3) : collect(),
            'photos' => $hasContent ? $content->photos($event)->take(6) : collect(),
            'pages' => $hasContent ? $content->availablePages($event) : [],
            'asic' => [
                'countdown_status' => $event?->nama_event,
            ],
        ]);
    }
}
