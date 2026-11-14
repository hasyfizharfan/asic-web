<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('registration-periods:close-expired', function () {
    $closed = DB::table('registration_period')
        ->where('status_periode', 'Aktif')
        ->whereDate('tanggal_selesai', '<', now()->toDateString())
        ->update(['status_periode' => 'Ditutup']);

    $this->info("{$closed} periode pendaftaran ditutup.");
})->purpose('Tutup periode pendaftaran yang telah melewati tanggal selesai');

Schedule::command('registration-periods:close-expired')->hourly();
