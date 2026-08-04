@php $warnings = $user->warnings()->latest()->get(); @endphp

<x-app-layout :title="'Paramètres'">
    <div class="mx-auto max-w-2xl space-y-6">
        <h1 class="text-lg font-semibold text-ink">Paramètres</h1>

        @if($warnings->isNotEmpty())
            <div class="card border-amber-300 p-4 dark:border-amber-800">
                <h2 class="mb-2 text-sm font-semibold text-ink">Avertissements reçus</h2>
                <ul class="space-y-1 text-sm text-muted">
                    @foreach($warnings as $warning)
                        <li>{{ $warning->created_at->format('d/m/Y') }} - {{ $warning->reason }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-5">
            <h2 class="mb-3 text-sm font-semibold text-ink">Notifications</h2>
            <form method="post" action="{{ route('settings.notifications') }}" class="flex items-center justify-between">
                @csrf @method('patch')
                <label class="flex items-center gap-2 text-sm text-ink">
                    <input type="checkbox" name="notifications_enabled" value="1" {{ $user->notifications_enabled ? 'checked' : '' }} class="rounded border-ink/20">
                    Activer les notifications
                </label>
                <button type="submit" class="btn-secondary">Enregistrer</button>
            </form>
        </div>

        <div class="card p-5">
            <h2 class="mb-3 text-sm font-semibold text-ink">Adresse email</h2>
            <form method="post" action="{{ route('settings.email') }}" class="space-y-3">
                @csrf @method('patch')
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Nouvel email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="field" required>
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="field" required>
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-secondary">Mettre à jour l'email</button>
            </form>
        </div>

        <div class="card p-5">
            <h2 class="mb-3 text-sm font-semibold text-ink">Mot de passe</h2>
            <form method="post" action="{{ route('settings.password') }}" class="space-y-3">
                @csrf @method('patch')
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="field" required>
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Nouveau mot de passe</label>
                    <input type="password" name="password" class="field" required>
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="field" required>
                </div>
                <button type="submit" class="btn-secondary">Mettre à jour le mot de passe</button>
            </form>
        </div>

        <div class="card p-5">
            <h2 class="mb-2 text-sm font-semibold text-ink">Exporter mes données</h2>
            <p class="mb-3 text-sm text-muted">Télécharge un fichier JSON avec votre profil, vos liens, vos sujets et vos messages.</p>
            <a href="{{ route('settings.export') }}" class="btn-secondary">Télécharger l'export</a>
        </div>

        <div class="card border-red-300 p-5 dark:border-red-900">
            <h2 class="mb-2 text-sm font-semibold text-red-600">Zone dangereuse</h2>
            <form method="post" action="{{ route('settings.destroy') }}" class="space-y-3" onsubmit="return confirm('Supprimer définitivement votre compte ?');">
                @csrf @method('delete')
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Tapez SUPPRIMER pour confirmer</label>
                    <input type="text" name="confirm_text" class="field" required>
                    @error('confirm_text') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="field" required>
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-danger">Supprimer mon compte</button>
            </form>
        </div>
    </div>
</x-app-layout>
