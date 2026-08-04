<?php

namespace App\Support;

class Username
{
    /**
     * Normalise un nom en @username : minuscules, sans accents, séparé par des underscores.
     */
    public static function normalize(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $lower = mb_strtolower($value, 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $lower) ?: $lower;

        $ascii = strtr($ascii, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'œ' => 'oe', 'æ' => 'ae',
        ]);

        $ascii = preg_replace('/[^a-z0-9_]+/', '_', $ascii);
        $ascii = trim($ascii, '_');
        $ascii = preg_replace('/_+/', '_', $ascii);

        return $ascii ?? '';
    }

    /**
     * Normalise le nom en @username unique (ajoute un suffixe numérique si besoin).
     */
    public static function unique(string $name, ?int $ignoreUserId = null): string
    {
        $base = self::normalize($name);
        if ($base === '' || strlen($base) < 3) {
            $base = 'membre';
        }
        $base = substr($base, 0, 26);

        $username = $base;
        $i = 1;
        while (\App\Models\User::query()
            ->where('username', $username)
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
            ->exists()) {
            $username = $base.'_'.(++$i);
        }

        return $username;
    }
}
