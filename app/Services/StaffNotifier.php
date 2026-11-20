<?php

namespace App\Services;

use App\Models\Role;
use App\Models\UserAccount;
use App\Notifications\StaffAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Mengirim pemberitahuan ke semua akun AKTIF pada satu peran panitia.
 * - Dikirim setelah transaksi utama commit; kegagalan hanya dicatat ke log (aksi peserta tetap berhasil).
 * - Tidak memuat data sensitif: abstrak tanpa nama peserta (Tim Abstrak cukup judul & bidang).
 */
class StaffNotifier
{
    /** @param array<string, scalar|null> $params */
    public function notifyRole(string $role, string $kind, ?string $url, array $params = [], ?string $dedupe = null): void
    {
        DB::afterCommit(function () use ($role, $kind, $url, $params, $dedupe): void {
            try {
                $idRole = Role::where('nama_role', $role)->value('id_role');
                $users = UserAccount::query()->where('id_role', $idRole)->where('status_akun', 'Aktif')->get();
                if ($dedupe) {
                    $users = $users->reject(fn (UserAccount $u) => $u->notifications()->where('data->dedupe', $dedupe)->exists());
                }
                if ($users->isNotEmpty()) {
                    Notification::send($users, new StaffAlert($kind, $url, $params, $dedupe));
                }
            } catch (Throwable $e) {
                Log::warning('staff_notification.failed', ['kind' => $kind, 'role' => $role, 'error' => $e->getMessage()]);
            }
        });
    }

    /** Tautan relatif ke halaman portal; null bila rute tidak ada. */
    public static function url(string $route, array $params = []): ?string
    {
        try {
            return route($route, $params, false);
        } catch (Throwable) {
            return null;
        }
    }
}
