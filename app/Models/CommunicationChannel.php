<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationChannel extends Model
{
    use HasFactory;

    protected $table = 'communication_channel';
    protected $primaryKey = 'id_channel';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['id_event','nama_kanal','jenis_kanal','url','status'];

    public const TYPES = ['WhatsApp', 'Google Group', 'Telegram', 'Lainnya'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }
}
