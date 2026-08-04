<x-moderation-layout title="Vue d'ensemble" active="dashboard">
    <div class="card p-4">
        <p class="text-sm text-ink">{{ $pendingCount }} signalement(s) en attente.</p>
        @if($pendingCount > 0)
            <a href="{{ route('moderation.reports.index') }}" class="mt-1 inline-block text-sm text-brand hover:underline">Voir la file →</a>
        @endif
    </div>

    <div class="card divide-y divide-ink/10">
        <div class="px-4 py-3"><h2 class="text-sm font-semibold text-ink">Signalements récents</h2></div>
        @forelse($pendingReports as $report)
            <div class="px-4 py-3 text-sm">
                <p class="text-ink">{{ $report->reporter->displayName() }} a signalé un {{ $report->reportable_type === \App\Models\Topic::class ? 'sujet' : 'message' }} ({{ $report->reason }})</p>
                <p class="text-xs text-muted">{{ $report->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="px-4 py-6 text-sm text-muted">Aucun signalement en attente.</p>
        @endforelse
    </div>

    <div class="card divide-y divide-ink/10">
        <div class="px-4 py-3"><h2 class="text-sm font-semibold text-ink">Actions récentes</h2></div>
        @forelse($recentActions as $action)
            <div class="px-4 py-3 text-sm">
                <p class="text-ink">{{ $action->moderator->displayName() }} - {{ $action->action }}</p>
                <p class="text-xs text-muted">{{ $action->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="px-4 py-6 text-sm text-muted">Aucune action récente.</p>
        @endforelse
    </div>
</x-moderation-layout>
