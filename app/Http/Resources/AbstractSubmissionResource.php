<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbstractSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isReviewer = $user?->role?->nama_role === Role::ROLE_TIM_ABSTRAK;

        if (! $isReviewer) {
            return $this->resource->toArray();
        }

        return [
            'id_abstract' => $this->id_abstract,
            'id_registration' => $this->id_registration,
            'judul_abstrak' => $this->judul_abstrak,
            'scope' => $this->scope,
            'metode_presentasi' => $this->metode_presentasi,
            'tanggal_submit' => $this->tanggal_submit,
            'status_abstrak' => $this->status_abstrak,
            'registration' => $this->registration ? [
                'id_registration' => $this->registration->id_registration,
                'id_event' => $this->registration->id_event,
            ] : null,
            'reviews' => $this->reviews->sortBy('tanggal_review')->values()->map(fn ($review) => [
                'id_review' => $review->id_review,
                'hasil_review' => $review->hasil_review,
                'catatan_review' => $review->catatan_review,
                'tanggal_review' => $review->tanggal_review,
            ]),
            'reviewed_by_me' => $this->reviews->contains('id_reviewer', $user->id_user),
        ];
    }
}
