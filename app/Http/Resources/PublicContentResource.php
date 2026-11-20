<?php

namespace App\Http\Resources;

use App\Models\ContactPerson;
use App\Models\EventContent;
use App\Models\EventPhoto;
use App\Models\Faq;
use App\Models\ImportantDate;
use App\Models\SponsorshipPackage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Konten publik dalam bahasa aktif (jatuh ke bahasa lain bila kosong). Hanya kolom yang
 * relevan untuk publik; tidak ada catatan internal atau data pengelola.
 */
class PublicContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $m = $this->resource;

        return match (true) {
            $m instanceof EventContent => ['id' => $m->id_content, 'section' => $m->section, 'judul' => $m->localized('judul'), 'isi' => $m->localized('isi'), 'tautan' => $m->tautan],
            $m instanceof ImportantDate => ['id' => $m->id_date, 'label' => $m->localized('label'), 'tanggal_mulai' => $m->tanggal_mulai?->toDateString(), 'tanggal_selesai' => $m->tanggal_selesai?->toDateString()],
            $m instanceof Faq => ['id' => $m->id_faq, 'pertanyaan' => $m->localized('pertanyaan'), 'jawaban' => $m->localized('jawaban'), 'kategori' => $m->kategori],
            $m instanceof ContactPerson => array_filter(['id' => $m->id_contact, 'nama' => $m->nama, 'peran' => $m->localized('peran'), 'email' => $m->email, 'telepon' => $m->telepon, 'whatsapp' => $m->whatsapp], fn ($v) => $v !== null && $v !== ''),
            $m instanceof EventPhoto => ['id' => $m->id_photo, 'url' => Storage::disk('public')->url($m->file_path), 'caption' => $m->localized('caption')],
            $m instanceof SponsorshipPackage => ['id' => $m->id_package, 'nama' => $m->nama, 'deskripsi' => $m->localized('deskripsi'), 'manfaat' => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $m->localized('manfaat'))))), 'harga_teks' => $m->harga_teks, 'kuota' => $m->kuota],
            default => [],
        };
    }
}
