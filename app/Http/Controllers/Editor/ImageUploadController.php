<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $file = $request->file('image');
        $filename = 'img_'.$request->user()->id.'_'.now()->timestamp.'_'.Str::random(8).'.'.$file->extension();
        $path = $file->storeAs('uploads', $filename, 'public');

        return response()->json(['url' => asset('storage/'.$path)]);
    }
}
