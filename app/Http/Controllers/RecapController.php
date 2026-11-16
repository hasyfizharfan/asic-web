<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** M-10: rekapitulasi data. Seluruh angka dari database; filter id_event, id_period, status. */
class RecapController extends Controller
{
    use ApiResponses;

    public static function filters(Request $request): array
    {
        return $request->validate([
            'id_event' => ['nullable', 'integer', 'exists:event,id_event'],
            'id_period' => ['nullable', 'integer', 'exists:registration_period,id_period'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
    }

    public function registrations(Request $request, ReportService $r): JsonResponse
    {
        return $this->ok(__('asic-api.messages.rekap_registrasi_berhasil_dimuat'), $r->registrations(self::filters($request)));
    }

    public function participants(Request $request, ReportService $r): JsonResponse
    {
        return $this->ok(__('asic-api.messages.rekap_peserta_berhasil_dimuat'), $r->participants(self::filters($request)));
    }

    public function abstracts(Request $request, ReportService $r): JsonResponse
    {
        return $this->ok(__('asic-api.messages.rekap_abstrak_dan_hasil_seleksi_berhasil_dimuat'), $r->abstracts(self::filters($request)));
    }

    public function invoices(Request $request, ReportService $r): JsonResponse
    {
        return $this->ok(__('asic-api.messages.rekap_invoice_berhasil_dimuat'), $r->invoices(self::filters($request)));
    }

    public function payments(Request $request, ReportService $r): JsonResponse
    {
        return $this->ok(__('asic-api.messages.rekap_pembayaran_berhasil_dimuat'), $r->payments(self::filters($request)));
    }

    public function attendance(Request $request, ReportService $r): JsonResponse
    {
        return $this->ok(__('asic-api.messages.rekap_kehadiran_per_sesi_berhasil_dimuat'), $r->attendance(self::filters($request)));
    }
}
