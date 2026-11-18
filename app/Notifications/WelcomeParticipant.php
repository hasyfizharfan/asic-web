<?php

namespace App\Notifications;

use App\Support\MailGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Email konfirmasi pendaftaran akun peserta. Bila akun belum terverifikasi, berisi tombol
 * verifikasi (tautan bertanda tangan, berlaku 48 jam); bila sudah, tombol masuk portal.
 * Dikirim langsung (notifyNow) agar peserta tidak menunggu antrean.
 */
class WelcomeParticipant extends Notification
{
    use Queueable;

    public const LINK_HOURS = 48;

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return MailGuard::allows($notifiable->email ?? null) ? ['mail'] : [];
    }

    public static function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addHours(self::LINK_HOURS), [
            'id' => $notifiable->getKey(), 'hash' => sha1(strtolower((string) $notifiable->email)),
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = config('asic.event_short_name', '4th ASIC 2027');
        $name = trim((string) ($notifiable->participant?->full_name ?? ''));
        $verify = $notifiable->email_verified_at === null;

        $mail = (new MailMessage)
            ->subject(__($verify ? 'asic-notifications.welcome.subject_verify' : 'asic-notifications.welcome.subject', ['event' => $event]))
            ->greeting($name !== '' ? __('asic-notifications.mail.greeting', ['name' => $name]) : __('asic-notifications.mail.greeting_plain'))
            ->line(__($verify ? 'asic-notifications.welcome.created_verify' : 'asic-notifications.welcome.created', ['event' => $event, 'email' => $notifiable->email]));

        if ($verify) {
            $mail->action(__('asic-notifications.welcome.verify_button'), self::verificationUrl($notifiable))
                ->line(__('asic-notifications.welcome.verify_expiry', ['hours' => self::LINK_HOURS]))
                ->line(__('asic-notifications.welcome.next'));
        } else {
            $mail->line(__('asic-notifications.welcome.next'))
                ->action(__('asic-notifications.welcome.button'), route('auth.participant.login'));
        }

        return $mail->line(__('asic-notifications.welcome.not_you'))
            ->salutation(__('asic-notifications.mail.salutation', ['event' => $event]));
    }
}
