<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Notifications\UserMentioned;
use App\Support\Username;

class MentionNotifier
{
    /**
     * Notifie les utilisateurs mentionnés ("@pseudo") dans le contenu d'un message.
     */
    public function notify(Post $post, User $author): void
    {
        preg_match_all('/@([a-zA-Z0-9_]{3,30})/', $post->content, $matches);
        $usernames = collect($matches[1] ?? [])
            ->map(fn (string $name) => Username::normalize($name))
            ->filter(fn (string $name) => strlen($name) >= 3)
            ->unique()
            ->values();

        if ($usernames->isEmpty()) {
            return;
        }

        $mentioned = User::query()
            ->whereIn('username', $usernames)
            ->where('id', '!=', $author->id)
            ->get();

        foreach ($mentioned as $user) {
            $user->notify(new UserMentioned($post, $author));
        }
    }
}
