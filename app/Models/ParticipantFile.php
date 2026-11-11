<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantFile extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'participant_file';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_file';

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
     * Menggunakan kolom custom tanggal_upload.
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
        'jenis_file',
        'nama_file',
        'file_path',
        'tanggal_upload',
        'status_file',
    ];

    /**
     * Cast atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_upload' => 'datetime',
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

