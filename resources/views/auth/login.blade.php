<x-guest-layout>
    <h1 class="mb-4 text-lg font-semibold text-ink">Connexion</h1>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-xs font-medium text-ink">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="mb-1 block text-xs font-medium text-ink">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="field">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <label class="flex items-center gap-2 text-sm text-ink">
            <input type="checkbox" name="remember" class="rounded border-ink/20">
            Se souvenir de moi
        </label>

        <div class="flex items-center justify-between pt-1">
            @if (Route::has('password.request'))
                <a class="text-sm text-muted hover:text-ink" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            @endif
            <button type="submit" class="btn-primary">Connexion</button>
        </div>
    </form>

    <p class="mt-5 text-center text-sm text-muted">
        Pas encore de compte ? <a href="{{ route('register') }}" class="text-brand hover:underline">Inscrivez-vous</a>
    </p>
</x-guest-layout>
