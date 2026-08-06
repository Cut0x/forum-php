<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;

class BadgeAwarder
{
    /**
     * Attribue à l'utilisateur tous les badges à règle automatique (posts_count, topics_count,
     * account_age_days, role) qu'il a désormais mérités. Les badges "manual" ne sont jamais
     * attribués ici — uniquement depuis /admin/users. Un badge déjà obtenu n'est jamais retiré,
     * même si l'utilisateur ne remplit plus la condition (ex: changement de rôle).
     */
    public function awardFor(User $user): void
    {
        $postCount = $user->posts()->count();
        $topicCount = $user->topics()->count();
        $accountAgeDays = $user->created_at?->diffInDays(now()) ?? 0;

        $earnedIds = Badge::query()
            ->whereNotNull('rule_type')
            ->where('rule_type', '!=', Badge::RULE_MANUAL)
            ->get()
            ->filter(function (Badge $badge) use ($postCount, $topicCount, $accountAgeDays, $user) {
                return match ($badge->rule_type) {
                    Badge::RULE_POSTS_COUNT => $postCount >= (int) $badge->rule_value,
                    Badge::RULE_TOPICS_COUNT => $topicCount >= (int) $badge->rule_value,
                    Badge::RULE_ACCOUNT_AGE_DAYS => $accountAgeDays >= (int) $badge->rule_value,
                    Badge::RULE_ROLE => $user->role === $badge->rule_value,
                    default => false,
                };
            })
            ->pluck('id');

        if ($earnedIds->isNotEmpty()) {
            $user->badges()->syncWithoutDetaching($earnedIds->all());
        }
    }
}
