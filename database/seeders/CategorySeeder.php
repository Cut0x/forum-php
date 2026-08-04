<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Annonces', 'description' => 'Nouveautés et mises à jour.', 'sort_order' => 1, 'is_readonly' => true, 'is_pinned' => true],
            ['name' => 'Support', 'description' => 'Questions et aide technique.', 'sort_order' => 2, 'is_readonly' => false, 'is_pinned' => false],
            ['name' => 'Discussions', 'description' => 'Sujets libres.', 'sort_order' => 3, 'is_readonly' => false, 'is_pinned' => false],
        ];

        foreach ($categories as $category) {
            $existing = Category::query()->where('name', $category['name'])->first();
            $slug = $existing?->slug ?? Category::uniqueSlug($category['name']);

            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                $category + ['slug' => $slug]
            );
        }
    }
}
