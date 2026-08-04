<x-guest-layout>
    <h1 class="mb-4 text-lg font-semibold text-ink">Inscription</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-1 block text-xs font-medium text-ink">Nom</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="field">
            <p class="mt-1 text-xs text-muted">Votre @username sera généré automatiquement à partir de ce nom.</p>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <label for="email" class="mb-1 block text-xs font-medium text-ink">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="field">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="mb-1 block text-xs font-medium text-ink">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="field">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-xs font-medium text-ink">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="field">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" class="btn-primary w-full">Créer un compte</button>
    </form>

    <p class="mt-5 text-center text-sm text-muted">
        Déjà inscrit ? <a href="{{ route('login') }}" class="text-brand hover:underline">Connectez-vous</a>
    </p>
</x-guest-layout>
