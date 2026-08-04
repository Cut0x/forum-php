<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class AvatarUploader
{
    /**
     * Recadre et redimensionne l'avatar en carré 256x256, puis le stocke.
     * Retourne le chemin relatif (disque "public").
     */
    public function store(UploadedFile $file, int $userId): string
    {
        $manager = new ImageManager(Driver::class);
        $image = $manager->decodePath($file->getRealPath())->cover(256, 256);

        $filename = 'avatars/user_'.$userId.'_'.now()->timestamp.'_'.Str::random(6).'.jpg';
        Storage::disk('public')->put($filename, (string) $image->encode(new JpegEncoder(quality: 90)));

        return $filename;
    }
}
