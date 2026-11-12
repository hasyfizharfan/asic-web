<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $table = 'event';
    protected $primaryKey = 'id_event';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nama_event',
        'tema',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'mode_kegiatan',
        'status_event',
    ];

    public function registrationPeriods(): HasMany
    {
        return $this->hasMany(RegistrationPeriod::class, 'id_event', 'id_event');
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(AddOn::class, 'id_event', 'id_event');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'id_event', 'id_event');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class, 'id_event', 'id_event');
    }

    public function documentTemplates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'id_event', 'id_event');
    }
}

