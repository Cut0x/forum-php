<div id="footer-panel" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <form method="post" action="{{ route('admin.footer.categories.store') }}" class="card space-y-2 p-4" data-remote="replace" data-target="#footer-panel">
            @csrf
            <h2 class="text-sm font-semibold text-ink">Nouvelle catégorie</h2>
            <div class="flex gap-2">
                <input type="text" name="name" placeholder="Utiles" required class="field">
                <button type="submit" class="btn-secondary shrink-0">Ajouter</button>
            </div>
        </form>

        <form method="post" action="{{ route('admin.footer.links.store') }}" class="card space-y-2 p-4" data-remote="replace" data-target="#footer-panel">
            @csrf
            <h2 class="text-sm font-semibold text-ink">Nouveau lien</h2>
            <select name="footer_category_id" class="field" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input type="text" name="label" placeholder="Documentation" required class="field">
            <input type="text" name="url" placeholder="https://…" required class="field">
            <button type="submit" class="btn-secondary">Ajouter</button>
        </form>
    </div>

    <div class="space-y-3">
        @foreach($categories as $category)
            <div class="card p-4">
                <div class="flex items-center justify-between">
                    <strong class="text-ink">{{ $category->name }}</strong>
                    <form method="post" action="{{ route('admin.footer.categories.destroy', $category) }}" data-remote="replace" data-target="#footer-panel">
                        @csrf @method('delete')
                        <x-confirm-submit
                            class="text-sm text-muted hover:text-red-600"
                            :title="'Supprimer la catégorie « '.$category->name.' » ?'"
                            message="Tous ses liens seront supprimés avec elle."
                        />
                    </form>
                </div>
                <div class="mt-2 space-y-1">
                    @foreach($category->links as $link)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink">{{ $link->label }} <span class="text-muted">- {{ $link->url }}</span></span>
                            <form method="post" action="{{ route('admin.footer.links.destroy', $link) }}" data-remote="replace" data-target="#footer-panel">
                                @csrf @method('delete')
                                <button type="submit" class="text-muted hover:text-red-600">Retirer</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
