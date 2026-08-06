@php $ruleTypes = \App\Models\Badge::ruleTypes(); @endphp

<div id="badges-panel" class="space-y-6">
    <form method="post" action="{{ route('admin.badges.store') }}" class="card space-y-3 p-5" enctype="multipart/form-data" data-remote="replace" data-target="#badges-panel" x-data="{ ruleType: 'manual' }">
        @csrf
        <h2 class="text-sm font-semibold text-ink">Nouveau badge</h2>
        <div class="grid gap-3 sm:grid-cols-4">
            <input type="text" name="name" placeholder="Nom" required class="field">
            <input type="text" name="code" placeholder="code_unique" required class="field">
            <input type="color" name="color" value="#0d6efd" class="h-10 w-full rounded border border-ink/15 bg-transparent">
            <select name="rule_type" x-model="ruleType" class="field">
                @foreach($ruleTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs text-muted">Icône (upload, recadrée en 128×128)</label>
                <input type="file" name="icon_file" accept="image/png,image/jpeg,image/gif,image/webp" class="field text-xs">
                <p class="mt-1 text-[11px] text-muted">Ou, à défaut, un nom de fichier déjà présent dans <code>public/images/badges/</code> : <input type="text" name="icon" placeholder="badge.png" class="field mt-1 text-xs"></p>
            </div>
            <div x-show="ruleType !== 'manual'" x-cloak>
                <label class="mb-1 block text-xs text-muted">Valeur de la règle</label>
                <template x-if="ruleType === 'role'">
                    <select name="rule_value" class="field">
                        <option value="member">Membre</option>
                        <option value="moderator">Modérateur</option>
                        <option value="admin">Admin</option>
                    </select>
                </template>
                <template x-if="ruleType !== 'role'">
                    <input type="number" name="rule_value" min="0" placeholder="Seuil (ex : 10)" class="field">
                </template>
            </div>
        </div>
        <button type="submit" class="btn-primary">Ajouter</button>
    </form>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($badges as $badge)
            <div class="card space-y-2 p-4">
                <form
                    method="post"
                    action="{{ route('admin.badges.update', $badge) }}"
                    class="space-y-2"
                    enctype="multipart/form-data"
                    data-remote="replace"
                    data-target="#badges-panel"
                    x-data="{ ruleType: '{{ $badge->rule_type ?? 'manual' }}' }"
                >
                    @csrf @method('patch')
                    <div class="flex items-center gap-2">
                        <img src="{{ $badge->iconUrl }}" alt="" class="h-8 w-8" onerror="this.style.opacity=0.2">
                        <input type="text" name="name" value="{{ $badge->name }}" class="field">
                    </div>
                    <input type="text" name="code" value="{{ $badge->code }}" class="field text-xs">
                    <div class="flex items-center gap-2">
                        <input type="color" name="color" value="{{ $badge->color }}" class="h-8 w-8 shrink-0 rounded border border-ink/15 bg-transparent">
                        <input type="file" name="icon_file" accept="image/png,image/jpeg,image/gif,image/webp" class="field text-xs">
                    </div>
                    <input type="text" name="icon" value="{{ $badge->icon }}" class="field text-xs" placeholder="ou nom de fichier statique">

                    <select name="rule_type" x-model="ruleType" class="field text-xs">
                        @foreach($ruleTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div x-show="ruleType !== 'manual'" x-cloak>
                        <template x-if="ruleType === 'role'">
                            <select name="rule_value" class="field text-xs">
                                <option value="member" @selected($badge->rule_value === 'member')>Membre</option>
                                <option value="moderator" @selected($badge->rule_value === 'moderator')>Modérateur</option>
                                <option value="admin" @selected($badge->rule_value === 'admin')>Admin</option>
                            </select>
                        </template>
                        <template x-if="ruleType !== 'role'">
                            <input type="number" name="rule_value" min="0" value="{{ $badge->rule_value }}" placeholder="Seuil" class="field text-xs">
                        </template>
                    </div>

                    <button type="submit" class="btn-secondary w-full">Enregistrer</button>
                </form>

                <form method="post" action="{{ route('admin.badges.destroy', $badge) }}" data-remote="replace" data-target="#badges-panel">
                    @csrf @method('delete')
                    <x-confirm-submit
                        label="Supprimer"
                        class="btn-danger w-full"
                        title="Supprimer « {{ $badge->name }} » ?"
                        message="Il sera retiré de tous les utilisateurs qui l'ont obtenu. Cette action est irréversible."
                    />
                </form>
            </div>
        @endforeach
    </div>
</div>
