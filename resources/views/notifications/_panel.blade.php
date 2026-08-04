@php
    $tabs = ['all' => 'Toutes', 'reply' => 'Réponses', 'mention' => 'Mentions', 'vote' => 'Votes'];
@endphp

<div id="notifications-panel">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-lg font-semibold text-ink">Notifications</h1>
        <div class="flex gap-2">
            <form method="post" action="{{ route('notifications.read-all', ['type' => $type]) }}" data-remote="replace" data-target="#notifications-panel">
                @csrf @method('patch')
                <button type="submit" class="btn-secondary">Tout marquer comme lu</button>
            </form>
            <form method="post" action="{{ route('notifications.destroy-all', ['type' => $type]) }}" data-remote="replace" data-target="#notifications-panel">
                @csrf @method('delete')
                <x-confirm-submit
                    label="Tout supprimer"
                    class="btn-secondary"
                    title="Supprimer toutes les notifications ?"
                    message="Cette action est irréversible."
                />
            </form>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-1.5">
        @foreach($tabs as $key => $label)
            <a href="{{ route('notifications.index', $key === 'all' ? [] : ['type' => $key]) }}"
               class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $type === $key ? 'bg-brand text-white' : 'bg-ink/5 text-muted hover:text-ink' }}">
                {{ $label }}@if($key !== 'all' && ($counts[$key] ?? 0) > 0) ({{ $counts[$key] }})@endif
            </a>
        @endforeach
    </div>

    <div class="card divide-y divide-ink/10">
        @forelse($notifications as $notification)
            @php
                $link = isset($notification->data['topic_slug']) ? route('topics.show', $notification->data['topic_slug']) : '#';
            @endphp
            <div class="flex items-start justify-between gap-3 px-4 py-3">
                <a href="{{ $link }}" class="min-w-0 flex-1">
                    <p class="text-sm text-ink {{ $notification->read_at ? '' : 'font-medium' }}">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="mt-0.5 text-xs text-muted">{{ $notification->created_at->diffForHumans() }}</p>
                </a>
                <div class="flex items-center gap-2">
                    @unless($notification->read_at)
                        <span class="h-2 w-2 rounded-full bg-brand"></span>
                    @endunless
                    <form method="post" action="{{ route('notifications.destroy', $notification->id) }}" data-remote="replace" data-target="#notifications-panel">
                        @csrf @method('delete')
                        <button type="submit" class="text-xs text-muted hover:text-red-600">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="px-4 py-8 text-center text-sm text-muted">Aucune notification.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
