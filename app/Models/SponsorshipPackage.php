<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorshipPackage extends Model
{
    use HasBilingualFields;

    public const BILINGUAL = ['deskripsi', 'manfaat'];

    protected $table = 'sponsorship_package';

    protected $primaryKey = 'id_package';

    protected $fillable = [
        'id_event',
        'nama',
        'deskripsi_id',
        'deskripsi_en',
        'manfaat_id',
        'manfaat_en',
        'harga_teks',
        'kuota',
        'status',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'kuota' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }
}
