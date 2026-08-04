<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\Topic;
use App\Notifications\ReplyPosted;
use App\Services\BadgeAwarder;
use App\Services\MentionNotifier;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    public function store(StorePostRequest $request, Topic $topic): RedirectResponse
    {
        $post = $topic->posts()->create([
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
        ]);

        app(BadgeAwarder::class)->awardFor($request->user());
        app(MentionNotifier::class)->notify($post, $request->user());

        if ($topic->user_id !== $request->user()->id) {
            $topic->user->notify(new ReplyPosted($post, $request->user()));
        }

        return redirect()->route('topics.show', $topic)->with('success', 'Réponse publiée.')->withFragment('post-'.$post->id);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update([
            'content' => $request->validated('content'),
            'edited_at' => now(),
        ]);

        return back()->with('success', 'Message mis à jour.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return back()->with('success', 'Message supprimé.');
    }
}
