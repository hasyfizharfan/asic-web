<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasBilingualFields;

    public const BILINGUAL = ['pertanyaan', 'jawaban'];

    protected $table = 'faq';

    protected $primaryKey = 'id_faq';

    protected $fillable = [
        'id_event',
        'pertanyaan_id',
        'pertanyaan_en',
        'jawaban_id',
        'jawaban_en',
        'kategori',
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
