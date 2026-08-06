<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\TopicVote;
use App\Notifications\TopicVoted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TopicVoteController extends Controller
{
    public function store(Request $request, Topic $topic): RedirectResponse|JsonResponse
    {
        $this->authorize('vote', $topic);

        $value = (int) $request->validate(['value' => ['required', 'integer', 'in:1,-1']])['value'];
        $user = $request->user();

        $existing = TopicVote::query()->where('topic_id', $topic->id)->where('user_id', $user->id)->first();
        $userVote = $value;

        if (! $existing) {
            TopicVote::query()->create(['topic_id' => $topic->id, 'user_id' => $user->id, 'value' => $value]);
            $this->notifyOwner($topic, $user, $value);
        } elseif ($existing->value === $value) {
            $existing->delete();
            $userVote = null;
        } else {
            $existing->update(['value' => $value]);
            $this->notifyOwner($topic, $user, $value);
        }

        if ($request->ajax()) {
            return response()->json([
                'score' => (int) $topic->votes()->sum('value'),
                'value' => $userVote,
            ]);
        }

        return back();
    }

    protected function notifyOwner(Topic $topic, $actor, int $value): void
    {
        if ($topic->user_id !== $actor->id) {
            $topic->user->notify(new TopicVoted($topic, $actor, $value));
        }
    }
}
