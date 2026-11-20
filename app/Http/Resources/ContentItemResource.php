<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** Representasi konten untuk pengelola: semua kolom + penanda terjemahan belum lengkap. */
class ContentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource->attributesToArray();
        if (method_exists($this->resource, 'translationComplete')) {
            $data['translation_complete'] = $this->resource->translationComplete();
        }
        if (isset($data['file_path'])) {
            $data['url'] = Storage::disk('public')->url($data['file_path']);
        }

        return $data;
    }
}
