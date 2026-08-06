@php
    $fields = [
        'light' => [
            'theme_light_bg' => 'Fond',
            'theme_light_surface' => 'Surface',
            'theme_light_text' => 'Texte',
            'theme_light_muted' => 'Texte atténué',
            'theme_light_primary' => 'Couleur principale',
            'theme_light_accent' => 'Accent',
        ],
        'dark' => [
            'theme_dark_bg' => 'Fond',
            'theme_dark_surface' => 'Surface',
            'theme_dark_text' => 'Texte',
            'theme_dark_muted' => 'Texte atténué',
            'theme_dark_primary' => 'Couleur principale',
            'theme_dark_accent' => 'Accent',
        ],
    ];
@endphp

<div id="theme-panel" class="space-y-6">
    @php
        $assets = [
            'site_logo' => ['label' => 'Logo (barre de navigation)', 'help' => 'Affiché à la place de l\'icône par défaut dans l\'en-tête.'],
            'site_favicon' => ['label' => 'Favicon (onglet du navigateur)', 'help' => 'PNG carré recommandé.'],
            'site_footer_logo' => ['label' => 'Logo (pied de page)', 'help' => 'Affiché au-dessus du texte du footer.'],
        ];
    @endphp

    <form method="post" action="{{ route('admin.theme.identity') }}" class="card space-y-4 p-5" data-remote="replace" data-target="#theme-panel">
        @csrf @method('patch')
        <h2 class="text-sm font-semibold text-ink">Identité visuelle</h2>
        <p class="text-xs text-muted">Chaque champ attend un lien direct vers une image déjà hébergée (PNG recommandé), par ex. <code>https://exemple.com/logo.png</code>. Laissez vide pour revenir à l'affichage par défaut.</p>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach($assets as $key => $meta)
                <div class="space-y-2">
                    <label for="{{ $key }}" class="block text-xs font-medium text-ink">{{ $meta['label'] }}</label>
                    <div class="flex h-16 items-center justify-center rounded-lg border border-dashed border-ink/15 bg-ink/5 p-2">
                        @if($identity[$key] ?? null)
                            <img src="{{ $identity[$key] }}" alt="" class="max-h-full max-w-full object-contain" onerror="this.style.opacity=0.2">
                        @else
                            <span class="text-xs text-muted">Aucun</span>
                        @endif
                    </div>
                    <input id="{{ $key }}" type="url" name="{{ $key }}" value="{{ old($key, $identity[$key] ?? '') }}" placeholder="https://…/logo.png" class="field text-xs">
                    <p class="text-[11px] text-muted">{{ $meta['help'] }}</p>
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn-primary">Enregistrer l'identité visuelle</button>
    </form>

    <div class="card flex flex-wrap items-center gap-2 p-4">
        @foreach($presets as $key => $preset)
            <form method="post" action="{{ route('admin.theme.preset') }}" data-remote="replace" data-target="#theme-panel">
                @csrf
                <input type="hidden" name="preset" value="{{ $key }}">
                <button type="submit" class="btn-secondary">{{ $preset['label'] }}</button>
            </form>
        @endforeach
        <form method="post" action="{{ route('admin.theme.reset') }}" class="ml-auto" data-remote="replace" data-target="#theme-panel">
            @csrf
            <button type="submit" class="btn-ghost">Réinitialiser</button>
        </form>
    </div>

    <form method="post" action="{{ route('admin.theme.update') }}" class="card space-y-6 p-5" data-remote="none">
        @csrf @method('patch')

        <div class="grid gap-6 sm:grid-cols-2">
            @foreach($fields as $mode => $modeFields)
                <div>
                    <h2 class="mb-3 text-sm font-semibold text-ink">{{ $mode === 'light' ? 'Clair' : 'Sombre' }}</h2>
                    <div class="space-y-3">
                        @foreach($modeFields as $key => $label)
                            <div class="flex items-center justify-between gap-3">
                                <label for="{{ $key }}" class="text-xs text-muted">{{ $label }}</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" value="{{ $theme[$key] }}" onchange="document.getElementById('{{ $key }}').value = this.value" class="h-8 w-8 rounded border border-ink/15 bg-transparent">
                                    <input id="{{ $key }}" type="text" name="{{ $key }}" value="{{ old($key, $theme[$key]) }}" class="field w-28 text-xs">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-ink">Police (CSS font-family)</label>
            <input type="text" name="theme_font" value="{{ old('theme_font', $theme['theme_font']) }}" class="field">
        </div>

        <button type="submit" class="btn-primary">Enregistrer</button>
    </form>
</div>
