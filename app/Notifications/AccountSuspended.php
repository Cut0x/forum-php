<?php

namespace App\Notifications;

use App\Models\Suspension;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountSuspended extends Notification
{
    use Queueable;

    public function __construct(public Suspension $suspension) {}

    public function via(object $notifiable): array
    {
        return $notifiable->notifications_enabled ? ['database', 'mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Compte suspendu')
            ->greeting('Bonjour '.$notifiable->displayName().',')
            ->line('Votre compte a été suspendu jusqu\'au '.$this->suspension->ends_at->format('d/m/Y H:i').'.')
            ->line('Raison : '.$this->suspension->reason);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Votre compte est suspendu jusqu\'au '.$this->suspension->ends_at->format('d/m/Y H:i').'.',
            'suspension_id' => $this->suspension->id,
        ];
    }
}
