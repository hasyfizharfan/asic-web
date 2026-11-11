<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AddOn extends Model
{
    use HasFactory;

    protected $table = 'add_on';
    protected $primaryKey = 'id_add_on';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_event',
        'nama_add_on',
        'deskripsi',
        'harga',
        'status',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    public function registrationAddOns(): HasMany
    {
        return $this->hasMany(RegistrationAddOn::class, 'id_add_on', 'id_add_on');
    }
}

