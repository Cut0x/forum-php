<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.edit', ['user' => $request->user()]);
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $request->user()->update([
            'notifications_enabled' => $request->boolean('notifications_enabled'),
        ]);

        return back()->with('success', 'Paramètres enregistrés.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'current_password' => ['required', 'current_password'],
        ]);

        $request->user()->update(['email' => $data['email']]);

        return back()->with('success', 'Email mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Mot de passe mis à jour.');
    }

    public function export(Request $request): JsonResponse
    {
        $user = $request->user();

        $payload = [
            'user' => $user->only(['id', 'name', 'username', 'email', 'role', 'bio', 'created_at']),
            'links' => $user->links()->get(['label', 'url']),
            'topics' => $user->topics()->get(['id', 'title', 'created_at']),
            'posts' => $user->posts()->get(['id', 'topic_id', 'content', 'created_at']),
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->json($payload)
            ->header('Content-Disposition', 'attachment; filename="export-'.$user->username.'.json"');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirm_text' => ['required', 'in:SUPPRIMER'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
