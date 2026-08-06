<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Category;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\UserMentioned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForumTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_categories_and_topics(): void
    {
        $topic = Topic::factory()->for(Category::factory())->create();

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('topics.show', [$topic->category, $topic]))->assertOk()->assertSee($topic->title);
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
        $response->assertRedirect(route('topics.show', [$category, $topic]));
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

    public function test_member_can_reply_to_a_specific_post_and_the_parent_author_is_mentioned(): void
    {
        Notification::fake();

        $parentAuthor = User::factory()->create(['username' => 'helene_blanchard']);
        $replier = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create();
        $parentPost = Post::factory()->for($topic)->create(['user_id' => $parentAuthor->id]);

        $this->actingAs($replier)->post(route('posts.store', $topic), [
            'parent_id' => $parentPost->id,
            'content' => '@helene_blanchard Merci pour ta réponse !',
        ])->assertRedirect();

        $reply = Post::query()->where('parent_id', $parentPost->id)->first();
        $this->assertNotNull($reply);
        $this->assertSame($replier->id, $reply->user_id);

        Notification::assertSentTo($parentAuthor, UserMentioned::class);
    }

    public function test_ajax_reply_to_a_post_renders_the_fragment_without_error(): void
    {
        // Reproduit le flux réel du bouton "Répondre" inline (data-remote="append"), qui envoie
        // une requête AJAX — contrairement aux autres tests de ce fichier qui postent en direct
        // et empruntent la branche redirect(), jamais la branche fragment() qui rend la vue.
        $parentAuthor = User::factory()->create();
        $replier = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create();
        $parentPost = Post::factory()->for($topic)->create(['user_id' => $parentAuthor->id]);

        $response = $this->actingAs($replier)->post(route('posts.store', $topic), [
            'parent_id' => $parentPost->id,
            'content' => 'Réponse imbriquée via AJAX.',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertSee('Réponse imbriquée via AJAX.', false);
    }

    public function test_reply_cannot_be_attached_to_a_post_from_another_topic(): void
    {
        $user = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create();
        $otherTopicPost = Post::factory()->for(Topic::factory()->for(Category::factory()))->create();

        $this->actingAs($user)->post(route('posts.store', $topic), [
            'parent_id' => $otherTopicPost->id,
            'content' => 'Réponse invalide.',
        ])->assertSessionHasErrors('parent_id');
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

    public function test_badge_awarder_grants_a_posts_count_badge_automatically(): void
    {
        $badge = Badge::query()->create([
            'name' => 'Premier message',
            'code' => 'test_first_post',
            'icon' => 'test.png',
            'color' => '#000000',
            'rule_type' => Badge::RULE_POSTS_COUNT,
            'rule_value' => '1',
        ]);

        $user = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create();

        $this->actingAs($user)->post(route('posts.store', $topic), [
            'content' => 'Mon premier message.',
        ])->assertRedirect();

        $this->assertTrue($user->badges()->where('badges.id', $badge->id)->exists());
    }

    public function test_manual_badge_is_never_awarded_automatically(): void
    {
        $badge = Badge::query()->create([
            'name' => 'Fondateur test',
            'code' => 'test_founder',
            'icon' => 'test.png',
            'color' => '#000000',
            'rule_type' => Badge::RULE_MANUAL,
            'rule_value' => null,
        ]);

        $user = User::factory()->create();
        $topic = Topic::factory()->for(Category::factory())->create();

        $this->actingAs($user)->post(route('posts.store', $topic), [
            'content' => 'Un message.',
        ])->assertRedirect();

        $this->assertFalse($user->badges()->where('badges.id', $badge->id)->exists());
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
