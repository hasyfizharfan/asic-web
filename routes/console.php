<?php

use App\Models\EventSession;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('registration-periods:close-expired', function () {
    $closed = DB::table('registration_period')
        ->where('status_periode', 'Aktif')
        ->whereDate('tanggal_selesai', '<', now()->toDateString())
        ->update(['status_periode' => 'Tidak Aktif']);

    $this->info("{$closed} periode pendaftaran ditutup.");
})->purpose('Tutup periode pendaftaran yang telah melewati tanggal selesai');

Artisan::command('asic:session-qr-refresh', function () {
    $count = 0;
    foreach (EventSession::whereNotNull('qr_token')->get() as $session) {
        $path = $session->qr_code_path ?: 'session_qrcodes/qr_'.$session->qr_token.'.svg';
        $svg = (string) QrCode::format('svg')->size(350)->errorCorrection('H')->generate($session->checkinUrl());
        Storage::disk('public')->put($path, $svg);
        $session->forceFill(['qr_code_path' => $path])->save();
        $count++;
    }
    $this->info("{$count} QR sesi diperbarui (isi: tautan check-in).");
})->purpose('Perbarui gambar QR sesi agar berisi tautan check-in');

Schedule::command('registration-periods:close-expired')->hourly();
// Email notifikasi (QUEUE_CONNECTION=database): diproses tiap menit, berhenti saat antrean kosong.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')->everyMinute()->withoutOverlapping();
