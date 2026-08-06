@php $canEdit = auth()->id() === $user->id; @endphp

<x-app-layout :title="$user->displayName()">
    <section class="card mb-6 p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/default-avatar.jpg') }}" class="avatar h-20 w-20 ring-2 ring-ink/10" alt="">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-lg font-semibold text-ink">{{ $user->displayName() }}</h1>
                    <span class="text-sm text-muted">{{ '@'.$user->username }}</span>
                    <x-role-badge :role="$user->role" />
                </div>
                @if($user->bio)
                    <p class="mt-1 whitespace-pre-line text-sm text-muted">{{ $user->bio }}</p>
                @endif
                <p class="mt-2 text-xs text-muted">Inscrit le {{ $user->created_at->format('d/m/Y') }}</p>

                @if($user->badges->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($user->badges as $badge)
                            <img src="{{ asset('images/badges/'.$badge->icon) }}" title="{{ $badge->name }}" alt="{{ $badge->name }}" class="h-6 w-6">
                        @endforeach
                    </div>
                @endif
            </div>
            @if($canEdit)
                <a href="{{ route('profile.edit') }}" class="btn-secondary shrink-0">Éditer le profil</a>
            @endif
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card-hover p-4"><p class="text-xs text-muted">Sujets</p><p class="text-xl font-semibold text-ink">{{ $stats['topics'] }}</p></div>
        <div class="card-hover p-4"><p class="text-xs text-muted">Messages</p><p class="text-xl font-semibold text-ink">{{ $stats['posts'] }}</p></div>
        <div class="card-hover p-4"><p class="text-xs text-muted">Badges</p><p class="text-xl font-semibold text-ink">{{ $stats['badges'] }}</p></div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="card divide-y divide-ink/10">
            <div class="px-4 py-3"><h2 class="text-sm font-semibold text-ink">Sujets récents</h2></div>
            @forelse($recentTopics as $topic)
                <a href="{{ route('topics.show', $topic) }}" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-ink/5">
                    <span class="truncate">{{ $topic->title }}</span>
                    <time class="shrink-0 text-xs text-muted">{{ $topic->created_at->diffForHumans() }}</time>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-muted">Aucun sujet.</p>
            @endforelse
        </div>
        <div class="card divide-y divide-ink/10">
            <div class="px-4 py-3"><h2 class="text-sm font-semibold text-ink">Derniers messages</h2></div>
            @forelse($recentPosts as $post)
                <a href="{{ route('topics.show', $post->topic) }}" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-ink/5">
                    <span class="truncate">{{ $post->topic->title }}</span>
                    <time class="shrink-0 text-xs text-muted">{{ $post->created_at->diffForHumans() }}</time>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-muted">Aucun message.</p>
            @endforelse
        </div>
    </div>

    @if($user->links->isNotEmpty())
        <div class="card mt-4 p-4">
            <h2 class="mb-2 text-sm font-semibold text-ink">Liens</h2>
            <div class="flex flex-col gap-1.5">
                @foreach($user->links as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 text-sm text-brand hover:underline">
                        <x-icon name="link" class="h-3.5 w-3.5" /> {{ $link->label }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
