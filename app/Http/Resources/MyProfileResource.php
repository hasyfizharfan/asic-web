<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_user' => $this->id_user, 'email' => $this->email,
            'participant' => $this->participant ? [
                'id_participant' => $this->participant->id_participant,
                'prefix' => $this->participant->prefix, 'first_name' => $this->participant->first_name,
                'last_name' => $this->participant->last_name, 'full_name' => $this->participant->full_name,
                'passport_no' => $this->participant->passport_no, 'job_title' => $this->participant->job_title,
                'phone' => $this->participant->phone, 'institution' => $this->participant->institution,
                'address' => $this->participant->address, 'city' => $this->participant->city,
                'country' => $this->participant->country, 'postcode' => $this->participant->postcode,
            ] : null,
        ];
    }
}
