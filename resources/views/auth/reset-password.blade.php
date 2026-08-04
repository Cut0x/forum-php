<x-guest-layout>
    <h1 class="mb-4 text-lg font-semibold text-ink">Réinitialiser le mot de passe</h1>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="mb-1 block text-xs font-medium text-ink">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="field">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="mb-1 block text-xs font-medium text-ink">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="field">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-xs font-medium text-ink">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="field">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" class="btn-primary w-full">Réinitialiser</button>
    </form>
</x-guest-layout>
