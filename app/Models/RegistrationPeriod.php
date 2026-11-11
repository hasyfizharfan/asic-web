<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationPeriod extends Model
{
    use HasFactory;

    protected $table = 'registration_period';
    protected $primaryKey = 'id_period';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_event',
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_periode',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'id_period', 'id_period');
    }
}

