<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Pemberitahuan pekerjaan baru untuk panitia (lonceng portal staf): bukti bayar masuk → Bendahara,
 * abstrak masuk/diganti → Tim Abstrak, permintaan kerja sama → Sekretariat.
 * Hanya di dalam situs (database); judul/isi diterjemahkan saat dibaca (asic-notifications.kinds.*).
 */
class StaffAlert extends Notification
{
    /** @param array<string, scalar|null> $params */
    public function __construct(
        public readonly string $kind,
        public readonly ?string $url,
        public readonly array $params = [],
        public readonly ?string $dedupe = null,
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
            'audience' => 'staff',
            'url' => $this->url,
            'params' => $this->params,
            'dedupe' => $this->dedupe,
        ];
    }
}
