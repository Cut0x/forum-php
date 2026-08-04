<div id="emotes-panel" class="space-y-6">
    <form method="post" action="{{ route('admin.emotes.store') }}" enctype="multipart/form-data" class="card space-y-3 p-5" data-remote="replace" data-target="#emotes-panel">
        @csrf
        <h2 class="text-sm font-semibold text-ink">Nouvelle émote</h2>
        <div class="grid gap-3 sm:grid-cols-3">
            <input type="text" name="name" placeholder="smile" required class="field">
            <input type="text" name="title" placeholder="Titre (optionnel)" class="field">
            <input type="file" name="file" accept="image/png,image/jpeg,image/gif,image/webp" required class="text-sm text-muted">
        </div>
        <p class="text-xs text-muted">Nom : lettres, chiffres, "_" et "-" uniquement (sans accents ni espaces). Image de 2 Mo maximum.</p>
        <label class="flex items-center gap-2 text-sm text-ink">
            <input type="checkbox" name="is_enabled" value="1" checked class="rounded border-ink/20"> Active
        </label>
        <button type="submit" class="btn-primary">Ajouter</button>
    </form>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($emotes as $emote)
            <form method="post" action="{{ route('admin.emotes.update', $emote) }}" enctype="multipart/form-data" class="card space-y-2 p-4 {{ $emote->is_enabled ? '' : 'opacity-50' }}" data-remote="replace" data-target="#emotes-panel">
                @csrf @method('patch')
                <div class="flex items-center gap-2">
                    <img src="{{ asset('storage/emotes/'.$emote->file) }}" alt="" class="h-8 w-8">
                    <input type="text" name="name" value="{{ $emote->name }}" class="field">
                </div>
                <input type="text" name="title" value="{{ $emote->title }}" placeholder="Titre" class="field text-xs">
                <input type="file" name="file" accept="image/png,image/jpeg,image/gif,image/webp" class="text-xs text-muted">
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-ink">
                        <input type="checkbox" name="is_enabled" value="1" {{ $emote->is_enabled ? 'checked' : '' }} class="rounded border-ink/20"> Active
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-secondary">Enregistrer</button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>

    @if($emotes->isNotEmpty())
        <div class="flex flex-wrap gap-3">
            @foreach($emotes as $emote)
                <form method="post" action="{{ route('admin.emotes.destroy', $emote) }}" data-remote="replace" data-target="#emotes-panel">
                    @csrf @method('delete')
                    <x-confirm-submit :label="'Supprimer :'.$emote->name.':'" class="text-xs text-muted hover:text-red-600" />
                </form>
            @endforeach
        </div>
    @endif
</div>
