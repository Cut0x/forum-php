<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id && ! $user->isSuspended() && $post->deleted_at === null;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isStaff() || ($user->id === $post->user_id && ! $user->isSuspended());
    }

    public function vote(User $user, Post $post): bool
    {
        return ! $user->isSuspended() && $post->deleted_at === null;
    }
}
