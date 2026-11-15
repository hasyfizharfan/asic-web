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
        return $this->privateFilePath('file_ttd')
            ? route('api.digital-signatures.files', ['id_role' => $this->id_role, 'file_type' => 'ttd'])
            : null;
    }

    /**
     * Accessor URL Stempel.
     */
    public function getStempelUrlAttribute(): ?string
    {
        return $this->privateFilePath('file_stempel')
            ? route('api.digital-signatures.files', ['id_role' => $this->id_role, 'file_type' => 'stempel'])
            : null;
    }

    /**
     * Return a private-disk file path, migrating any legacy public-disk file on read.
     */
    public function privateFilePath(string $attribute): ?string
    {
        if (! in_array($attribute, ['file_ttd', 'file_stempel'], true)) {
            throw new \InvalidArgumentException('Jenis berkas tanda tangan tidak valid.');
        }

        $path = $this->getAttribute($attribute);
        if (! is_string($path) || $path === '') {
            return null;
        }

        $private = Storage::disk('local');
        if ($private->exists($path)) {
            Storage::disk('public')->delete($path);

            return $path;
        }

        $public = Storage::disk('public');
        if (! $public->exists($path)) {
            return null;
        }

        if (! $private->put($path, $public->get($path))) {
            return null;
        }

        $public->delete($path);

        return $path;
    }
}
