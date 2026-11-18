<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Halaman "Lihat semua notifikasi" untuk portal panitia (data diambil lewat /api/me/notifications). */
class StaffNotificationController extends Controller
{
    private const PORTALS = ['secretariat', 'treasurer', 'reviewer', 'admin'];

    public function __invoke(Request $request, string $portal): View
    {
        abort_unless(in_array($portal, self::PORTALS, true), 404);

        return view($portal.'.notifications', ['userName' => $request->user()?->email]);
    }
}
