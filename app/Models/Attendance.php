<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'attendance';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_attendance';

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
     * Menggunakan kolom custom tanggal_generate dan tanggal_check_in.
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
        'qr_code',
        'tanggal_generate',
        'tanggal_check_in',
        'status_kehadiran',
    ];

    /**
     * Cast atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_generate' => 'datetime',
            'tanggal_check_in' => 'datetime',
        ];
    }

    /**
     * Relasi belongsTo ke Registration.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }

    /**
     * Helper untuk memeriksa apakah peserta sudah hadir.
     */
    public function isHadir(): bool
    {
        return $this->status_kehadiran === 'Hadir';
    }
}

