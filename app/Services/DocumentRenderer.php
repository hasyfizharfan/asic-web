<?php

namespace App\Services;

use App\Support\Pdf\SimplePdf;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Template dokumen tetap (LOA, Surat Penolakan, Invoice, Receipt).
 * Data peserta/abstrak/tagihan disisipkan secara dinamis dari $ctx.
 * Catatan: berkas template resmi dari client belum diterima; tata letak ini adalah
 * template bawaan yang dapat diganti tanpa mengubah alur penerbitan.
 */
class DocumentRenderer
{
    /**
     * @param  array<string, mixed>  $c
     */
    public function render(string $jenis, array $c): string
    {
        return match ($jenis) {
            'LOA' => $this->loa($c),
            'Penolakan' => $this->rejection($c),
            'Invoice' => $this->invoice($c),
            'Receipt' => $this->receipt($c),
            default => throw new InvalidArgumentException("Jenis dokumen '$jenis' tidak didukung."),
        };
    }

    public static function money(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 2, ',', '.');
    }

    private function header(SimplePdf $pdf, string $title, array $c): void
    {
        $pdf->text('ASIC 2027 - Annual Scientific International Conference', 13, true, 'center')
            ->text('Faculty of Agriculture, Universitas Andalas', 10, false, 'center')
            ->space(4)->line()->space(6)
            ->text($title, 15, true, 'center')->space(6)
            ->text('No. / Number : '.$c['nomor'])
            ->text('Tanggal / Date : '.Carbon::parse($c['tanggal'])->format('d F Y'))
            ->space(8);
    }

    private function loa(array $c): string
    {
        $pdf = new SimplePdf;
        $this->header($pdf, 'LETTER OF ACCEPTANCE', $c);
        $pdf->text('To / Kepada Yth.', 11, true)
            ->text($c['name'])
            ->text(trim(($c['institution'] ?? '').' '.($c['country'] ? '('.$c['country'].')' : '')))
            ->space(8)
            ->text("Dear {$c['name']},")->space(4)
            ->text("We are pleased to inform you that, after review by the Abstract Team, your abstract entitled:")->space(2)
            ->text('"'.$c['abstract_title'].'"', 11, true, 'left', 14)->space(2)
            ->text("has been ACCEPTED for {$c['presentation_method']} presentation at {$c['event_name']}"
                .($c['event_dates'] ? ", held on {$c['event_dates']}" : '')
                .($c['event_location'] ? " at {$c['event_location']}" : '').'.')
            ->space(4);
        if (! empty($c['review_notes'])) {
            $pdf->text('Reviewer notes:', 11, true)->text($c['review_notes'], 11, false, 'left', 14)->space(4);
        }
        if (! empty($c['invoice_nomor'])) {
            $pdf->text("Your registration invoice ({$c['invoice_nomor']}) has been issued together with this letter. "
                .'Payment must be completed before '.Carbon::parse($c['invoice_due'])->format('d F Y').'.')->space(4);
        }
        $pdf->text('We look forward to welcoming you.')->space(24)
            ->text('Organizing Committee ASIC 2027', 11, true);

        return $pdf->output();
    }

    private function rejection(array $c): string
    {
        $pdf = new SimplePdf;
        $this->header($pdf, 'LETTER OF REJECTION', $c);
        $pdf->text('To / Kepada Yth.', 11, true)
            ->text($c['name'])
            ->text(trim(($c['institution'] ?? '').' '.($c['country'] ? '('.$c['country'].')' : '')))
            ->space(8)
            ->text("Dear {$c['name']},")->space(4)
            ->text("Thank you for submitting your abstract to {$c['event_name']}. The abstract entitled:")->space(2)
            ->text('"'.$c['abstract_title'].'"', 11, true, 'left', 14)->space(2)
            ->text('was carefully reviewed by the Abstract Team. We regret to inform you that it has NOT been accepted for presentation.')
            ->space(4);
        if (! empty($c['review_notes'])) {
            $pdf->text('Reviewer notes:', 11, true)->text($c['review_notes'], 11, false, 'left', 14)->space(4);
        }
        $pdf->text('We appreciate your interest and hope to see your contribution at future events.')->space(24)
            ->text('Organizing Committee ASIC 2027', 11, true);

        return $pdf->output();
    }

    private function invoice(array $c): string
    {
        $pdf = new SimplePdf;
        $this->header($pdf, 'INVOICE', $c);
        $pdf->text('Ditagihkan kepada / Billed to', 11, true)
            ->text($c['name'])->text($c['institution'] ?? '')
            ->space(4)
            ->text('Kegiatan / Event : '.$c['event_name'])
            ->text('Kategori / Category : '.$c['registration_type'].' - '.$c['attendance_mode'].' - '.$c['participation_type'])
            ->text('Jatuh tempo / Due date : '.Carbon::parse($c['invoice_due'])->format('d F Y'), 11, true)
            ->space(8);

        $rows = [];
        foreach ($c['items'] as $item) {
            $rows[] = [$item['jenis_biaya'], (string) $item['jumlah'], self::money($item['harga_satuan']), self::money($item['subtotal'])];
        }
        $pdf->table(['Uraian / Item', 'Qty', 'Harga satuan', 'Subtotal'], $rows, [227, 40, 86, 86], ['left', 'right', 'right', 'right'])
            ->space(6)
            ->text('TOTAL TAGIHAN / TOTAL DUE : '.self::money($c['total']), 12, true, 'right')
            ->space(10);
        if (! empty($c['payment_qr_info'])) {
            $pdf->text('Informasi pembayaran (QR) / Payment info : '.$c['payment_qr_info']);
        }
        $pdf->text('Unggah bukti pembayaran melalui website sebelum tanggal jatuh tempo.');

        return $pdf->output();
    }

    private function receipt(array $c): string
    {
        $pdf = new SimplePdf;
        $this->header($pdf, 'RECEIPT / KUITANSI', $c);
        $pdf->text('Telah diterima dari / Received from : '.$c['name'])
            ->text('Instansi / Institution : '.($c['institution'] ?? '-'))
            ->space(4)
            ->text('Untuk pembayaran / For payment of invoice : '.$c['invoice_nomor'])
            ->text('Kegiatan / Event : '.$c['event_name'])
            ->space(6)
            ->text('Jumlah / Amount : '.self::money($c['amount']), 13, true)
            ->space(4)
            ->text('Tanggal verifikasi / Verified at : '.Carbon::parse($c['verified_at'])->format('d F Y H:i'))
            ->text('Diverifikasi oleh / Verified by : Bendahara ASIC 2027')
            ->space(24)
            ->text('Treasurer ASIC 2027', 11, true);

        return $pdf->output();
    }
}
