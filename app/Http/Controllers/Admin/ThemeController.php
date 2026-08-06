<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ThemeController extends Controller
{
    protected const KEYS = [
        'theme_light_bg', 'theme_light_surface', 'theme_light_text', 'theme_light_muted', 'theme_light_primary', 'theme_light_accent',
        'theme_dark_bg', 'theme_dark_surface', 'theme_dark_text', 'theme_dark_muted', 'theme_dark_primary', 'theme_dark_accent',
        'theme_font',
    ];

    /** Réglages d'identité visuelle : chacun est un lien direct vers une image (PNG recommandé). */
    protected const IDENTITY_KEYS = ['site_logo', 'site_favicon', 'site_footer_logo'];

    public function edit(): View
    {
        return view('admin.theme', $this->theme());
    }

    public function update(Request $request): RedirectResponse|Response
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

        if ($request->ajax()) {
            return $this->ajaxOk('Thème mis à jour.');
        }

        return back()->with('success', 'Thème mis à jour.');
    }

    public function updateIdentity(Request $request): RedirectResponse|Response
    {
        $data = $request->validate([
            'site_logo' => ['nullable', 'url', 'max:2048'],
            'site_favicon' => ['nullable', 'url', 'max:2048'],
            'site_footer_logo' => ['nullable', 'url', 'max:2048'],
        ]);

        Settings::setMany([
            'site_logo' => $data['site_logo'] ?? '',
            'site_favicon' => $data['site_favicon'] ?? '',
            'site_footer_logo' => $data['site_footer_logo'] ?? '',
        ]);

        if ($request->ajax()) {
            return $this->fragment(view('admin.theme._panel', $this->theme()), 'Identité visuelle mise à jour.');
        }

        return back()->with('success', 'Identité visuelle mise à jour.');
    }

    public function preset(Request $request): RedirectResponse|Response
    {
        $data = $request->validate(['preset' => ['required', 'string']]);
        $preset = config('theme.presets.'.$data['preset']);

        if (! $preset) {
            if ($request->ajax()) {
                return $this->fragment(view('admin.theme._panel', $this->theme()), 'Preset introuvable.', 'error');
            }

            return back()->with('error', 'Preset introuvable.');
        }

        Settings::setMany($preset['colors']);

        if ($request->ajax()) {
            return $this->fragment(view('admin.theme._panel', $this->theme()), 'Preset appliqué.');
        }

        return back()->with('success', 'Preset appliqué.');
    }

    public function reset(Request $request): RedirectResponse|Response
    {
        $defaults = collect(config('theme.defaults'))->only(self::KEYS)->all();
        Settings::setMany($defaults);

        if ($request->ajax()) {
            return $this->fragment(view('admin.theme._panel', $this->theme()), 'Thème réinitialisé.');
        }

        return back()->with('success', 'Thème réinitialisé.');
    }

    protected function theme(): array
    {
        return [
            'theme' => collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => Settings::get($key, config("theme.defaults.$key"))]),
            'presets' => config('theme.presets'),
            'identity' => collect(self::IDENTITY_KEYS)->mapWithKeys(fn ($key) => [$key => Settings::get($key, config("theme.defaults.$key"))]),
        ];
    }
}
