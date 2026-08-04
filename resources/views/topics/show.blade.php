@php
    $canEditTitle = auth()->check() && auth()->user()->can('update', $topic);
    $canDeleteTopic = auth()->check() && auth()->user()->can('delete', $topic);
    $canModerate = auth()->check() && auth()->user()->can('moderate', $topic);
    $canReply = auth()->check() && auth()->user()->can('reply', $topic);
    $canReport = auth()->check() && auth()->id() !== $topic->user_id;
@endphp

<x-app-layout :title="$topic->title">
    <nav class="mb-4 text-sm text-muted">
        <a href="{{ route('categories.show', $topic->category) }}" class="hover:text-ink">{{ $topic->category->name }}</a>
        <span class="mx-1">/</span>
        <span class="text-ink">{{ $topic->title }}</span>
    </nav>

    <div x-data="{ editingTitle: false }" class="mb-6">
        <div x-show="!editingTitle" class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-1.5 text-lg font-semibold text-ink">
                    @if($topic->pinned_at) <x-icon name="pin" class="h-4 w-4 text-brand" /> @endif
                    @if($topic->locked_at) <x-icon name="lock" class="h-4 w-4 text-muted" /> @endif
                    {{ $topic->title }}
                </h1>
                <p class="mt-1 text-sm text-muted">
                    Par <a href="{{ route('profile.show', $topic->user) }}" class="font-medium text-ink hover:underline">{{ $topic->user->displayName() }}</a>
                    <x-role-badge :role="$topic->user->role" />
                    · {{ $topic->created_at->diffForHumans() }}
                    @if($topic->locked_at) · <span class="text-muted">verrouillé</span> @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                @if($canEditTitle)
                    <button type="button" @click="editingTitle = true" class="text-muted hover:text-ink">Modifier le titre</button>
                @endif
                @if($canDeleteTopic)
                    <form method="post" action="{{ route('topics.destroy', $topic) }}" onsubmit="return confirm('Supprimer ce sujet ?');">
                        @csrf @method('delete')
                        <button type="submit" class="text-muted hover:text-red-600">Supprimer</button>
                    </form>
                @endif
                @if($canReport)
                    <x-forum.report-button :reportable="$topic" route="topics.reports.store" />
                @endif
            </div>
        </div>

        <form x-show="editingTitle" x-cloak method="post" action="{{ route('topics.update', $topic) }}" class="flex gap-2">
            @csrf @method('patch')
            <input type="text" name="title" value="{{ $topic->title }}" maxlength="180" class="field" required>
            <button type="submit" class="btn-primary">Enregistrer</button>
            <button type="button" @click="editingTitle = false" class="btn-secondary">Annuler</button>
        </form>

        @if($canModerate)
            <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-dashed border-ink/15 p-3 text-xs">
                <span class="font-medium text-muted">Modération :</span>
                <form method="post" action="{{ route('moderation.topics.'.($topic->locked_at ? 'unlock' : 'lock'), $topic) }}">
                    @csrf @method('patch')
                    <button type="submit" class="btn-ghost !px-2 !py-1">{{ $topic->locked_at ? 'Déverrouiller' : 'Verrouiller' }}</button>
                </form>
                <form method="post" action="{{ route('moderation.topics.'.($topic->pinned_at ? 'unpin' : 'pin'), $topic) }}">
                    @csrf @method('patch')
                    <button type="submit" class="btn-ghost !px-2 !py-1">{{ $topic->pinned_at ? 'Désépingler' : 'Épingler' }}</button>
                </form>
                <form method="post" action="{{ route('moderation.topics.move', $topic) }}" class="flex items-center gap-1">
                    @csrf @method('patch')
                    <select name="category_id" class="field !py-1 text-xs">
                        @foreach(\App\Models\Category::query()->orderBy('name')->get() as $cat)
                            <option value="{{ $cat->id }}" @selected($cat->id === $topic->category_id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-ghost !px-2 !py-1">Déplacer</button>
                </form>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        @foreach($posts as $post)
            <x-forum.post :post="$post" />
        @endforeach
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>

    <div class="card mt-6 p-4 sm:p-5">
        @if($topic->locked_at)
            <p class="text-sm text-muted">Ce sujet est verrouillé, les réponses ne sont plus possibles.</p>
        @elseif(!auth()->check())
            <p class="text-sm text-muted"><a href="{{ route('login') }}" class="text-brand hover:underline">Connectez-vous</a> pour répondre à ce sujet.</p>
        @elseif(!$canReply)
            <p class="text-sm text-muted">Vous ne pouvez pas répondre à ce sujet.</p>
        @else
            <form method="post" action="{{ route('posts.store', $topic) }}">
                @csrf
                <x-editor name="content" placeholder="Votre réponse…" />
                <button type="submit" class="btn-primary mt-3">Répondre</button>
            </form>
        @endif
    </div>
</x-app-layout>
