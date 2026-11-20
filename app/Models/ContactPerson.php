<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPerson extends Model
{
    use HasBilingualFields;

    public const BILINGUAL = ['peran'];

    protected $table = 'contact_person';

    protected $primaryKey = 'id_contact';

    protected $fillable = [
        'id_event',
        'nama',
        'peran_id',
        'peran_en',
        'email',
        'telepon',
        'whatsapp',
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
