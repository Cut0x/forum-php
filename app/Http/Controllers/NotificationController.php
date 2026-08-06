<?php

namespace App\Http\Controllers;

use App\Notifications\PostVoted;
use App\Notifications\ReplyPosted;
use App\Notifications\TopicVoted;
use App\Notifications\UserMentioned;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class NotificationController extends Controller
{
    protected const TYPES = [
        'reply' => ReplyPosted::class,
        'mention' => UserMentioned::class,
        'vote' => [PostVoted::class, TopicVoted::class],
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
            $this->applyTypeFilter($query, self::TYPES[$type]);
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
            $this->applyTypeFilter($query, self::TYPES[$type]);
        }

        $notifications = $query->paginate(20)->withQueryString();

        $counts = collect(self::TYPES)->mapWithKeys(fn ($classes, $key) => [
            $key => $this->applyTypeFilter($user->unreadNotifications(), $classes)->count(),
        ]);

        return compact('notifications', 'type', 'counts');
    }

    protected function applyTypeFilter(MorphMany $query, string|array $classes): MorphMany
    {
        return is_array($classes) ? $query->whereIn('type', $classes) : $query->where('type', $classes);
    }
}
