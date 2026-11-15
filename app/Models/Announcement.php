<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcement';
    protected $primaryKey = 'id_announcement';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['id_event','id_author','judul','isi_pengumuman','tanggal_publikasi','status'];

    protected function casts(): array
    {
        return ['tanggal_publikasi' => 'datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event', 'id_event');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'id_author', 'id_user');
    }

    /** Pengumuman yang boleh dilihat peserta: Dipublikasikan dan tanggal publikasi sudah tiba. */
    public function scopeVisibleToParticipants(Builder $q): Builder
    {
        return $q->where('status', 'Dipublikasikan')
            ->where(fn ($w) => $w->whereNull('tanggal_publikasi')->orWhere('tanggal_publikasi', '<=', now()));
    }
}
