<?php

namespace App\Notifications;

use App\Models\IssuedDocument;
use App\Support\MailGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * KF-30: notifikasi perubahan status, di dalam situs (database) dan ke email peserta.
 * Yang disimpan hanya jenis, id_registration, tautan, parameter, dan kunci dedupe;
 * judul dan isi diterjemahkan saat ditampilkan (lang/{id,en}/asic-notifications.php).
 *
 * Email: dikirim lewat antrean (QUEUE_CONNECTION=database + schedule queue:work) supaya
 * aksi panitia tidak menunggu server email; notifikasi di situs tetap langsung (sync).
 * Dokumen resmi (LoA, invoice, receipt, surat penolakan, sertifikat) ikut dilampirkan (PDF).
 */
class StatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public const KINDS = [
        'loa_issued', 'invoice_issued', 'receipt_issued', 'rejection_issued', 'certificate_issued',
        'invoice_extended', 'invoice_expired',
        'payment_valid', 'payment_invalid',
        'abstract_accepted', 'abstract_rejected',
        'reregistered',
    ];

    /**
     * Tidak dikirim ke email: keputusan abstrak sudah diwakili email LoA/surat penolakan
     * (berisi lampiran), dan registrasi ulang terjadi saat peserta berada di meja panitia.
     */
    public const NO_MAIL = ['abstract_accepted', 'abstract_rejected', 'reregistered'];

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
        $channels = ['database'];
        if (MailGuard::allows($notifiable->email ?? null) && ! in_array($this->kind, self::NO_MAIL, true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /** Notifikasi di situs langsung tersimpan; hanya email yang lewat antrean. */
    public function viaConnections(): array
    {
        return ['database' => 'sync'];
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

    public function toMail(object $notifiable): MailMessage
    {
        $replace = ['id' => $this->idRegistration ?? '-'] + array_map(fn ($v) => (string) ($v ?? '-'), $this->params);
        $event = config('asic.event_short_name', '4th ASIC 2027');
        $name = trim((string) ($notifiable->participant?->full_name ?? ''));
        $document = $this->document();

        $mail = (new MailMessage)
            ->subject(__('asic-notifications.kinds.'.$this->kind.'.title', $replace).' - '.$event)
            ->greeting($name !== '' ? __('asic-notifications.mail.greeting', ['name' => $name]) : __('asic-notifications.mail.greeting_plain'))
            ->line(__('asic-notifications.kinds.'.$this->kind.'.body', $replace));
        if ($document) {
            $mail->line(__('asic-notifications.mail.attached'));
        }
        if ($this->url) {
            $mail->action(__('asic-notifications.mail.open'), url($this->url));
        }
        $mail->line(__('asic-notifications.mail.footer'))
            ->salutation(__('asic-notifications.mail.salutation', ['event' => $event]));

        if ($document) {
            try {
                $disk = config('asic.documents_disk', 'local');
                if (Storage::disk($disk)->exists($document->file_document)) {
                    $mail->attachData(Storage::disk($disk)->get($document->file_document),
                        str_replace(['/', ' '], '-', $document->jenis_dokumen.'-'.$document->nomor_dokumen).'.pdf', ['mime' => 'application/pdf']);
                }
            } catch (Throwable) {
                // Lampiran gagal dibaca: email tetap terkirim dengan tautan ke halaman Dokumen.
            }
        }

        return $mail;
    }

    /** Dokumen resmi yang memicu notifikasi ini (kunci dedupe "document:{id}"). */
    private function document(): ?IssuedDocument
    {
        if (! str_starts_with($this->dedupe, 'document:')) {
            return null;
        }

        return IssuedDocument::find((int) substr($this->dedupe, 9));
    }
}
