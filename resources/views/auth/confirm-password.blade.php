<x-guest-layout>
    <h1 class="mb-2 text-lg font-semibold text-ink">Confirmer le mot de passe</h1>
    <p class="mb-4 text-sm text-muted">Ceci est une zone sécurisée. Merci de confirmer votre mot de passe.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <div>
            <label for="password" class="mb-1 block text-xs font-medium text-ink">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="field">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <button type="submit" class="btn-primary w-full">Confirmer</button>
    </form>
</x-guest-layout>
