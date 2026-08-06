@props(['topic', 'showCategory' => false])

@php
    $canVote = auth()->check() && auth()->user()->can('vote', $topic);
    $userVote = $topic->votes?->first()?->value;
@endphp

<article class="card-hover flex gap-2 p-3 sm:gap-3 sm:p-4">
    <x-forum.vote
        size="md"
        :score="$topic->score ?? 0"
        :user-vote="$userVote"
        :can-vote="$canVote"
        :action="route('topics.vote', $topic)"
    />

    <a href="{{ route('topics.show', $topic) }}" class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-1.5">
            @if($showCategory)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-ink/5 px-2 py-0.5 text-xs font-medium text-muted">
                    <span class="h-1.5 w-1.5 rounded-full" style="background: hsl({{ \App\Support\Color::hueForLabel($topic->category->slug) }} 65% 45%)"></span>
                    {{ $topic->category->name }}
                </span>
            @endif
            @if($topic->pinned_at)
                <x-icon name="pin" class="h-3.5 w-3.5 shrink-0 text-brand" />
            @endif
            @if($topic->locked_at)
                <x-icon name="lock" class="h-3.5 w-3.5 shrink-0 text-muted" />
            @endif
        </div>
        <h3 class="mt-1 truncate text-sm font-medium text-ink sm:text-base">{{ $topic->title }}</h3>
        <div class="mt-1.5 flex items-center gap-2 text-xs text-muted">
            <img src="{{ $topic->user->avatar ? asset('storage/'.$topic->user->avatar) : asset('images/default-avatar.jpg') }}" alt="" class="avatar h-4 w-4">
            <span class="truncate">{{ $topic->user->displayName() }}</span>
            <span>·</span>
            <time>{{ $topic->created_at->diffForHumans() }}</time>
            @if($topic->posts_count ?? null)
                <span class="ml-auto flex shrink-0 items-center gap-1">
                    <x-icon name="chat" class="h-3.5 w-3.5" /> {{ $topic->posts_count }}
                </span>
            @endif
        </div>
    </a>
</article>
