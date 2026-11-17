<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class EventSession extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'event_session';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_session';

    /**
     * Tipe primary key (INT Auto Increment).
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
     * Tabel tidak menggunakan timestamp created_at dan updated_at default Laravel.
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
        'id_event',
        'nama_sesi',
        'waktu',
        'waktu_selesai',
        'qr_token',
        'qr_code_path',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    /**
     * Relasi belongsTo ke Event.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    /**
     * Relasi hasMany ke Attendance.
     *
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'id_session', 'id_session');
    }

    /**
     * Accessor URL QR Code sesi dari Storage public.
     */
    /**
     * Isi QR sesi berupa tautan check-in. Dipindai dengan kamera di website maupun kamera HP biasa,
     * keduanya membuka halaman kehadiran peserta dan langsung mencatat hadir.
     */
    public static function checkinUrlFor(string $token): string
    {
        return url('/participant/attendance').'?sesi='.rawurlencode($token);
    }

    public function checkinUrl(): string
    {
        return self::checkinUrlFor((string) $this->qr_token);
    }

    /** Ambil token sesi dari hasil pindai (tautan check-in atau token mentah). */
    public static function tokenFromScan(string $value): string
    {
        $value = trim($value);
        if (str_contains($value, 'sesi=')) {
            parse_str((string) parse_url($value, PHP_URL_QUERY), $params);
            if (! empty($params['sesi']) && is_string($params['sesi'])) {
                return trim($params['sesi']);
            }
        }

        return $value;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        if ($this->qr_code_path && Storage::disk('public')->exists($this->qr_code_path)) {
            return Storage::disk('public')->url($this->qr_code_path);
        }

        return null;
    }
}
