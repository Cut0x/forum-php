<?php

namespace App\Notifications;

use App\Models\Warning;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountWarned extends Notification
{
    use Queueable;

    public function __construct(public Warning $warning) {}

    public function via(object $notifiable): array
    {
        return $notifiable->notifications_enabled ? ['database', 'mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Avertissement de la modération')
            ->greeting('Bonjour '.$notifiable->displayName().',')
            ->line('Vous avez reçu un avertissement de la part de la modération.')
            ->line('Raison : '.$this->warning->reason)
            ->line('Merci de respecter les règles de la communauté.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Vous avez reçu un avertissement : '.$this->warning->reason,
            'warning_id' => $this->warning->id,
        ];
    }
}
