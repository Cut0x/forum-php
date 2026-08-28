<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('profile.show', $user));

        $response->assertOk();
    }

    public function test_profile_page_does_not_crash_when_a_posted_topic_has_been_deleted(): void
    {
        $author = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create();
        Post::factory()->for($topic)->create(['user_id' => $author->id]);

        $topic->delete();

        $response = $this->get(route('profile.show', $author));

        $response->assertOk();
    }

    public function test_edit_profile_page_requires_authentication(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Ancien Nom']);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nouveau Nom',
                'bio' => 'Une bio de test.',
            ]);

        $user->refresh();

        $response->assertSessionHasNoErrors()->assertRedirect(route('profile.show', $user));
        $this->assertSame('Nouveau Nom', $user->name);
        $this->assertSame('Une bio de test.', $user->bio);
    }
}
