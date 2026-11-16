<?php

namespace App\Services;

use App\Models\DigitalSignature;
use App\Models\Role;
use App\Support\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

/**
 * Render dokumen (LoA, Surat Penolakan, Invoice, Receipt) mengikuti format template panitia
 * (folder resources/views/documents). Tanda tangan + stempel diambil dari modul Tanda Tangan Digital:
 * LoA & penolakan oleh Ketua (peran Admin), invoice & receipt oleh Bendahara.
 */
class DocumentRenderer
{
    private const VIEWS = [
        'LOA' => 'documents.loa',
        'Penolakan' => 'documents.rejection',
        'Invoice' => 'documents.invoice',
        'Receipt' => 'documents.receipt',
    ];

    private const SIGNER = [
        'LOA' => ['chair', Role::ROLE_ADMIN],
        'Penolakan' => ['chair', Role::ROLE_ADMIN],
        'Invoice' => ['treasurer', Role::ROLE_BENDAHARA],
        'Receipt' => ['treasurer', Role::ROLE_BENDAHARA],
    ];

    /**
     * @param  array<string, mixed>  $c
     */
    public function render(string $jenis, array $c): string
    {
        return Pdf::loadHTML($this->html($jenis, $c))->setPaper('a4')->output();
    }

    /**
     * HTML dokumen sebelum diubah ke PDF (dipakai juga untuk pratinjau/test).
     *
     * @param  array<string, mixed>  $c
     */
    public function html(string $jenis, array $c): string
    {
        $view = self::VIEWS[$jenis] ?? throw new InvalidArgumentException("Jenis dokumen '$jenis' tidak didukung.");
        [$signerKey, $role] = self::SIGNER[$jenis];

        return view($view, $c + [
            'letterhead' => config('asic.letterhead'),
            'logoLeft' => $this->publicImage(config('asic.letterhead.logo_left')),
            'logoRight' => $this->publicImage(config('asic.letterhead.logo_right')),
            'city' => config('asic.city', 'Padang'),
            'payment' => config('asic.payment'),
            'signer' => config("asic.signers.$signerKey") + $this->signatureImages($role),
        ])->render();
    }

    /** Format nominal seperti di template panitia: 1,000,000 (IDR) atau 1,000.00 (USD). */
    public static function money(mixed $value, ?string $currency = 'IDR'): string
    {
        return Currency::plain($value, $currency);
    }

    /** Terbilang bahasa Inggris untuk receipt ("Five million rupiah" / "... US dollars"). */
    public static function amountInWords(mixed $value, ?string $currency = 'IDR'): string
    {
        return Currency::words($value, $currency);
    }

    /**
     * @return array{ttd: ?string, stempel: ?string}
     */
    private function signatureImages(string $roleName): array
    {
        try {
            $sig = DigitalSignature::whereHas('role', fn ($q) => $q->where('nama_role', $roleName))
                ->orderByDesc('tanggal_upload')->first();
        } catch (Throwable) {
            $sig = null;
        }

        return [
            'ttd' => $this->privateSignatureImage($sig, 'file_ttd'),
            'stempel' => $this->privateSignatureImage($sig, 'file_stempel'),
        ];
    }

    private function privateSignatureImage(?DigitalSignature $signature, string $attribute): ?string
    {
        if (! $signature || ! ($path = $signature->privateFilePath($attribute))) {
            return null;
        }

        try {
            $disk = Storage::disk('local');

            return 'data:'.($disk->mimeType($path) ?: 'image/png').';base64,'.base64_encode($disk->get($path));
        } catch (Throwable) {
            return null;
        }
    }

    /** Gambar dari disk "public" sebagai data URI agar dompdf tidak perlu akses berkas/URL luar. */
    private function publicImage(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        try {
            $disk = Storage::disk('public');
            if (! $disk->exists($path)) {
                return null;
            }

            return 'data:'.($disk->mimeType($path) ?: 'image/png').';base64,'.base64_encode($disk->get($path));
        } catch (Throwable) {
            return null;
        }
    }
}
