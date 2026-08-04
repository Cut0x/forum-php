<?php

namespace App\Http\Controllers;

use App\Notifications\PostVoted;
use App\Notifications\ReplyPosted;
use App\Notifications\UserMentioned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        return view('notifications.index', $this->panelData($request));
    }

    public function readAll(Request $request): RedirectResponse|Response
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->ajax()) {
            return $this->fragment(view('notifications._panel', $this->panelData($request)), 'Notifications marquées comme lues.')
                ->header('X-Unread-Count', (string) $request->user()->unreadNotifications()->count());
        }

        return back()->with('success', 'Notifications marquées comme lues.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse|Response
    {
        $request->user()->notifications()->where('id', $notification)->delete();

        if ($request->ajax()) {
            return $this->fragment(view('notifications._panel', $this->panelData($request)), 'Notification supprimée.')
                ->header('X-Unread-Count', (string) $request->user()->unreadNotifications()->count());
        }

        return back()->with('success', 'Notification supprimée.');
    }

    public function destroyAll(Request $request): RedirectResponse|Response
    {
        $type = $request->query('type', 'all');
        $query = $request->user()->notifications();
        if (isset(self::TYPES[$type])) {
            $query->where('type', self::TYPES[$type]);
        }
        $query->delete();

        if ($request->ajax()) {
            return $this->fragment(view('notifications._panel', $this->panelData($request)), 'Notifications supprimées.')
                ->header('X-Unread-Count', (string) $request->user()->unreadNotifications()->count());
        }

        return back()->with('success', 'Notifications supprimées.');
    }

    protected function panelData(Request $request): array
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

        return compact('notifications', 'type', 'counts');
    }
}
