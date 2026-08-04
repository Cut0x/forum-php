<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostVote;
use App\Notifications\PostVoted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostVoteController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('vote', $post);

        $value = (int) $request->validate(['value' => ['required', 'integer', 'in:1,-1']])['value'];
        $user = $request->user();

        $existing = PostVote::query()->where('post_id', $post->id)->where('user_id', $user->id)->first();

        if (! $existing) {
            PostVote::query()->create(['post_id' => $post->id, 'user_id' => $user->id, 'value' => $value]);
            $this->notifyOwner($post, $user, $value);
        } elseif ($existing->value === $value) {
            $existing->delete();
        } else {
            $existing->update(['value' => $value]);
            $this->notifyOwner($post, $user, $value);
        }

        return back();
    }

    protected function notifyOwner(Post $post, $actor, int $value): void
    {
        if ($post->user_id !== $actor->id) {
            $post->user->notify(new PostVoted($post, $actor, $value));
        }
    }
}
