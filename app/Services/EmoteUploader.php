<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

class EmoteUploader
{
    /**
     * Taille (en pixels) à laquelle toutes les émotes sont recadrées,
     * pour qu'elles s'affichent toutes au même format dans le texte.
     */
    protected const SIZE = 64;

    /**
     * Recadre l'émote en carré, la stocke et retourne son nom de fichier.
     */
    public function store(UploadedFile $file, string $name): string
    {
        $isGif = $file->getMimeType() === 'image/gif';

        $manager = new ImageManager(Driver::class);
        $image = $manager->decodePath($file->getRealPath())->cover(self::SIZE, self::SIZE);

        $filename = Str::slug($name).'.'.($isGif ? 'gif' : 'png');
        $encoded = $isGif ? (string) $image->encode(new GifEncoder()) : (string) $image->encode(new PngEncoder());

        Storage::disk('public')->put('emotes/'.$filename, $encoded);

        return $filename;
    }
}
