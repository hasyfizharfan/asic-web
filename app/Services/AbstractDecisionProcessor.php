<?php

namespace App\Services;

use App\Mail\DocumentIssuedMail;
use App\Models\AbstractSubmission;
use App\Models\IssuedDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        $files = [];

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
                        $invDoc = IssuedDocument::where('id_registration', $reg->id_registration)->where('jenis_dokumen', 'Invoice')->first();
                        $invDoc && $files[] = ['path' => $invDoc->file_document, 'name' => 'Invoice.pdf'];
                    }
                } catch (Throwable $e) {
                    Log::error('abstract.invoice_failed', ['id_abstract' => $abstract->id_abstract, 'error' => $e->getMessage()]);
                    $result['warnings'][] = 'Invoice gagal diterbitkan; coba ulang lewat endpoint penerbitan dokumen.';
                }
                $doc = $this->documents->issue($reg, 'LOA', $extra);
                $result['documents']['loa'] = $doc;
                array_unshift($files, ['path' => $doc->file_document, 'name' => 'LOA.pdf']);
                $subject = 'Letter of Acceptance - '.$reg->event->nama_event;
                $body = "Your abstract has been accepted. Please find the Letter of Acceptance attached.\nAbstrak Anda telah diterima. Terlampir Letter of Acceptance.";
            } elseif ($abstract->status_abstrak === 'Ditolak') {
                $doc = $this->documents->issue($reg, 'Penolakan');
                $result['documents']['penolakan'] = $doc;
                $files[] = ['path' => $doc->file_document, 'name' => 'Letter-of-Rejection.pdf'];
                $subject = 'Abstract Decision - '.$reg->event->nama_event;
                $body = "We regret that your abstract was not accepted. Please see the attached letter.\nMohon maaf, abstrak Anda belum dapat diterima. Terlampir surat penolakan.";
            } else {
                return $result;
            }
        } catch (Throwable $e) {
            Log::error('abstract.document_failed', ['id_abstract' => $abstract->id_abstract, 'error' => $e->getMessage()]);
            $result['warnings'][] = 'Dokumen gagal diterbitkan; coba ulang lewat endpoint penerbitan dokumen.';

            return $result;
        }

        $email = $reg->participant?->userAccount?->email;
        if ($email) {
            try {
                Mail::to($email)->send(new DocumentIssuedMail($subject, $body, $files, $this->documents->disk()));
            } catch (Throwable $e) {
                Log::warning('abstract.email_failed', ['id_abstract' => $abstract->id_abstract, 'error' => $e->getMessage()]);
                $result['warnings'][] = 'Email dokumen gagal dikirim.';
            }
        }

        return $result;
    }
}
