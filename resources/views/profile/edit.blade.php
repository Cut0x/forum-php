@php $links = $user->links; @endphp

<x-app-layout :title="'Éditer le profil'">
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-4 text-lg font-semibold text-ink">Éditer le profil</h1>

        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card space-y-4 p-5">
            @csrf
            @method('patch')

            <div class="flex items-center gap-4">
                <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/default-avatar.jpg') }}" class="avatar h-16 w-16" alt="">
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Avatar</label>
                    <input type="file" name="avatar" accept="image/png,image/jpeg" class="text-sm text-muted">
                    @error('avatar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-ink">Nom</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="field" required>
                <p class="mt-1 text-xs text-muted">Le @username est recalculé automatiquement (sans accents, sans majuscules).</p>
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-ink">Bio</label>
                <textarea name="bio" rows="3" class="field">{{ old('bio', $user->bio) }}</textarea>
                @error('bio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-xs font-medium text-ink">Liens</label>
                <div class="space-y-2">
                    @for($i = 0; $i < 3; $i++)
                        <div class="flex gap-2">
                            <input type="text" name="links[{{ $i }}][label]" value="{{ old("links.$i.label", $links[$i]->label ?? '') }}" placeholder="Label (ex : GitHub)" class="field">
                            <input type="url" name="links[{{ $i }}][url]" value="{{ old("links.$i.url", $links[$i]->url ?? '') }}" placeholder="https://…" class="field">
                        </div>
                    @endfor
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('profile.show', $user) }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</x-app-layout>
