<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\ModerationAction;
use App\Models\Suspension;
use App\Models\User;
use App\Models\Warning;
use App\Notifications\AccountSuspended;
use App\Notifications\AccountWarned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UserModerationController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($query !== '', fn ($q) => $q->where('name', 'like', "%$query%")->orWhere('username', 'like', "%$query%"))
            ->withCount(['warnings', 'suspensions'])
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('moderation.users.index', compact('users', 'query'));
    }

    public function warn(Request $request, User $user): RedirectResponse|Response
    {
        $this->authorize('moderate', $user);

        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $warning = Warning::query()->create([
            'user_id' => $user->id,
            'moderator_id' => $request->user()->id,
            'reason' => $data['reason'],
        ]);

        $user->notify(new AccountWarned($warning));
        ModerationAction::log($request->user(), 'warn', $user, ['reason' => $data['reason']]);

        return $this->respond($request, $user, 'Avertissement envoyé.');
    }

    public function suspend(Request $request, User $user): RedirectResponse|Response
    {
        $this->authorize('moderate', $user);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $endsAt = now()->addDays((int) $data['days']);

        $suspension = Suspension::query()->create([
            'user_id' => $user->id,
            'moderator_id' => $request->user()->id,
            'reason' => $data['reason'],
            'starts_at' => now(),
            'ends_at' => $endsAt,
        ]);

        $user->update(['suspended_until' => $endsAt]);
        $user->notify(new AccountSuspended($suspension));
        ModerationAction::log($request->user(), 'suspend', $user, ['reason' => $data['reason'], 'ends_at' => $endsAt->toDateTimeString()]);

        return $this->respond($request, $user, 'Utilisateur suspendu jusqu\'au '.$endsAt->format('d/m/Y').'.');
    }

    public function unsuspend(Request $request, User $user): RedirectResponse|Response
    {
        $this->authorize('moderate', $user);

        $user->update(['suspended_until' => null]);
        $user->suspensions()->whereNull('lifted_at')->update(['lifted_at' => now()]);
        ModerationAction::log($request->user(), 'unsuspend', $user);

        return $this->respond($request, $user, 'Suspension levée.');
    }

    protected function respond(Request $request, User $user, string $message): RedirectResponse|Response
    {
        if ($request->ajax()) {
            $user->loadCount(['warnings', 'suspensions']);

            return $this->fragment(view('moderation.users._card', ['user' => $user]), $message);
        }

        return back()->with('success', $message);
    }
}
