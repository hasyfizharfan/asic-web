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
     * Atribut yang dapat diisi massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_registration',
        'id_session',
        'tanggal_generate',
        'tanggal_check_in',
        'status_kehadiran',
        'status_presentasi',
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
     *
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'id_registration', 'id_registration');
    }

    /**
     * Relasi belongsTo ke EventSession (QR Sesi).
     *
     * @return BelongsTo<EventSession, $this>
     */
    public function eventSession(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'id_session', 'id_session');
    }

    /**
     * Helper untuk memeriksa apakah peserta sudah hadir pada sesi ini.
     */
    public function isHadir(): bool
    {
        return $this->status_kehadiran === 'Hadir';
    }

    /**
     * Helper untuk memeriksa apakah peserta sudah melakukan presentasi.
     */
    public function isSudahPresentasi(): bool
    {
        return $this->status_presentasi === 'Sudah';
    }
}
