<x-admin-layout title="Vue d'ensemble" active="dashboard">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="card p-4"><p class="text-xs text-muted">Utilisateurs</p><p class="text-xl font-semibold text-ink">{{ $stats['users'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-muted">Sujets</p><p class="text-xl font-semibold text-ink">{{ $stats['topics'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-muted">Messages</p><p class="text-xl font-semibold text-ink">{{ $stats['posts'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-muted">Catégories</p><p class="text-xl font-semibold text-ink">{{ $stats['categories'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-muted">Signalements en attente</p><p class="text-xl font-semibold text-ink">{{ $stats['pending_reports'] }}</p></div>
    </div>

    @if($stats['pending_reports'] > 0)
        <div class="card border-amber-300 p-4 dark:border-amber-800">
            <p class="text-sm text-ink">{{ $stats['pending_reports'] }} signalement(s) en attente de traitement.</p>
            <a href="{{ route('moderation.reports.index') }}" class="mt-2 inline-block text-sm text-brand hover:underline">Voir la file de modération →</a>
        </div>
    @endif
</x-admin-layout>
