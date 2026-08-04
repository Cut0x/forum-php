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

<x-admin-layout title="Thème" active="theme">
    <div class="card flex flex-wrap items-center gap-2 p-4">
        @foreach($presets as $key => $preset)
            <form method="post" action="{{ route('admin.theme.preset') }}">
                @csrf
                <input type="hidden" name="preset" value="{{ $key }}">
                <button type="submit" class="btn-secondary">{{ $preset['label'] }}</button>
            </form>
        @endforeach
        <form method="post" action="{{ route('admin.theme.reset') }}" class="ml-auto">
            @csrf
            <button type="submit" class="btn-ghost">Réinitialiser</button>
        </form>
    </div>

    <form method="post" action="{{ route('admin.theme.update') }}" class="card space-y-6 p-5">
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
</x-admin-layout>
