<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function index(): View
    {
        $event = Schema::hasTable('event')
            ? Event::query()->where('status_event', 'Aktif')->orderByDesc('tanggal_mulai')->first()
            : null;
        $fees = $event && Schema::hasTable('registration_fee')
            ? RegistrationFee::query()->where('id_event', $event->id_event)->with('registrationPeriod')->get()
            : collect();

        $feeAmount = static fn (string $type): ?string => $fees
            ->first(fn (RegistrationFee $fee): bool => $fee->registration_type === $type)
            ?->finalAmount();

        return view('public.landing', [
            'event' => $event,
            'fees' => $fees,
            'asic' => [
                'email' => config('mail.from.address', 'asic@agr.unand.ac.id'),
                'countdown_status' => $event?->nama_event ?? 'Event details will be announced soon',
                'fee_student_id' => $feeAmount('Student') ? 'IDR '.number_format((float) $feeAmount('Student'), 0, ',', '.') : 'IDR TBA',
                'fee_student_intl' => 'USD TBA',
                'fee_regular_id' => $feeAmount('Regular') ? 'IDR '.number_format((float) $feeAmount('Regular'), 0, ',', '.') : 'IDR TBA',
                'fee_regular_intl' => 'USD TBA',
            ],
        ]);
    }
}
