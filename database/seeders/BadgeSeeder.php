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
        $badges = [
            ['name' => 'Fondateur', 'code' => 'founder', 'icon' => 'founder.png', 'color' => '#4f8cff', 'rule_type' => Badge::RULE_MANUAL, 'rule_value' => null],
            ['name' => 'Admin', 'code' => 'admin', 'icon' => 'admin.png', 'color' => '#ff4d4f', 'rule_type' => Badge::RULE_ROLE, 'rule_value' => 'admin'],
            ['name' => 'Modérateur', 'code' => 'moderator', 'icon' => 'moderator.png', 'color' => '#7c5cff', 'rule_type' => Badge::RULE_ROLE, 'rule_value' => 'moderator'],
            ['name' => 'Contributeur', 'code' => 'contributor', 'icon' => 'contributor.png', 'color' => '#16a34a', 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '5'],
            ['name' => 'Premier message', 'code' => 'starter', 'icon' => 'starter.png', 'color' => '#4f8cff', 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '1'],
            ['name' => '10 messages', 'code' => 'writer', 'icon' => 'writer.png', 'color' => '#00d1b2', 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '10'],
            ['name' => '25 messages', 'code' => 'speaker', 'icon' => 'speaker.png', 'color' => '#ffb020', 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '25'],
            ['name' => '50 messages', 'code' => 'veteran', 'icon' => 'veteran.png', 'color' => '#7c5cff', 'rule_type' => Badge::RULE_POSTS_COUNT, 'rule_value' => '50'],
            ['name' => 'Premier sujet', 'code' => 'first_topic', 'icon' => 'first_topic.png', 'color' => '#ff4d4f', 'rule_type' => Badge::RULE_TOPICS_COUNT, 'rule_value' => '1'],
            ['name' => '10 sujets', 'code' => 'topics_10', 'icon' => 'topics_10.png', 'color' => '#ff4d4f', 'rule_type' => Badge::RULE_TOPICS_COUNT, 'rule_value' => '10'],
            ['name' => 'Donateur', 'code' => 'donator', 'icon' => 'donator.png', 'color' => '#00c2ff', 'rule_type' => Badge::RULE_MANUAL, 'rule_value' => null],
        ];

        foreach ($badges as $badge) {
            Badge::query()->updateOrCreate(['code' => $badge['code']], $badge);
        }
    }
}
