<?php

namespace App\Support;

use App\Models\Setting as SettingModel;
use Illuminate\Support\Facades\Cache;

class Settings
{
    protected const CACHE_KEY = 'app.settings';

    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return SettingModel::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        SettingModel::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            SettingModel::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    public static function ensureDefaults(array $defaults): void
    {
        $existing = self::all();
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $existing)) {
                SettingModel::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
            }
        }
        Cache::forget(self::CACHE_KEY);
    }
}
