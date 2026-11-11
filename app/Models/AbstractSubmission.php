<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Atribut yang dapat diisi.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_registration',
        'judul_abstrak',
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
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }
}

