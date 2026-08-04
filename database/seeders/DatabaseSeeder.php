<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Contenu de démo optionnel : php artisan db:seed --class=DemoContentSeeder
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            CategorySeeder::class,
            BadgeSeeder::class,
            FooterSeeder::class,
        ]);
    }
}
