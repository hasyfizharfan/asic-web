<?php

namespace App\Http\Resources;

use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RegistrationPeriod */
class RegistrationPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_period' => $this->id_period,
            'id_event' => $this->id_event,
            'nama_event' => $this->event?->nama_event,
            'nama_periode' => $this->nama_periode,
            'tanggal_mulai' => substr((string) $this->tanggal_mulai, 0, 10),
            'tanggal_selesai' => substr((string) $this->tanggal_selesai, 0, 10),
            'status_periode' => $this->status_periode,
        ];
    }
}
