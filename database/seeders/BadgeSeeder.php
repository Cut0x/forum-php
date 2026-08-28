<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Badges par défaut du forum, avec leur règle d'attribution automatique
     * (voir App\Models\Badge::ruleTypes()). "manual" = jamais attribué automatiquement,
     * uniquement depuis /admin/users.
     */
    public function run(): void
    {
        // "priority" détermine le badge mis en avant seul à côté du pseudo (ex : messages) quand
        // un utilisateur en a plusieurs : le plus élevé l'emporte. Rôles > ancienneté/dons > mérite.
        $badges = [
            ['name' => 'Admin', 'code' => 'admin', 'icon' => 'admin.png', 'color' => '#ff4d4f', 'priority' => 100, 'rule_type' => Badge::RULE_ROLE, 'rule_value' => 'admin'],
            ['name' => 'Modérateur', 'code' => 'moderator', 'icon' => 'moderator.png', 'color' => '#7c5cff', 'priority' => 90, 'rule_type' => Badge::RULE_ROLE, 'rule_value' => 'moderator'],
            ['name' => 'Fondateur', 'code' => 'founder', 'icon' => 'founder.png', 'color' => '#4f8cff', 'priority' => 80, 'rule_type' => Badge::RULE_MANUAL, 'rule_value' => null],
            ['name' => 'Donateur', 'code' => 'donator', 'icon' => 'donator.png', 'color' => '#00c2ff', 'priority' => 70, 'rule_type' => Badge::RULE_MANUAL, 'rule_value' => null],
            ['name' => '50 messages', 'code' => 'veteran', 'icon' => 'veteran.png', 'color' => '#7c5cff', 'priority' => 50, 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '50'],
            ['name' => '25 messages', 'code' => 'speaker', 'icon' => 'speaker.png', 'color' => '#ffb020', 'priority' => 40, 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '25'],
            ['name' => '10 sujets', 'code' => 'topics_10', 'icon' => 'topics_10.png', 'color' => '#ff4d4f', 'priority' => 35, 'rule_type' => Badge::RULE_TOPICS_COUNT, 'rule_value' => '10'],
            ['name' => '10 messages', 'code' => 'writer', 'icon' => 'writer.png', 'color' => '#00d1b2', 'priority' => 30, 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '10'],
            ['name' => 'Contributeur', 'code' => 'contributor', 'icon' => 'contributor.png', 'color' => '#16a34a', 'priority' => 20, 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '5'],
            ['name' => 'Premier sujet', 'code' => 'first_topic', 'icon' => 'first_topic.png', 'color' => '#ff4d4f', 'priority' => 15, 'rule_type' => Badge::RULE_TOPICS_COUNT, 'rule_value' => '1'],
            ['name' => 'Premier message', 'code' => 'starter', 'icon' => 'starter.png', 'color' => '#4f8cff', 'priority' => 10, 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '1'],
        ];

        foreach ($badges as $badge) {
            Badge::query()->updateOrCreate(['code' => $badge['code']], $badge);
        }
    }
}
