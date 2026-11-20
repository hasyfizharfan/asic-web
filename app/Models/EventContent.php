<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventContent extends Model
{
    use HasBilingualFields;

    public const BILINGUAL = ['judul', 'isi'];

    public const SECTIONS = ['scope', 'accommodation', 'pickup', 'presentation_oral', 'presentation_poster', 'visa', 'travel', 'sponsorship_intro', 'exhibition_intro', 'about', 'publication', 'facilities', 'committee', 'venue_info'];

    protected $table = 'event_content';

    protected $primaryKey = 'id_content';

    protected $fillable = [
        'id_event',
        'section',
        'judul_id',
        'judul_en',
        'isi_id',
        'isi_en',
        'tautan',
        'status',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }
}
