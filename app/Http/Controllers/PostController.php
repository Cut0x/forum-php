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
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
    public function store(StorePostRequest $request, Topic $topic): RedirectResponse|Response
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

        if ($request->ajax()) {
            $post->load('user.badges');

            return $this->fragment(view('components.forum.post', ['post' => $post]), 'Réponse publiée.');
        }

        return redirect()->route('topics.show', $topic)->with('success', 'Réponse publiée.')->withFragment('post-'.$post->id);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse|Response
    {
        $post->update([
            'content' => $request->validated('content'),
            'edited_at' => now(),
        ]);

        if ($request->ajax()) {
            $post->load('user.badges', 'votes');

            return $this->fragment(view('components.forum.post', ['post' => $post]), 'Message mis à jour.');
        }

        return back()->with('success', 'Message mis à jour.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse|Response
    {
        $this->authorize('delete', $post);

        $post->delete();

        if ($request->ajax()) {
            return $this->ajaxOk('Message supprimé.');
        }

        return back()->with('success', 'Message supprimé.');
    }
}
