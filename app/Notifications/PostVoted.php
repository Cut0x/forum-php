<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostVoted extends Notification
{
    use Queueable;

    public function __construct(public Post $post, public User $actor, public int $value) {}

    public function via(object $notifiable): array
    {
        return $notifiable->notifications_enabled ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->value === 1 ? 'positif' : 'négatif';

        return [
            'message' => $this->actor->displayName().' a laissé un vote '.$label.' sur votre message.',
            'topic_id' => $this->post->topic_id,
            'topic_slug' => $this->post->topic->slug,
            'post_id' => $this->post->id,
            'actor_id' => $this->actor->id,
            'actor_username' => $this->actor->username,
            'actor_name' => $this->actor->displayName(),
        ];
    }
}
