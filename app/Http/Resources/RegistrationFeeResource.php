<?php

namespace App\Http\Resources;

use App\Models\RegistrationFee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RegistrationFee
 */
class RegistrationFeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_fee' => $this->id_fee,
            'id_event' => $this->id_event,
            'id_period' => $this->id_period,
            'registration_type' => $this->registration_type,
            'attendance_mode' => $this->attendance_mode,
            'tujuan_publikasi' => $this->tujuan_publikasi,
            'amount' => (string) $this->amount,
            'discount' => (string) $this->discount,
            'final_amount' => $this->finalAmount(),
            'event' => $this->whenLoaded('event', fn () => [
                'id_event' => $this->event->id_event,
                'nama_event' => $this->event->nama_event,
                'status_event' => $this->event->status_event,
            ]),
            'period' => $this->whenLoaded('registrationPeriod', fn () => [
                'id_period' => $this->registrationPeriod->id_period,
                'nama_periode' => $this->registrationPeriod->nama_periode,
                'tanggal_mulai' => (string) $this->registrationPeriod->tanggal_mulai,
                'tanggal_selesai' => (string) $this->registrationPeriod->tanggal_selesai,
                'status_periode' => $this->registrationPeriod->status_periode,
            ]),
            'invoices_count' => $this->whenCounted('invoices'),
            'is_used' => $this->when(isset($this->resource->invoices_count), fn () => $this->invoices_count > 0),
        ];
    }
}
