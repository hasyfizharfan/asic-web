<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Catatan audit konten ke acl_audit_log (tanpa data sensitif). Gagal mencatat tidak membatalkan aksi. */
final class Audit
{
    /** @param array<string, scalar|null> $meta */
    public static function log(string $action, string $targetType, int|string|null $targetId, ?string $note = null, array $meta = []): void
    {
        try {
            $actor = Auth::user();
            AuditLog::create([
                'id_user' => $actor?->id_user,
                'user_email' => $actor?->email ?? 'system',
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId === null ? null : (string) $targetId,
                'note' => $note,
                'meta' => $meta,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('audit.failed', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }
}
