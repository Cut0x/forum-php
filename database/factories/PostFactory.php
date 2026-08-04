<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'topic_id' => Topic::factory(),
            'user_id' => User::factory(),
            'content' => implode("\n\n", fake()->paragraphs(fake()->numberBetween(1, 3))),
        ];
    }
}
