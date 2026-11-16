<?php

namespace App\Http\Resources;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Registration */
class RegistrationSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_registration' => $this->id_registration,
            'registration_status' => $this->registration_status,
            'registration_date' => $this->registration_date,
            'registration_type' => $this->registration_type,
            'attendance_mode' => $this->attendance_mode,
            'participation_type' => $this->participation_type,
            'event' => $this->event?->nama_event,
            'participant' => [
                'full_name' => $this->participant?->full_name,
                'email' => $this->participant?->userAccount?->email,
                'institution' => $this->participant?->institution,
                'country' => $this->participant?->country,
            ],
            'invoice' => [
                'status_pembayaran' => $this->invoice?->status_pembayaran,
            ],
            'abstract' => [
                'judul_abstrak' => $this->abstractSubmission?->judul_abstrak,
                'status_abstrak' => $this->abstractSubmission?->status_abstrak,
                'scope' => $this->abstractSubmission?->scope,
            ],
        ];
    }
}
