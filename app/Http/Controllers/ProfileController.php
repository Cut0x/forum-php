<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\AvatarUploader;
use App\Support\Username;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $user->load(['badges', 'links']);

        $stats = [
            'topics' => $user->topics()->count(),
            'posts' => $user->posts()->count(),
            'badges' => $user->badges->count(),
        ];

        // whereHas filtre par sécurité les sujets/messages dont le parent (catégorie/sujet)
        // n'existe plus, au cas où (ex: catégorie supprimée sans cascade).
        $recentTopics = $user->topics()->whereHas('category')->with('category')->latest()->limit(5)->get(['id', 'title', 'slug', 'category_id', 'created_at']);
        $recentPosts = $user->posts()->whereHas('topic')->with(['topic:id,title,slug,category_id', 'topic.category'])->latest()->limit(5)->get();

        return view('profile.show', compact('user', 'stats', 'recentTopics', 'recentPosts'));
    }

    public function edit(Request $request): View
    {
        $user = $request->user()->load('links');

        return view('profile.edit', compact('user'));
    }

    public function update(ProfileUpdateRequest $request, AvatarUploader $avatarUploader): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $newName = trim($data['name']);
        if ($newName !== $user->name) {
            $user->username = Username::unique($newName, $user->id);
        }
        $user->name = $newName;
        $user->bio = $data['bio'] ?? null;

        if ($request->hasFile('avatar')) {
            $user->avatar = $avatarUploader->store($request->file('avatar'), $user->id);
        }

        $user->save();

        $user->links()->delete();
        foreach ($data['links'] ?? [] as $link) {
            if (! empty($link['label']) && ! empty($link['url'])) {
                $user->links()->create(['label' => $link['label'], 'url' => $link['url']]);
            }
        }

        return redirect()->route('profile.show', $user)->with('success', 'Profil mis à jour.');
    }
}
