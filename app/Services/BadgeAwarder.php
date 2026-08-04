<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;

class BadgeAwarder
{
    /**
     * Attribue à l'utilisateur les badges d'activité qu'il a désormais mérités.
     */
    public function awardFor(User $user): void
    {
        $postCount = $user->posts()->count();
        $topicCount = $user->topics()->count();

        $earnedCodes = collect(config('theme.badge_rules'))
            ->filter(fn (array $rule) => $postCount >= $rule['min_posts'] && $topicCount >= $rule['min_topics'])
            ->keys();

        if ($earnedCodes->isEmpty()) {
            return;
        }

        $badgeIds = Badge::query()->whereIn('code', $earnedCodes)->pluck('id', 'code');
        $user->badges()->syncWithoutDetaching($badgeIds->all());
    }

    /**
     * Attribue le badge lié au rôle (admin/moderator) lors d'un changement de rôle.
     */
    public function awardForRole(User $user, string $role): void
    {
        if (! in_array($role, ['admin', 'moderator'], true)) {
            return;
        }

        $badge = Badge::query()->where('code', $role === 'admin' ? 'admin' : 'moderator')->first();
        if ($badge) {
            $user->badges()->syncWithoutDetaching([$badge->id]);
        }
    }
}
