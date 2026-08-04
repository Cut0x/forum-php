<?php

namespace Database\Seeders;

use App\Models\FooterCategory;
use App\Models\FooterLink;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    public function run(): void
    {
        if (FooterCategory::query()->exists()) {
            return;
        }

        $useful = FooterCategory::query()->create(['name' => 'Utiles', 'sort_order' => 1]);
        $resources = FooterCategory::query()->create(['name' => 'Ressources', 'sort_order' => 2]);

        FooterLink::query()->insert([
            ['footer_category_id' => $useful->id, 'label' => 'Accueil', 'url' => '/', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['footer_category_id' => $useful->id, 'label' => 'Contact', 'url' => '#', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['footer_category_id' => $resources->id, 'label' => 'Documentation', 'url' => '#', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['footer_category_id' => $resources->id, 'label' => 'GitHub', 'url' => 'https://github.com/', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
