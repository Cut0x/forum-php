<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emote;
use App\Services\EmoteUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class EmoteController extends Controller
{
    public function index(): View
    {
        $emotes = Emote::query()->orderBy('name')->get();

        return view('admin.emotes', compact('emotes'));
    }

    public function store(Request $request, EmoteUploader $uploader): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_+-]{2,50}$/', 'unique:emotes,name'],
            'title' => ['nullable', 'string', 'max:80'],
            'file' => ['required', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
        ]);

        $filename = $uploader->store($request->file('file'), $data['name']);

        Emote::query()->create([
            'name' => $data['name'],
            'title' => $data['title'] ?? null,
            'file' => $filename,
            'is_enabled' => $request->boolean('is_enabled', true),
        ]);

        $this->flushCache();

        return back()->with('success', 'Émote ajoutée.');
    }

    public function update(Request $request, Emote $emote, EmoteUploader $uploader): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_+-]{2,50}$/', 'unique:emotes,name,'.$emote->id],
            'title' => ['nullable', 'string', 'max:80'],
            'file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
        ]);

        $filename = $emote->file;
        if ($request->hasFile('file')) {
            $filename = $uploader->store($request->file('file'), $data['name']);
        }

        $emote->update([
            'name' => $data['name'],
            'title' => $data['title'] ?? null,
            'file' => $filename,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        $this->flushCache();

        return back()->with('success', 'Émote mise à jour.');
    }

    public function destroy(Emote $emote): RedirectResponse
    {
        $emote->delete();
        $this->flushCache();

        return back()->with('success', 'Émote supprimée.');
    }

    protected function flushCache(): void
    {
        Cache::forget('emotes.enabled');
        Cache::forget('emotes.enabled.list');
    }
}
