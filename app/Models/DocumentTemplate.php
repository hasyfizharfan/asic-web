<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentTemplate extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'document_template';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_template';

    /**
     * Auto incrementing primary key.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Tipe primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Menggunakan kolom custom tanggal_upload.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Konstanta Jenis Dokumen Template (Poin 3 & Poin 5).
     */
    public const JENIS_ABSTRAK = 'Abstrak';
    public const JENIS_PRESENTASI = 'Presentasi';
    public const JENIS_ARTIKEL = 'Artikel';
    public const JENIS_LOA = 'LOA';
    public const JENIS_PENOLAKAN = 'Penolakan';
    public const JENIS_INVOICE = 'Invoice';
    public const JENIS_RECEIPT = 'Receipt';
    public const JENIS_SERTIFIKAT = 'Sertifikat';

    /**
     * Atribut yang dapat diisi massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_event',
        'nama_template',
        'jenis_dokumen',
        'format_nomor',
        'file_template',
        'tanggal_upload',
        'status_template',
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
     * Relasi belongsTo ke Event.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    /**
     * Accessor file template URL.
     */
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_template && Storage::disk('public')->exists($this->file_template)) {
            return Storage::disk('public')->url($this->file_template);
        }

        return null;
    }
}

