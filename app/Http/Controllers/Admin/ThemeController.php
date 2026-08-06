<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteAssetUploader;
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

    /** Clé de réglage => [dossier de stockage, dimension max en pixels]. */
    protected const IDENTITY_ASSETS = [
        'site_logo' => ['branding/logo', 512],
        'site_favicon' => ['branding/favicon', 256],
        'site_footer_logo' => ['branding/footer-logo', 512],
    ];

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

    public function updateIdentity(Request $request, SiteAssetUploader $uploader): RedirectResponse|Response
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'footer_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
        ]);

        $updates = [];

        foreach (self::IDENTITY_ASSETS as $key => [$folder, $maxDimension]) {
            $field = str($key)->after('site_')->value(); // site_logo -> logo, etc.
            $current = Settings::get($key, config("theme.defaults.$key"));

            if ($request->hasFile($field)) {
                $uploader->delete($current ?: null);
                $updates[$key] = $uploader->store($request->file($field), $folder, $maxDimension);
            } elseif ($request->boolean('remove_'.$field)) {
                $uploader->delete($current ?: null);
                $updates[$key] = '';
            }
        }

        if ($updates !== []) {
            Settings::setMany($updates);
        }

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
            'identity' => collect(array_keys(self::IDENTITY_ASSETS))->mapWithKeys(fn ($key) => [$key => Settings::get($key, config("theme.defaults.$key"))]),
        ];
    }
}
