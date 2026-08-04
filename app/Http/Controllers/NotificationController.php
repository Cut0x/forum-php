<?php

namespace App\Http\Controllers;

use App\Notifications\PostVoted;
use App\Notifications\ReplyPosted;
use App\Notifications\UserMentioned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    protected const TYPES = [
        'reply' => ReplyPosted::class,
        'mention' => UserMentioned::class,
        'vote' => PostVoted::class,
    ];

    public function index(Request $request): View
    {
        $type = $request->query('type', 'all');
        $user = $request->user();

        $query = $user->notifications();
        if (isset(self::TYPES[$type])) {
            $query->where('type', self::TYPES[$type]);
        }

        $notifications = $query->paginate(20)->withQueryString();

        $counts = collect(self::TYPES)->mapWithKeys(fn ($class, $key) => [
            $key => $user->unreadNotifications()->where('type', $class)->count(),
        ]);

        return view('notifications.index', compact('notifications', 'type', 'counts'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Notifications marquées comme lues.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->where('id', $notification)->delete();

        return back()->with('success', 'Notification supprimée.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return back()->with('success', 'Notifications supprimées.');
    }
}
