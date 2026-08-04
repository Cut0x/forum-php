@php
    $statuses = ['pending' => 'En attente', 'resolved' => 'Traités', 'dismissed' => 'Rejetés'];
    $reasons = ['spam' => 'Spam', 'abus' => 'Abus / harcèlement', 'hors_sujet' => 'Hors sujet', 'autre' => 'Autre'];
@endphp

<x-moderation-layout title="Signalements" active="reports">
    <div class="flex flex-wrap gap-1.5">
        @foreach($statuses as $key => $label)
            <a href="{{ route('moderation.reports.index', ['status' => $key]) }}" class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $status === $key ? 'bg-brand text-white' : 'bg-ink/5 text-muted hover:text-ink' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="card divide-y divide-ink/10">
        @forelse($reports as $report)
            @php
                $reportable = $report->reportable;
                $isTopic = $reportable instanceof \App\Models\Topic;
                $link = $reportable ? ($isTopic ? route('topics.show', $reportable) : route('topics.show', $reportable->topic).'#post-'.$reportable->id) : '#';
                $excerpt = $reportable ? ($isTopic ? $reportable->title : \Illuminate\Support\Str::limit(strip_tags($reportable->content), 140)) : 'Contenu supprimé';
            @endphp
            <div class="p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm text-ink">
                            <span class="font-medium">{{ $report->reporter->displayName() }}</span>
                            a signalé {{ $isTopic ? 'le sujet' : 'un message' }} -
                            <span class="text-muted">{{ $reasons[$report->reason] ?? $report->reason }}</span>
                        </p>
                        <a href="{{ $link }}" class="mt-1 block truncate text-sm text-brand hover:underline">{{ $excerpt }}</a>
                        @if($report->note)
                            <p class="mt-1 text-xs text-muted">« {{ $report->note }} »</p>
                        @endif
                        <p class="mt-1 text-xs text-muted">{{ $report->created_at->diffForHumans() }}</p>
                    </div>
                    @if($status === 'pending')
                        <div class="flex shrink-0 gap-2">
                            <form method="post" action="{{ route('moderation.reports.resolve', $report) }}" data-remote="remove" data-target="closest:div.p-4">
                                @csrf @method('patch')
                                <button type="submit" class="btn-secondary">Traiter</button>
                            </form>
                            <form method="post" action="{{ route('moderation.reports.dismiss', $report) }}" data-remote="remove" data-target="closest:div.p-4">
                                @csrf @method('patch')
                                <button type="submit" class="btn-ghost">Rejeter</button>
                            </form>
                        </div>
                    @else
                        <span class="shrink-0 text-xs text-muted">
                            {{ $status === 'resolved' ? 'Traité' : 'Rejeté' }} par {{ $report->resolver?->displayName() ?? '-' }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <p class="px-4 py-8 text-center text-sm text-muted">Aucun signalement.</p>
        @endforelse
    </div>

    <div>{{ $reports->links() }}</div>
</x-moderation-layout>
