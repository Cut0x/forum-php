<x-moderation-layout title="Utilisateurs" active="users">
    <form method="get" action="{{ route('moderation.users.index') }}" class="card p-3">
        <input type="search" name="q" value="{{ $query }}" placeholder="Rechercher un utilisateur…" class="field">
    </form>

    <div class="space-y-3">
        @foreach($users as $user)
            @include('moderation.users._card', ['user' => $user])
        @endforeach
    </div>

    <div>{{ $users->links() }}</div>
</x-moderation-layout>
