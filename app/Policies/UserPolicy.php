<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function moderate(User $user, User $target): bool
    {
        if (! $user->isStaff()) {
            return false;
        }

        // Un modérateur ne peut pas sanctionner un admin ou un autre modérateur.
        if ($user->isModerator() && $target->isStaff()) {
            return false;
        }

        return $user->id !== $target->id;
    }

    public function manageRole(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->id !== $target->id;
    }
}
