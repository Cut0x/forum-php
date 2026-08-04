<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeAwarder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with('badges')->orderBy('name')->get();
        $badges = Badge::query()->orderBy('name')->get();

        return view('admin.users', compact('users', 'badges'));
    }

    public function updateRole(Request $request, User $user, BadgeAwarder $awarder): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_MEMBER, User::ROLE_MODERATOR, User::ROLE_ADMIN])],
        ]);

        $user->update(['role' => $data['role']]);
        $awarder->awardForRole($user, $data['role']);

        return back()->with('success', 'Rôle mis à jour.');
    }

    public function attachBadge(User $user, Badge $badge): RedirectResponse
    {
        $user->badges()->syncWithoutDetaching([$badge->id]);

        return back()->with('success', 'Badge ajouté.');
    }

    public function detachBadge(User $user, Badge $badge): RedirectResponse
    {
        $user->badges()->detach($badge->id);

        return back()->with('success', 'Badge retiré.');
    }
}
