<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Models\Category;
use App\Models\Topic;
use App\Services\BadgeAwarder;
use App\Services\MentionNotifier;
use App\Services\PostThreadBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopicController extends Controller
{
    public function show(Category $category, Topic $topic): RedirectResponse|View
    {
        // Le sujet a été déplacé depuis (modération) : on ne casse pas le lien, on redirige
        // vers son URL canonique plutôt que de renvoyer une 404.
        if ($topic->category_id !== $category->id) {
            return redirect()->route('topics.show', [$topic->category, $topic], 301);
        }

        $topic->load(['category', 'user.badges']);
        $topic->loadSum('votes as score', 'value');
        $topic->load(['votes' => fn ($q) => $q->where('user_id', auth()->id() ?? 0)]);
        $topic->loadCount('posts');

        $flatPosts = $topic->posts()
            ->withTrashed()
            ->with(['user.badges', 'votes' => fn ($q) => $q->where('user_id', auth()->id() ?? 0)])
            ->withSum('votes as score', 'value')
            ->orderBy('created_at')
            ->get();

        $posts = app(PostThreadBuilder::class)->build($flatPosts);

        return view('topics.show', compact('topic', 'posts'));
    }

    public function store(StoreTopicRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $topic = Topic::query()->create([
            'category_id' => $category->id,
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => Topic::uniqueSlug($data['title']),
        ]);

        $post = $topic->posts()->create([
            'user_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        app(BadgeAwarder::class)->awardFor($request->user());
        app(MentionNotifier::class)->notify($post, $request->user());

        return redirect()->route('topics.show', [$category, $topic])->with('success', 'Sujet publié.');
    }

    public function update(UpdateTopicRequest $request, Topic $topic): RedirectResponse|JsonResponse
    {
        $topic->update([
            'title' => $request->validated('title'),
            'edited_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json(['title' => $topic->title]);
        }

        return back()->with('success', 'Sujet mis à jour.');
    }

    public function destroy(Topic $topic): RedirectResponse
    {
        $this->authorize('delete', $topic);

        $topic->delete();

        return redirect()->route('categories.show', $topic->category)->with('success', 'Sujet supprimé.');
    }
}
