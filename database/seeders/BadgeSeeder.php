<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('theme.badges') as $badge) {
            Badge::query()->updateOrCreate(['code' => $badge['code']], $badge);
        }
    }
}
