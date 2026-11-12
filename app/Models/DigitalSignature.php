<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DigitalSignature extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'digital_signature';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_signature';

    /**
     * Tipe primary key.
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
     * Atribut yang dapat diisi massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_role',
        'file_ttd',
        'file_stempel',
        'tanggal_upload',
    ];

    /**
     * Cast atribut model.
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
     * Relasi belongsTo ke Role.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    /**
     * Accessor URL Tanda Tangan.
     */
    public function getTtdUrlAttribute(): ?string
    {
        if ($this->file_ttd && Storage::disk('public')->exists($this->file_ttd)) {
            return Storage::disk('public')->url($this->file_ttd);
        }

        return null;
    }

    /**
     * Accessor URL Stempel.
     */
    public function getStempelUrlAttribute(): ?string
    {
        if ($this->file_stempel && Storage::disk('public')->exists($this->file_stempel)) {
            return Storage::disk('public')->url($this->file_stempel);
        }

        return null;
    }
}

