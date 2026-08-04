<x-guest-layout>
    <h1 class="mb-2 text-lg font-semibold text-ink">Vérification de l'email</h1>
    <p class="mb-4 text-sm text-muted">
        Merci de vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-emerald-600">
            Un nouveau lien de vérification a été envoyé à votre adresse email.
        </div>
    @endif

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary">Renvoyer l'email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-muted hover:text-ink">Déconnexion</button>
        </form>
    </div>
</x-guest-layout>
