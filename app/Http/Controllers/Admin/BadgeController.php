<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::query()->orderBy('name')->get();

        return view('admin.badges', compact('badges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:badges,code'],
            'icon' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        Badge::query()->create($data);

        return back()->with('success', 'Badge créé.');
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:badges,code,'.$badge->id],
            'icon' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        $badge->update($data);

        return back()->with('success', 'Badge mis à jour.');
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $badge->delete();

        return back()->with('success', 'Badge supprimé.');
    }
}
