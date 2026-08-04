@php $target = '#mod-user-'.$user->id; @endphp

<div id="mod-user-{{ $user->id }}" x-data="{ warn: false, suspend: false }" class="card p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('profile.show', $user) }}" class="font-medium text-ink hover:underline">{{ $user->displayName() }}</a>
            <span class="text-sm text-muted">{{ '@'.$user->username }}</span>
            <x-role-badge :role="$user->role" />
            @if($user->isSuspended())
                <span class="ml-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700 dark:bg-red-950 dark:text-red-300">
                    Suspendu jusqu'au {{ $user->suspended_until->format('d/m/Y') }}
                </span>
            @endif
            <span class="ml-1 text-xs text-muted">{{ $user->warnings_count }} avertissement(s)</span>
        </div>
        @if(auth()->user()->can('moderate', $user))
            <div class="flex gap-2">
                <button type="button" @click="warn = !warn" class="btn-secondary">Avertir</button>
                @if($user->isSuspended())
                    <form method="post" action="{{ route('moderation.users.unsuspend', $user) }}" data-remote="replace" data-target="{{ $target }}">
                        @csrf
                        <button type="submit" class="btn-secondary">Lever la suspension</button>
                    </form>
                @else
                    <button type="button" @click="suspend = !suspend" class="btn-secondary">Suspendre</button>
                @endif
            </div>
        @endif
    </div>

    <form x-show="warn" x-cloak method="post" action="{{ route('moderation.users.warn', $user) }}" class="mt-3 flex gap-2" data-remote="replace" data-target="{{ $target }}">
        @csrf
        <input type="text" name="reason" placeholder="Raison de l'avertissement" required class="field">
        <button type="submit" class="btn-primary shrink-0">Envoyer</button>
    </form>

    <form x-show="suspend" x-cloak method="post" action="{{ route('moderation.users.suspend', $user) }}" class="mt-3 flex gap-2" data-remote="replace" data-target="{{ $target }}">
        @csrf
        <input type="text" name="reason" placeholder="Raison de la suspension" required class="field">
        <input type="number" name="days" value="7" min="1" max="365" class="field w-24" title="Durée (jours)">
        <button type="submit" class="btn-primary shrink-0">Suspendre</button>
    </form>
</div>
