<?php

namespace App\Observers;

use App\Models\Registration;
use App\Services\DocumentService;
use App\Services\InvoiceIssuer;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * KF-20: invoice Listener terbit segera setelah registrasi (setelah commit, sehingga add-on
 * yang disimpan dalam transaksi yang sama ikut terhitung), bersama LoA Listener.
 * Invoice dan LoA Presenter (Oral/Poster) baru terbit saat abstrak Diterima lewat
 * AbstractDecisionProcessor; abstrak Ditolak hanya mendapat surat penolakan.
 * Kegagalan tidak menggagalkan registrasi; invoice dapat diterbitkan ulang secara idempoten.
 */
class RegistrationObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Registration $registration): void
    {
        if ($registration->isPresenter()) {
            return;
        }

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
