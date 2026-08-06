<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ModerationAction;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TopicModerationController extends Controller
{
    public function lock(Request $request, Topic $topic): RedirectResponse|JsonResponse
    {
        $this->authorize('moderate', $topic);

        $topic->update(['locked_at' => now()]);
        ModerationAction::log($request->user(), 'lock', $topic);

        if ($request->ajax()) {
            return response()->json(['locked' => true]);
        }

        return back()->with('success', 'Sujet verrouillé.');
    }

    public function unlock(Request $request, Topic $topic): RedirectResponse|JsonResponse
    {
        $this->authorize('moderate', $topic);

        $topic->update(['locked_at' => null]);
        ModerationAction::log($request->user(), 'unlock', $topic);

        if ($request->ajax()) {
            return response()->json(['locked' => false]);
        }

        return back()->with('success', 'Sujet déverrouillé.');
    }

    public function pin(Request $request, Topic $topic): RedirectResponse|JsonResponse
    {
        $this->authorize('moderate', $topic);

        $topic->update(['pinned_at' => now()]);
        ModerationAction::log($request->user(), 'pin', $topic);

        if ($request->ajax()) {
            return response()->json(['pinned' => true]);
        }

        return back()->with('success', 'Sujet épinglé.');
    }

    public function unpin(Request $request, Topic $topic): RedirectResponse|JsonResponse
    {
        $this->authorize('moderate', $topic);

        $topic->update(['pinned_at' => null]);
        ModerationAction::log($request->user(), 'unpin', $topic);

        if ($request->ajax()) {
            return response()->json(['pinned' => false]);
        }

        return back()->with('success', 'Sujet désépinglé.');
    }

    public function move(Request $request, Topic $topic): RedirectResponse|JsonResponse
    {
        $this->authorize('moderate', $topic);

        $data = $request->validate(['category_id' => ['required', 'exists:categories,id']]);
        $destination = Category::query()->findOrFail($data['category_id']);

        $topic->update(['category_id' => $destination->id]);
        ModerationAction::log($request->user(), 'move', $topic, ['category' => $destination->name]);

        if ($request->ajax()) {
            return response()->json(['category' => $destination->name]);
        }

        return redirect()->route('topics.show', [$destination, $topic])->with('success', 'Sujet déplacé vers « '.$destination->name.' ».');
    }
}
