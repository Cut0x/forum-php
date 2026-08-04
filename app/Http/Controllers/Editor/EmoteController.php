<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Emote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class EmoteController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $emotes = Cache::remember('emotes.enabled.list', now()->addHour(), function () {
            return Emote::query()
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Emote $emote) => [
                    'name' => $emote->name,
                    'file' => asset('storage/emotes/'.$emote->file),
                    'title' => $emote->title,
                ]);
        });

        return response()->json($emotes);
    }
}
