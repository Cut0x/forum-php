@props(['topic', 'showCategory' => false])

<a href="{{ route('topics.show', $topic) }}" class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-ink/5">
    <div class="min-w-0">
        <div class="flex items-center gap-1.5">
            @if($topic->pinned_at)
                <x-icon name="pin" class="h-3.5 w-3.5 shrink-0 text-brand" />
            @endif
            @if($topic->locked_at)
                <x-icon name="lock" class="h-3.5 w-3.5 shrink-0 text-muted" />
            @endif
            <h3 class="truncate text-sm font-medium text-ink">{{ $topic->title }}</h3>
        </div>
        <p class="mt-0.5 truncate text-xs text-muted">
            @if($showCategory)
                {{ $topic->category->name }} ·
            @endif
            {{ $topic->user->displayName() }}
            @if($topic->posts_count ?? null)
                · {{ $topic->posts_count }} message{{ $topic->posts_count > 1 ? 's' : '' }}
            @endif
        </p>
    </div>
    <time class="shrink-0 text-xs text-muted">{{ $topic->created_at->diffForHumans() }}</time>
</a>
