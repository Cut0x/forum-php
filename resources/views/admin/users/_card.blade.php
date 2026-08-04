<div id="admin-user-{{ $user->id }}" class="card p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('profile.show', $user) }}" class="font-medium text-ink hover:underline">{{ $user->displayName() }}</a>
            <span class="text-sm text-muted">{{ '@'.$user->username }}</span>
        </div>
        <form method="post" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-2" data-remote="replace" data-target="#admin-user-{{ $user->id }}">
            @csrf @method('patch')
            <select name="role" class="field !py-1.5 text-xs">
                <option value="member" @selected($user->role === 'member')>Membre</option>
                <option value="moderator" @selected($user->role === 'moderator')>Modérateur</option>
                <option value="admin" @selected($user->role === 'admin')>Admin</option>
            </select>
            <button type="submit" class="btn-secondary !py-1.5 text-xs">OK</button>
        </form>
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($badges as $badge)
            @php $has = $user->badges->contains('id', $badge->id); @endphp
            <form method="post" action="{{ $has ? route('admin.users.badges.detach', [$user, $badge]) : route('admin.users.badges.attach', [$user, $badge]) }}" data-remote="replace" data-target="#admin-user-{{ $user->id }}">
                @csrf
                @if($has) @method('delete') @endif
                <button type="submit" class="flex items-center gap-1 rounded-full border px-2 py-1 text-xs {{ $has ? 'border-brand bg-brand/10 text-brand' : 'border-ink/15 text-muted hover:text-ink' }}">
                    <img src="{{ asset('images/badges/'.$badge->icon) }}" class="h-4 w-4" alt="">
                    {{ $badge->name }}
                </button>
            </form>
        @endforeach
    </div>
</div>
