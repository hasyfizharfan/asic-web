<?php

namespace App\Http\Resources;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DocumentTemplate */
class DocumentTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $issued = in_array($this->jenis_dokumen, DocumentTemplate::ISSUED, true);

        return [
            'id_template' => $this->id_template,
            'id_event' => $this->id_event,
            'kelompok' => $issued ? 'issued' : 'downloads',
            'jenis_dokumen' => $this->jenis_dokumen,
            'nama_template' => $this->nama_template,
            'format_nomor' => $this->when($issued, $this->format_nomor),
            'status_template' => $this->status_template,
            'bawaan' => str_starts_with((string) $this->file_template, 'builtin:'),
            'subjenis' => $this->subjenis,
            'bahasa' => $this->bahasa,
            'versi' => $this->versi,
            'nama_file_asli' => $this->nama_file_asli,
            'mime' => $this->mime,
            'ukuran_file' => $this->ukuran_file,
            'tanggal_upload' => $this->tanggal_upload?->toIso8601String(),
        ];
    }
}
