<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportantDate extends Model
{
    use HasBilingualFields;

    public const BILINGUAL = ['label'];

    protected $table = 'important_date';

    protected $primaryKey = 'id_date';

    protected $fillable = [
        'id_event',
        'label_id',
        'label_en',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'urutan' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }
}
