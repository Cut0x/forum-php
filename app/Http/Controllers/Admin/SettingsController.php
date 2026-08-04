<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_title' => ['required', 'string', 'max:80'],
            'site_description' => ['required', 'string', 'max:255'],
            'footer_text' => ['required', 'string', 'max:80'],
            'footer_link' => ['nullable', 'url', 'max:255'],
            'stripe_url' => ['nullable', 'url', 'max:255'],
        ]);

        Settings::setMany([
            ...$data,
            'stripe_enabled' => $request->boolean('stripe_enabled') ? '1' : '0',
        ]);

        return back()->with('success', 'Réglages mis à jour.');
    }
}
