<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeController extends Controller
{
    protected const KEYS = [
        'theme_light_bg', 'theme_light_surface', 'theme_light_text', 'theme_light_muted', 'theme_light_primary', 'theme_light_accent',
        'theme_dark_bg', 'theme_dark_surface', 'theme_dark_text', 'theme_dark_muted', 'theme_dark_primary', 'theme_dark_accent',
        'theme_font',
    ];

    public function edit(): View
    {
        $theme = collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => Settings::get($key, config("theme.defaults.$key"))]);
        $presets = config('theme.presets');

        return view('admin.theme', compact('theme', 'presets'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme_light_bg' => ['required', 'string', 'max:20'],
            'theme_light_surface' => ['required', 'string', 'max:20'],
            'theme_light_text' => ['required', 'string', 'max:20'],
            'theme_light_muted' => ['required', 'string', 'max:20'],
            'theme_light_primary' => ['required', 'string', 'max:20'],
            'theme_light_accent' => ['required', 'string', 'max:20'],
            'theme_dark_bg' => ['required', 'string', 'max:20'],
            'theme_dark_surface' => ['required', 'string', 'max:20'],
            'theme_dark_text' => ['required', 'string', 'max:20'],
            'theme_dark_muted' => ['required', 'string', 'max:20'],
            'theme_dark_primary' => ['required', 'string', 'max:20'],
            'theme_dark_accent' => ['required', 'string', 'max:20'],
            'theme_font' => ['required', 'string', 'max:120'],
        ]);

        Settings::setMany($data);

        return back()->with('success', 'Thème mis à jour.');
    }

    public function preset(Request $request): RedirectResponse
    {
        $data = $request->validate(['preset' => ['required', 'string']]);
        $preset = config('theme.presets.'.$data['preset']);

        if (! $preset) {
            return back()->with('error', 'Preset introuvable.');
        }

        Settings::setMany($preset['colors']);

        return back()->with('success', 'Preset appliqué.');
    }

    public function reset(): RedirectResponse
    {
        $defaults = collect(config('theme.defaults'))->only(self::KEYS)->all();
        Settings::setMany($defaults);

        return back()->with('success', 'Thème réinitialisé.');
    }
}
