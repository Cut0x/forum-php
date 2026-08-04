<x-app-layout>
    <section class="card mb-6 flex flex-col items-start justify-between gap-4 p-5 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-lg font-semibold text-ink">{{ $siteSettings['site_title'] }}</h1>
            <p class="mt-1 text-sm text-muted">{{ $siteSettings['site_description'] }}</p>
        </div>
        @auth
            <x-forum.new-topic-modal :categories="$categories" />
        @endauth
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card divide-y divide-ink/10 lg:col-span-2">
            <div class="px-4 py-3">
                <h2 class="text-sm font-semibold text-ink">Catégories</h2>
            </div>
            @forelse($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-ink/5">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            @if($category->is_pinned)
                                <x-icon name="pin" class="h-3.5 w-3.5 shrink-0 text-brand" />
                            @endif
                            <h3 class="truncate text-sm font-medium text-ink">{{ $category->name }}</h3>
                        </div>
                        <p class="mt-0.5 truncate text-xs text-muted">{{ $category->topics_count }} sujet{{ $category->topics_count > 1 ? 's' : '' }} · {{ $category->description }}</p>
                    </div>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-muted">Aucune catégorie pour le moment.</p>
            @endforelse
        </div>

        <div class="card divide-y divide-ink/10">
            <div class="px-4 py-3">
                <h2 class="text-sm font-semibold text-ink">Derniers sujets</h2>
            </div>
            @forelse($latestTopics as $topic)
                <x-forum.topic-row :topic="$topic" :show-category="true" />
            @empty
                <p class="px-4 py-6 text-sm text-muted">Aucun sujet pour le moment.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
