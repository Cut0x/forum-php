<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_categories_and_topics(): void
    {
        $topic = Topic::factory()->for(Category::factory())->create();

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('topics.show', $topic))->assertOk()->assertSee($topic->title);
    }

    public function test_member_can_create_a_topic(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['is_readonly' => false]);

        $response = $this->actingAs($user)->post(route('topics.store', $category), [
            'title' => 'Mon premier sujet',
            'content' => 'Contenu du sujet.',
        ]);

        $topic = Topic::query()->where('title', 'Mon premier sujet')->first();
        $response->assertRedirect(route('topics.show', $topic));
        $this->assertDatabaseHas('posts', ['topic_id' => $topic->id, 'user_id' => $user->id]);
    }

    public function test_member_cannot_post_in_a_readonly_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['is_readonly' => true]);

        $response = $this->actingAs($user)->post(route('topics.store', $category), [
            'title' => 'Interdit',
            'content' => 'Contenu.',
        ]);

        $response->assertForbidden();
    }

    public function test_member_can_vote_on_a_post(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $post = Post::factory()->for(Topic::factory()->for(Category::factory()))->create(['user_id' => $author->id]);

        $this->actingAs($voter)->post(route('posts.vote', $post), ['value' => 1])->assertRedirect();

        $this->assertDatabaseHas('post_votes', ['post_id' => $post->id, 'user_id' => $voter->id, 'value' => 1]);
    }

    public function test_member_can_vote_on_a_topic(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create(['user_id' => $author->id]);

        $this->actingAs($voter)->post(route('topics.vote', $topic), ['value' => 1])->assertRedirect();

        $this->assertDatabaseHas('topic_votes', ['topic_id' => $topic->id, 'user_id' => $voter->id, 'value' => 1]);
    }

    public function test_member_can_report_a_topic(): void
    {
        $reporter = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create();

        $this->actingAs($reporter)->post(route('topics.reports.store', $topic), [
            'reason' => 'spam',
        ])->assertRedirect();

        $this->assertDatabaseHas('reports', ['reportable_id' => $topic->id, 'reporter_id' => $reporter->id]);
    }

    public function test_member_cannot_access_admin_or_moderation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/moderation')->assertForbidden();
    }

    public function test_moderator_can_lock_a_topic_but_not_access_admin(): void
    {
        $moderator = User::factory()->moderator()->create();
        $topic = Topic::factory()->for(Category::factory())->create();

        $this->actingAs($moderator)->patch(route('moderation.topics.lock', $topic))->assertRedirect();
        $this->assertNotNull($topic->fresh()->locked_at);

        $this->actingAs($moderator)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_update_site_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'site_title' => 'Mon Forum',
            'site_description' => 'Une description.',
            'footer_text' => 'Mon Forum',
            'footer_link' => null,
            'stripe_url' => null,
        ]);

        $response->assertRedirect();
        $this->assertSame('Mon Forum', \App\Support\Settings::get('site_title'));
    }

    public function test_suspended_user_cannot_reply(): void
    {
        $user = User::factory()->create(['suspended_until' => now()->addDay()]);
        $topic = Topic::factory()->for(Category::factory())->create();

        $response = $this->actingAs($user)->post(route('posts.store', $topic), [
            'content' => 'Je tente de répondre.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('posts', ['topic_id' => $topic->id, 'user_id' => $user->id]);
    }
}
