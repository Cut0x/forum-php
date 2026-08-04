<x-guest-layout>
    <h1 class="mb-2 text-lg font-semibold text-ink">Mot de passe oublié</h1>
    <p class="mb-4 text-sm text-muted">Indiquez votre email, nous vous enverrons un lien de réinitialisation.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1 block text-xs font-medium text-ink">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="field">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <button type="submit" class="btn-primary w-full">Envoyer le lien</button>
    </form>
</x-guest-layout>
