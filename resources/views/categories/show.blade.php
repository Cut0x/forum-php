<x-app-layout :title="$category->name">
    <x-slot:sidebar>
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white" style="background: hsl({{ \App\Support\Color::hueForLabel($category->slug) }} 65% 45%)">
                    {{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}
                </span>
                <h2 class="min-w-0 truncate font-semibold text-ink">{{ $category->name }}</h2>
            </div>
            <p class="mt-3 text-sm text-muted">{{ $category->description }}</p>
            <div class="mt-3 flex items-center gap-2 border-t border-ink/10 pt-3 text-xs text-muted">
                <span>{{ $category->topics_count ?? $topics->total() }} sujet{{ ($category->topics_count ?? $topics->total()) > 1 ? 's' : '' }}</span>
                @if($category->is_readonly)
                    <span>· Lecture seule</span>
                @endif
            </div>
        </div>
    </x-slot:sidebar>

    <nav class="mb-4 text-sm text-muted">
        <a href="{{ route('categories.index') }}" class="hover:text-ink">Catégories</a>
        <span class="mx-1">/</span>
        <span class="text-ink">{{ $category->name }}</span>
    </nav>

    <div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
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

    <div class="space-y-2">
        @forelse($topics as $topic)
            <x-forum.topic-row :topic="$topic" />
        @empty
            <p class="card px-4 py-6 text-sm text-muted">Aucun sujet dans cette catégorie pour le moment.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $topics->links() }}</div>
</x-app-layout>
