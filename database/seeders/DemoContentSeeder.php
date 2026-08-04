<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    /**
     * Contenu de démonstration : quelques membres, sujets et réponses.
     * À lancer uniquement en environnement local (php artisan db:seed --class=DemoContentSeeder).
     */
    public function run(): void
    {
        $members = User::factory()->count(6)->create();

        Category::query()->get()->each(function (Category $category) use ($members) {
            Topic::factory()
                ->count(random_int(2, 4))
                ->for($category)
                ->create(['user_id' => fn () => $members->random()->id])
                ->each(function (Topic $topic) use ($members) {
                    Post::factory()->create([
                        'topic_id' => $topic->id,
                        'user_id' => $topic->user_id,
                    ]);

                    Post::factory()
                        ->count(random_int(0, 5))
                        ->create([
                            'topic_id' => $topic->id,
                            'user_id' => fn () => $members->random()->id,
                        ]);
                });
        });
    }
}
