@php
    $labels = [
        'lock' => 'a verrouillé', 'unlock' => 'a déverrouillé', 'pin' => 'a épinglé', 'unpin' => 'a désépinglé',
        'move' => 'a déplacé', 'warn' => 'a averti', 'suspend' => 'a suspendu', 'unsuspend' => 'a levé la suspension de',
    ];
@endphp

<x-moderation-layout title="Journal de modération" active="log">
    <div class="card divide-y divide-ink/10">
        @forelse($actions as $action)
            <div class="px-4 py-3 text-sm">
                <p class="text-ink">
                    <span class="font-medium">{{ $action->moderator->displayName() }}</span>
                    {{ $labels[$action->action] ?? $action->action }}
                    <span class="text-muted">{{ $action->targetLabel() }}</span>
                    @if(!empty($action->meta['reason']))
                        - {{ $action->meta['reason'] }}
                    @endif
                </p>
                <p class="text-xs text-muted">{{ $action->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="px-4 py-8 text-center text-sm text-muted">Aucune action enregistrée.</p>
        @endforelse
    </div>

    <div>{{ $actions->links() }}</div>
</x-moderation-layout>
