<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeAwarder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users', ['users' => $this->all(), 'badges' => $this->badges()]);
    }

    public function updateRole(Request $request, User $user, BadgeAwarder $awarder): RedirectResponse|Response
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_MEMBER, User::ROLE_MODERATOR, User::ROLE_ADMIN])],
        ]);

        $user->update(['role' => $data['role']]);
        $awarder->awardForRole($user, $data['role']);

        return $this->respond($request, $user, 'Rôle mis à jour.');
    }

    public function attachBadge(Request $request, User $user, Badge $badge): RedirectResponse|Response
    {
        $user->badges()->syncWithoutDetaching([$badge->id]);

        return $this->respond($request, $user, 'Badge ajouté.');
    }

    public function detachBadge(Request $request, User $user, Badge $badge): RedirectResponse|Response
    {
        $user->badges()->detach($badge->id);

        return $this->respond($request, $user, 'Badge retiré.');
    }

    protected function all()
    {
        return User::query()->with('badges')->orderBy('name')->get();
    }

    protected function badges()
    {
        return Badge::query()->orderBy('name')->get();
    }

    protected function respond(Request $request, User $user, string $message): RedirectResponse|Response
    {
        if ($request->ajax()) {
            $user->load('badges');

            return $this->fragment(view('admin.users._card', ['user' => $user, 'badges' => $this->badges()]), $message);
        }

        return back()->with('success', $message);
    }
}
