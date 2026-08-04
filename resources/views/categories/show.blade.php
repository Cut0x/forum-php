<x-app-layout :title="$category->name">
    <nav class="mb-4 text-sm text-muted">
        <a href="{{ route('categories.index') }}" class="hover:text-ink">Catégories</a>
        <span class="mx-1">/</span>
        <span class="text-ink">{{ $category->name }}</span>
    </nav>

    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="flex items-center gap-1.5 text-lg font-semibold text-ink">
                @if($category->is_pinned)
                    <x-icon name="pin" class="h-4 w-4 text-brand" />
                @endif
                {{ $category->name }}
            </h1>
            <p class="mt-1 text-sm text-muted">{{ $category->description }}</p>
        </div>
        @auth
            <x-forum.new-topic-modal :categories="$categories" :selected="$category" />
        @endauth
    </div>

    <div class="card divide-y divide-ink/10">
        @forelse($topics as $topic)
            <x-forum.topic-row :topic="$topic" />
        @empty
            <p class="px-4 py-6 text-sm text-muted">Aucun sujet dans cette catégorie pour le moment.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $topics->links() }}</div>
</x-app-layout>
