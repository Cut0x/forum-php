<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        return view('admin.badges', ['badges' => $this->all()]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:badges,code'],
            'icon' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        Badge::query()->create($data);

        return $this->respond($request, 'Badge créé.');
    }

    public function update(Request $request, Badge $badge): RedirectResponse|Response
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:badges,code,'.$badge->id],
            'icon' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        $badge->update($data);

        return $this->respond($request, 'Badge mis à jour.');
    }

    public function destroy(Request $request, Badge $badge): RedirectResponse|Response
    {
        $badge->delete();

        return $this->respond($request, 'Badge supprimé.');
    }

    protected function all()
    {
        return Badge::query()->orderBy('name')->get();
    }

    protected function respond(Request $request, string $message): RedirectResponse|Response
    {
        if ($request->ajax()) {
            return $this->fragment(view('admin.badges._panel', ['badges' => $this->all()]), $message);
        }

        return back()->with('success', $message);
    }
}
