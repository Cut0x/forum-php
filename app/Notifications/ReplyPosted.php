<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReplyPosted extends Notification
{
    use Queueable;

    public function __construct(public Post $post, public User $actor) {}

    public function via(object $notifiable): array
    {
        return $notifiable->notifications_enabled ? ['database', 'mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle réponse sur votre sujet')
            ->greeting('Bonjour '.$notifiable->displayName().',')
            ->line($this->actor->displayName().' a répondu à votre sujet « '.$this->post->topic->title.' ».')
            ->action('Voir la réponse', route('topics.show', $this->post->topic).'#post-'.$this->post->id);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->actor->displayName().' a répondu à votre sujet.',
            'topic_id' => $this->post->topic_id,
            'topic_slug' => $this->post->topic->slug,
            'post_id' => $this->post->id,
            'actor_id' => $this->actor->id,
            'actor_username' => $this->actor->username,
            'actor_name' => $this->actor->displayName(),
        ];
    }
}
