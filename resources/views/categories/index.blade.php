<x-app-layout :title="'Catégories'">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-ink">Catégories</h1>
        @auth
            <x-forum.new-topic-modal :categories="$categories" />
        @endauth
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach($categories as $category)
            <a href="{{ route('categories.show', $category) }}" class="card-hover flex items-start gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white" style="background: hsl({{ \App\Support\Color::hueForLabel($category->slug) }} 65% 45%)">
                    {{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                        @if($category->is_pinned)
                            <x-icon name="pin" class="h-3.5 w-3.5 shrink-0 text-brand" />
                        @endif
                        <h2 class="truncate font-medium text-ink">{{ $category->name }}</h2>
                        @if($category->is_readonly)
                            <span class="shrink-0 text-xs text-muted">(lecture seule)</span>
                        @endif
                    </div>
                    <p class="mt-1 truncate text-sm text-muted">{{ $category->description }}</p>
                    <p class="mt-2 text-xs text-muted">{{ $category->topics_count }} sujet{{ $category->topics_count > 1 ? 's' : '' }}</p>
                </div>
            </a>
        @endforeach
    </div>
</x-app-layout>
