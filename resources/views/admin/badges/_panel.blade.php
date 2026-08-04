<div id="badges-panel" class="space-y-6">
    <form method="post" action="{{ route('admin.badges.store') }}" class="card space-y-3 p-5" data-remote="replace" data-target="#badges-panel">
        @csrf
        <h2 class="text-sm font-semibold text-ink">Nouveau badge</h2>
        <div class="grid gap-3 sm:grid-cols-4">
            <input type="text" name="name" placeholder="Nom" required class="field">
            <input type="text" name="code" placeholder="code_unique" required class="field">
            <input type="text" name="icon" placeholder="badge.png" required class="field">
            <input type="color" name="color" value="#0d6efd" class="h-10 w-full rounded border border-ink/15 bg-transparent">
        </div>
        <p class="text-xs text-muted">Les icônes sont cherchées dans <code>public/images/badges/</code>.</p>
        <button type="submit" class="btn-primary">Ajouter</button>
    </form>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($badges as $badge)
            <form method="post" action="{{ route('admin.badges.update', $badge) }}" class="card space-y-2 p-4" data-remote="replace" data-target="#badges-panel">
                @csrf @method('patch')
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/badges/'.$badge->icon) }}" alt="" class="h-8 w-8" onerror="this.style.opacity=0.2">
                    <input type="text" name="name" value="{{ $badge->name }}" class="field">
                </div>
                <input type="text" name="code" value="{{ $badge->code }}" class="field text-xs">
                <input type="text" name="icon" value="{{ $badge->icon }}" class="field text-xs">
                <div class="flex items-center gap-2">
                    <input type="color" name="color" value="{{ $badge->color }}" class="h-8 w-8 rounded border border-ink/15 bg-transparent">
                    <button type="submit" class="btn-secondary flex-1">Enregistrer</button>
                </div>
            </form>
        @endforeach
    </div>
</div>
