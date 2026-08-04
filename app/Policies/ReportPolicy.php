<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function create(User $user): bool
    {
        return ! $user->isSuspended();
    }

    public function moderate(User $user): bool
    {
        return $user->isStaff();
    }
}
