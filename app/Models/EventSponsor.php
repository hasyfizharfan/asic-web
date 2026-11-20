<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EventSponsor extends Model
{
    /** Kelompok tampilan di website, berurutan seperti biasa di halaman konferensi. */
    public const CATEGORIES = ['Penyelenggara', 'Pendukung', 'Sponsor', 'Mitra Publikasi', 'Mitra Media'];

    protected $table = 'event_sponsor';

    protected $primaryKey = 'id_sponsor';

    protected $fillable = ['id_event', 'nama', 'kategori', 'tingkat', 'logo_path', 'url', 'status', 'urutan'];

    protected $appends = ['logo_url'];

    protected function casts(): array
    {
        return ['urutan' => 'integer'];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }
}
