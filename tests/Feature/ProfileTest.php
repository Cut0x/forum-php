<?php

namespace Tests\Feature;

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
