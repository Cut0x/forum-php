<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Topic;
use App\Models\User;

class TopicPolicy
{
    public function create(User $user, Category $category): bool
    {
        return ! $user->isSuspended() && (! $category->is_readonly || $user->isStaff());
    }

    public function update(User $user, Topic $topic): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->id === $topic->user_id && ! $topic->isLocked() && ! $user->isSuspended();
    }

    public function delete(User $user, Topic $topic): bool
    {
        return $user->isStaff() || ($user->id === $topic->user_id && ! $user->isSuspended());
    }

    public function reply(User $user, Topic $topic): bool
    {
        if ($user->isSuspended()) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        return ! $topic->isLocked() && ! $topic->category->is_readonly && $topic->deleted_at === null;
    }

    public function moderate(User $user, Topic $topic): bool
    {
        return $user->isStaff();
    }

    public function vote(User $user, Topic $topic): bool
    {
        return ! $user->isSuspended() && $topic->deleted_at === null;
    }
}
