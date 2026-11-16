<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * KF-30: notifikasi perubahan status di dalam situs (channel database saja).
 * Yang disimpan hanya jenis, id_registration, tautan, parameter, dan kunci dedupe;
 * judul dan isi diterjemahkan saat ditampilkan (lang/{id,en}/asic-notifications.php)
 * sehingga mengikuti bahasa pengguna.
 */
class StatusChanged extends Notification
{
    use Queueable;

    public const KINDS = [
        'loa_issued', 'invoice_issued', 'receipt_issued', 'rejection_issued', 'certificate_issued',
        'invoice_extended', 'invoice_expired',
        'payment_valid', 'payment_invalid',
        'abstract_accepted', 'abstract_rejected',
        'reregistered',
    ];

    /** @param array<string, scalar|null> $params */
    public function __construct(
        public readonly string $kind,
        public readonly ?int $idRegistration,
        public readonly ?string $url,
        public readonly array $params,
        public readonly string $dedupe,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'id_registration' => $this->idRegistration,
            'url' => $this->url,
            'params' => $this->params,
            'dedupe' => $this->dedupe,
        ];
    }
}
