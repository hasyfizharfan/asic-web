<?php

namespace App\Notifications;

use App\Support\MailGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Email konfirmasi setelah peserta membuat akun (akun langsung aktif, tanpa tautan aktivasi). */
class WelcomeParticipant extends Notification implements ShouldQueue
{
    use Queueable;

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return MailGuard::allows($notifiable->email ?? null) ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = config('asic.event_short_name', '4th ASIC 2027');
        $name = trim((string) ($notifiable->participant?->full_name ?? ''));

        return (new MailMessage)
            ->subject(__('asic-notifications.welcome.subject', ['event' => $event]))
            ->greeting($name !== '' ? __('asic-notifications.mail.greeting', ['name' => $name]) : __('asic-notifications.mail.greeting_plain'))
            ->line(__('asic-notifications.welcome.created', ['event' => $event, 'email' => $notifiable->email]))
            ->line(__('asic-notifications.welcome.next'))
            ->action(__('asic-notifications.welcome.button'), route('auth.participant.login'))
            ->line(__('asic-notifications.welcome.not_you'))
            ->salutation(__('asic-notifications.mail.salutation', ['event' => $event]));
    }
}
