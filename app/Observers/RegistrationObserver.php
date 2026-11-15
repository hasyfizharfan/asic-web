<?php

namespace App\Observers;

use App\Models\Registration;
use App\Services\DocumentService;
use App\Services\InvoiceIssuer;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Semua peserta mendapat invoice segera setelah registrasi (setelah commit, sehingga add-on
 * yang disimpan dalam transaksi yang sama ikut terhitung). LoA Listener terbit saat daftar;
 * LoA Presenter tetap menunggu keputusan abstrak.
 * Kegagalan tidak menggagalkan registrasi; invoice dapat diterbitkan ulang secara idempoten.
 */
class RegistrationObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Registration $registration): void
    {
        $invoice = null;
        try {
            $result = app(InvoiceIssuer::class)->issueFor($registration);
            $invoice = $result['invoice'] ?? null;
            if ($result['status'] === 'skipped') {
                Log::warning('invoice.skipped', ['id_registration' => $registration->id_registration, 'reason' => $result['reason']]);
            }
        } catch (Throwable $e) {
            Log::error('invoice.listener_failed', ['id_registration' => $registration->id_registration, 'error' => $e->getMessage()]);
        }

        if ($registration->isPresenter()) {
            return;
        }

        // Kesepakatan mitra: Listener tetap mendapat LoA otomatis setelah registrasi.
        try {
            app(DocumentService::class)->issue($registration, 'LOA', $invoice ? [
                'invoice_nomor' => $invoice->nomor_invoice,
                'invoice_due' => $invoice->tanggal_jatuh_tempo?->toDateString(),
            ] : []);
        } catch (Throwable $e) {
            Log::error('loa.listener_failed', ['id_registration' => $registration->id_registration, 'error' => $e->getMessage()]);
        }
    }
}
