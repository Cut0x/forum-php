<div id="categories-panel" class="space-y-6">
    <form method="post" action="{{ route('admin.categories.store') }}" class="card space-y-3 p-5" data-remote="replace" data-target="#categories-panel">
        @csrf
        <h2 class="text-sm font-semibold text-ink">Nouvelle catégorie</h2>
        <div class="grid gap-3 sm:grid-cols-2">
            <input type="text" name="name" placeholder="Nom" required class="field">
            <input type="text" name="description" placeholder="Description" required class="field">
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <label class="flex items-center gap-2 text-sm text-ink"><input type="checkbox" name="is_readonly" class="rounded border-ink/20"> Lecture seule</label>
            <label class="flex items-center gap-2 text-sm text-ink"><input type="checkbox" name="is_pinned" class="rounded border-ink/20"> Épinglée</label>
            <button type="submit" class="btn-primary ml-auto">Créer</button>
        </div>
    </form>

    <div class="space-y-3">
        @foreach($categories as $category)
            <div x-data="{ editing: false }" class="card p-4">
                <div x-show="!editing" class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-ink">{{ $category->name }}</p>
                        <p class="text-sm text-muted">{{ $category->description }}</p>
                        <div class="mt-1 flex gap-2">
                            @if($category->is_readonly)<span class="text-xs text-muted">Lecture seule</span>@endif
                            @if($category->is_pinned)<span class="text-xs text-muted">Épinglée</span>@endif
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-3 text-sm">
                        <button type="button" @click="editing = true" class="text-muted hover:text-ink">Éditer</button>
                        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" data-remote="replace" data-target="#categories-panel">
                            @csrf @method('delete')
                            <x-confirm-submit
                                title="Supprimer la catégorie ?"
                                :message="'« '.$category->name.' » sera définitivement supprimée, avec tous ses sujets.'"
                            />
                        </form>
                    </div>
                </div>

                <form x-show="editing" x-cloak method="post" action="{{ route('admin.categories.update', $category) }}" class="space-y-3" data-remote="replace" data-target="#categories-panel">
                    @csrf @method('patch')
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input type="text" name="name" value="{{ $category->name }}" class="field">
                        <input type="text" name="description" value="{{ $category->description }}" class="field">
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-ink"><input type="checkbox" name="is_readonly" {{ $category->is_readonly ? 'checked' : '' }} class="rounded border-ink/20"> Lecture seule</label>
                        <label class="flex items-center gap-2 text-sm text-ink"><input type="checkbox" name="is_pinned" {{ $category->is_pinned ? 'checked' : '' }} class="rounded border-ink/20"> Épinglée</label>
                        <input type="number" name="sort_order" value="{{ $category->sort_order }}" class="field w-24" placeholder="Ordre">
                        <button type="submit" class="btn-primary">Enregistrer</button>
                        <button type="button" @click="editing = false" class="btn-secondary">Annuler</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
