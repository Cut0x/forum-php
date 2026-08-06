<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

class SiteAssetUploader
{
    /**
     * Redimensionne (sans jamais agrandir ni recadrer, contrairement aux avatars/émotes/badges
     * qui sont recadrés en carré) un logo/favicon pour qu'il tienne dans un carré de $maxDimension,
     * le stocke sur le disque "public" et retourne son chemin relatif.
     */
    public function store(UploadedFile $file, string $folder, int $maxDimension = 512): string
    {
        $isGif = $file->getMimeType() === 'image/gif';

        $manager = new ImageManager(Driver::class);
        $image = $manager->decodePath($file->getRealPath())->scaleDown($maxDimension, $maxDimension);

        $filename = $folder.'/'.Str::random(20).'.'.($isGif ? 'gif' : 'png');
        $encoded = $isGif ? (string) $image->encode(new GifEncoder()) : (string) $image->encode(new PngEncoder());

        Storage::disk('public')->put($filename, $encoded);

        return $filename;
    }

    /**
     * Supprime un asset existant (si présent) — utilisé lors d'un remplacement ou d'un retrait.
     */
    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
