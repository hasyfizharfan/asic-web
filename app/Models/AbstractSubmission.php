<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AbstractSubmission extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'abstract_submission';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_abstract';

    /**
     * Tipe data primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Auto incrementing primary key.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Menggunakan kolom custom tanggal_submit.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_registration',
        'judul_abstrak',
        'scope',
        'metode_presentasi',
        'file_abstrak',
        'tanggal_submit',
        'status_abstrak',
    ];

    /**
     * Cast atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_submit' => 'datetime',
        ];
    }

    /**
     * Relasi belongsTo ke Registration.
     *
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }

    /**
     * Relasi hasMany ke AbstractReview.
     *
     * @return HasMany<AbstractReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(AbstractReview::class, 'id_abstract', 'id_abstract');
    }

    /**
     * Relasi hasOne ke review terbaru.
     *
     * @return HasOne<AbstractReview, $this>
     */
    public function latestReview(): HasOne
    {
        return $this->hasOne(AbstractReview::class, 'id_abstract', 'id_abstract')->latestOfMany('id_review');
    }
}
