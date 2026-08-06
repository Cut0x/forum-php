<?php

namespace App\Notifications;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TopicVoted extends Notification
{
    use Queueable;

    public function __construct(public Topic $topic, public User $actor, public int $value) {}

    public function via(object $notifiable): array
    {
        return $notifiable->notifications_enabled ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->value === 1 ? 'positif' : 'négatif';

        return [
            'message' => $this->actor->displayName().' a laissé un vote '.$label.' sur votre sujet.',
            'topic_id' => $this->topic->id,
            'topic_slug' => $this->topic->slug,
            'actor_id' => $this->actor->id,
            'actor_username' => $this->actor->username,
            'actor_name' => $this->actor->displayName(),
        ];
    }
}
