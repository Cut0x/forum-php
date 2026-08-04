<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Models\Category;
use App\Models\Topic;
use App\Services\BadgeAwarder;
use App\Services\MentionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TopicController extends Controller
{
    public function show(Topic $topic): View
    {
        $topic->load(['category', 'user.badges']);

        $posts = $topic->posts()
            ->withTrashed()
            ->with(['user.badges', 'votes' => fn ($q) => $q->where('user_id', auth()->id())])
            ->withSum('votes as score', 'value')
            ->orderBy('created_at')
            ->paginate(30);

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

        return redirect()->route('topics.show', $topic)->with('success', 'Sujet publié.');
    }

    public function update(UpdateTopicRequest $request, Topic $topic): RedirectResponse
    {
        $topic->update([
            'title' => $request->validated('title'),
            'edited_at' => now(),
        ]);

        return back()->with('success', 'Sujet mis à jour.');
    }

    public function destroy(Topic $topic): RedirectResponse
    {
        $this->authorize('delete', $topic);

        $topic->delete();

        return redirect()->route('categories.show', $topic->category)->with('success', 'Sujet supprimé.');
    }
}
