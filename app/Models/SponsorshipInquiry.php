<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorshipInquiry extends Model
{
    public const STATUSES = ['Baru', 'Dihubungi', 'Selesai', 'Ditolak'];

    public const TYPES = ['Sponsorship', 'Exhibition'];

    protected $table = 'sponsorship_inquiry';

    protected $primaryKey = 'id_inquiry';

    protected $fillable = [
        'id_event',
        'id_package',
        'jenis',
        'nama_organisasi',
        'nama_kontak',
        'email',
        'telepon',
        'pesan',
        'catatan_internal',
        'status',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SponsorshipPackage::class, 'id_package', 'id_package');
    }
}
