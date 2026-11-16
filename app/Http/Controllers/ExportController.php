<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Support\Excel\XlsxWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** M-10 / KF-25: ekspor Excel (.xlsx). Akses dibatasi per jenis data lewat middleware permission pada route. */
class ExportController extends Controller
{
    private function download(string $name, XlsxWriter $xlsx): Response
    {
        return response($xlsx->toString(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$name.'-'.now()->format('Ymd-His').'.xlsx"',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function participants(Request $request, ReportService $r): Response
    {
        [$h, $rows] = $r->participantRows(RecapController::filters($request));

        return $this->download('peserta', (new XlsxWriter)->addSheet('Peserta', $h, $rows));
    }

    public function abstracts(Request $request, ReportService $r): Response
    {
        $filters = RecapController::filters($request) + $request->validate([
            'scope' => ['nullable', 'string', 'max:150'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);
        [$h, $rows] = $r->abstractRows($filters);

        return $this->download('abstrak', (new XlsxWriter)->addSheet('Abstrak', $h, $rows));
    }

    public function payments(Request $request, ReportService $r): Response
    {
        [$h, $rows] = $r->paymentRows(RecapController::filters($request));

        return $this->download('pembayaran', (new XlsxWriter)->addSheet('Pembayaran', $h, $rows));
    }

    public function invoices(Request $request, ReportService $r): Response
    {
        [$h, $rows] = $r->invoiceRows(RecapController::filters($request));

        return $this->download('invoice', (new XlsxWriter)->addSheet('Invoice', $h, $rows));
    }

    public function recap(Request $request, ReportService $r): Response
    {
        $xlsx = new XlsxWriter;
        foreach ($r->recapSheets(RecapController::filters($request)) as $name => [$h, $rows]) {
            $xlsx->addSheet($name, $h, $rows);
        }

        return $this->download('rekapitulasi', $xlsx);
    }
}
