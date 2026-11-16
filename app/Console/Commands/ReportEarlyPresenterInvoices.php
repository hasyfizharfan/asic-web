<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

/**
 * KF-20: laporan read-only invoice presenter yang terbit sebelum abstraknya Diterima
 * (sisa aturan lama Tahap C). Tidak mengubah atau menghapus data apa pun.
 */
class ReportEarlyPresenterInvoices extends Command
{
    protected $signature = 'asic:report-early-presenter-invoices';

    protected $description = 'Daftar invoice presenter yang abstraknya belum Diterima (read-only)';

    public function handle(): int
    {
        $rows = Invoice::query()
            ->with(['registration.participant', 'registration.abstractSubmission'])
            ->whereHas('registration', fn ($q) => $q->whereIn('participation_type', ['Oral Presenter', 'Poster Presenter']))
            ->get()
            ->filter(fn (Invoice $invoice) => $invoice->registration?->abstractSubmission?->status_abstrak !== 'Diterima')
            ->values();

        $this->info('Invoice presenter dengan abstrak belum Diterima: '.$rows->count());
        if ($rows->isNotEmpty()) {
            $this->table(
                ['id_invoice', 'nomor_invoice', 'id_registration', 'peserta', 'status_abstrak', 'status_pembayaran'],
                $rows->map(fn (Invoice $invoice) => [
                    $invoice->id_invoice,
                    $invoice->nomor_invoice,
                    $invoice->id_registration,
                    $invoice->registration?->participant?->full_name,
                    $invoice->registration?->abstractSubmission?->status_abstrak ?? '(belum mengirim)',
                    $invoice->status_pembayaran,
                ])->all()
            );
        }

        return self::SUCCESS;
    }
}
