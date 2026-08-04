<?php

namespace App\Support;

class Color
{
    /**
     * Convertit une couleur hexadécimale ("#d97706") en triplet RGB ("217 119 6")
     * pour l'utiliser avec les classes Tailwind avec opacité (bg-brand/50).
     */
    public static function toRgbTriplet(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return '0 0 0';
        }

        return implode(' ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ]);
    }
}
