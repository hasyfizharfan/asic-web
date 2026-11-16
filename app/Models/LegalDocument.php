<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasBilingualFields;

    public const BILINGUAL = ['judul', 'isi'];

    public const TYPES = ['terms', 'privacy', 'ethics'];

    protected $table = 'legal_document';

    protected $primaryKey = 'id_legal';

    protected $fillable = [
        'jenis',
        'versi',
        'judul_id',
        'judul_en',
        'isi_id',
        'isi_en',
        'berlaku_mulai',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'berlaku_mulai' => 'date',
        ];
    }
}
