<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

class BadgeIconUploader
{
    /**
     * Taille (en pixels) à laquelle toutes les icônes de badge sont recadrées,
     * pour un affichage uniforme (même logique que EmoteUploader).
     */
    protected const SIZE = 128;

    /**
     * Recadre l'icône en carré, la stocke et retourne son chemin relatif (disque "public").
     */
    public function store(UploadedFile $file): string
    {
        $isGif = $file->getMimeType() === 'image/gif';

        $manager = new ImageManager(Driver::class);
        $image = $manager->decodePath($file->getRealPath())->cover(self::SIZE, self::SIZE);

        $filename = 'badges/'.Str::random(20).'.'.($isGif ? 'gif' : 'png');
        $encoded = $isGif ? (string) $image->encode(new GifEncoder()) : (string) $image->encode(new PngEncoder());

        Storage::disk('public')->put($filename, $encoded);

        return $filename;
    }
}
