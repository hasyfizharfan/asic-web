<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPhoto extends Model
{
    use HasBilingualFields;

    public const BILINGUAL = ['caption'];

    protected $table = 'event_photo';

    protected $primaryKey = 'id_photo';

    protected $fillable = [
        'id_event',
        'file_path',
        'caption_id',
        'caption_en',
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
