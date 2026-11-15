<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedDocument extends Model
{
    use HasFactory;

    protected $table = 'issued_document';

    protected $primaryKey = 'id_document';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_registration', 'id_template', 'jenis_dokumen', 'nomor_dokumen', 'file_document', 'tanggal_terbit'];

    protected function casts(): array
    {
        return ['tanggal_terbit' => 'datetime'];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'id_template', 'id_template');
    }
}
