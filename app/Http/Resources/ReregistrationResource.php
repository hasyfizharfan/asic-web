<?php

namespace App\Http\Resources;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Registration */
class ReregistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_registration' => $this->id_registration,
            'id_event' => $this->id_event,
            'registration_status' => $this->registration_status,
            'attendance_mode' => $this->attendance_mode,
            'participation_type' => $this->participation_type,
            'reregistered_at' => $this->reregistered_at?->toIso8601String(),
            'participant' => [
                'full_name' => $this->participant?->full_name,
                'institution' => $this->participant?->institution,
            ],
            'invoice' => ['status_pembayaran' => $this->invoice?->status_pembayaran],
        ];
    }
}
