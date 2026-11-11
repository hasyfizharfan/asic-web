<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbstractReview extends Model
{
    use HasFactory;

    protected $table = 'abstract_review';
    protected $primaryKey = 'id_review';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_abstract',
        'id_reviewer',
        'tanggal_review',
        'hasil_review',
        'catatan_review',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_review' => 'datetime',
        ];
    }

    public function abstractSubmission(): BelongsTo
    {
        return $this->belongsTo(AbstractSubmission::class, 'id_abstract', 'id_abstract');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'id_reviewer', 'id_user');
    }
}

