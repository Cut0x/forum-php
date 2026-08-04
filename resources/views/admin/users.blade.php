<x-admin-layout title="Utilisateurs" active="users">
    <div class="space-y-3">
        @foreach($users as $user)
            @include('admin.users._card', ['user' => $user, 'badges' => $badges])
        @endforeach
    </div>
</x-admin-layout>
