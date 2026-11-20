<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Judul dan isi diterjemahkan saat dibaca, mengikuti locale aktif.
 *
 * @mixin DatabaseNotification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = (array) $this->data;
        $kind = (string) ($data['kind'] ?? '');
        $replace = ['id' => $data['id_registration'] ?? '-'] + array_map(fn ($v) => (string) ($v ?? '-'), (array) ($data['params'] ?? []));
        $known = trans()->has('asic-notifications.kinds.'.$kind);

        return [
            'id' => $this->id,
            'kind' => $kind,
            'title' => $known ? __('asic-notifications.kinds.'.$kind.'.title', $replace) : $kind,
            'body' => $known ? __('asic-notifications.kinds.'.$kind.'.body', $replace) : '',
            'url' => $data['url'] ?? null,
            'id_registration' => $data['id_registration'] ?? null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
