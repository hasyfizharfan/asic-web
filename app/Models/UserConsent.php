<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** KF-35: catatan persetujuan syarat/privasi/etika per pengguna (versi yang disetujui saat itu). */
class UserConsent extends Model
{
    protected $table = 'user_consent';

    protected $primaryKey = 'id_consent';

    public $timestamps = false;

    protected $fillable = ['id_user', 'id_legal_document', 'jenis', 'versi', 'accepted_at', 'ip_address'];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'id_user', 'id_user');
    }

    public function legalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'id_legal_document', 'id_legal');
    }
}
