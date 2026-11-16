<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'acl_audit_log';

    public $timestamps = false;

    protected $fillable = ['id_user', 'user_email', 'action', 'target_type', 'target_id', 'note', 'meta', 'created_at'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'id_user', 'id_user');
    }
}
