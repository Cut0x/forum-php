<x-app-layout :title="'Recherche'">
    <div class="card mb-6 p-5">
        <h1 class="mb-3 text-lg font-semibold text-ink">Recherche</h1>
        <form method="get" action="{{ route('search') }}" class="flex gap-2">
            <input type="search" name="q" value="{{ $query }}" placeholder="Rechercher un sujet, un message…" class="field">
            <button type="submit" class="btn-primary">Rechercher</button>
        </form>
        @if($query !== '')
            <p class="mt-3 text-sm text-muted">{{ $results->count() }} résultat{{ $results->count() > 1 ? 's' : '' }} pour « {{ $query }} ».</p>
        @endif
    </div>

    @if($query === '')
        <p class="card px-4 py-6 text-sm text-muted">Entrez une recherche pour afficher des résultats.</p>
    @elseif($results->isEmpty())
        <p class="card px-4 py-6 text-sm text-muted">Aucun résultat.</p>
    @else
        <div class="space-y-2">
            @foreach($results as $topic)
                <x-forum.topic-row :topic="$topic" :show-category="true" />
            @endforeach
        </div>
    @endif
</x-app-layout>
