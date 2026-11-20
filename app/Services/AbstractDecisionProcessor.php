<?php

namespace App\Services;

use App\Models\AbstractSubmission;
use App\Models\IssuedDocument;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Menindaklanjuti keputusan seleksi abstrak: Diterima → invoice + LOA (+email),
 * Ditolak → surat penolakan (+email). Idempoten dan tidak pernah menggagalkan keputusan
 * yang sudah tersimpan; kegagalan dilaporkan di `warnings` dan dapat dicoba ulang.
 */
class AbstractDecisionProcessor
{
    public function __construct(private DocumentService $documents, private InvoiceIssuer $invoices) {}

    /**
     * @return array{documents: array<string, ?IssuedDocument>, invoice: array<string, mixed>|null, warnings: array<int, string>}
     */
    public function process(AbstractSubmission $abstract): array
    {
        $reg = $abstract->registration()->with(['participant.userAccount', 'event'])->firstOrFail();
        $result = ['documents' => [], 'invoice' => null, 'warnings' => []];
        if (in_array($abstract->status_abstrak, ['Diterima', 'Ditolak'], true)) {
            app(StatusNotifier::class)->notify($reg, $abstract->status_abstrak === 'Diterima' ? 'abstract_accepted' : 'abstract_rejected', 'abstract:'.$abstract->id_abstract.':'.$abstract->status_abstrak);
        }

        try {
            if ($abstract->status_abstrak === 'Diterima') {
                $extra = [];
                try {
                    $inv = $this->invoices->issueFor($reg);
                    $result['invoice'] = ['status' => $inv['status'], 'reason' => $inv['reason'], 'id_invoice' => $inv['invoice']?->id_invoice, 'nomor_invoice' => $inv['invoice']?->nomor_invoice];
                    if ($inv['status'] === 'skipped') {
                        $result['warnings'][] = 'Invoice belum terbit: '.$inv['reason'];
                    }
                    if ($inv['invoice']) {
                        $extra = ['invoice_nomor' => $inv['invoice']->nomor_invoice, 'invoice_due' => $inv['invoice']->tanggal_jatuh_tempo->toDateString()];
                    }
                } catch (Throwable $e) {
                    Log::error('abstract.invoice_failed', ['id_abstract' => $abstract->id_abstract, 'error' => $e->getMessage()]);
                    $result['warnings'][] = 'Invoice gagal diterbitkan; coba ulang lewat endpoint penerbitan dokumen.';
                }
                $doc = $this->documents->issue($reg, 'LOA', $extra);
                $result['documents']['loa'] = $doc;
            } elseif ($abstract->status_abstrak === 'Ditolak') {
                $doc = $this->documents->issue($reg, 'Penolakan');
                $result['documents']['penolakan'] = $doc;
            } else {
                return $result;
            }
        } catch (Throwable $e) {
            Log::error('abstract.document_failed', ['id_abstract' => $abstract->id_abstract, 'error' => $e->getMessage()]);
            $result['warnings'][] = 'Dokumen gagal diterbitkan; coba ulang lewat endpoint penerbitan dokumen.';

            return $result;
        }

        // Email ke peserta dikirim oleh StatusChanged (dokumen LoA/invoice/penolakan terlampir).

        return $result;
    }
}
