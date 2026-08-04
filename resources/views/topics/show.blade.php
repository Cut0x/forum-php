@php
    $canEditTitle = auth()->check() && auth()->user()->can('update', $topic);
    $canDeleteTopic = auth()->check() && auth()->user()->can('delete', $topic);
    $canModerate = auth()->check() && auth()->user()->can('moderate', $topic);
    $canReply = auth()->check() && auth()->user()->can('reply', $topic);
    $canReport = auth()->check() && auth()->id() !== $topic->user_id;
    $csrf = csrf_token();
@endphp

<x-app-layout :title="$topic->title">
    <nav class="mb-4 text-sm text-muted">
        <a href="{{ route('categories.show', $topic->category) }}" class="hover:text-ink">{{ $topic->category->name }}</a>
        <span class="mx-1">/</span>
        <span class="text-ink">{{ $topic->title }}</span>
    </nav>

    <div
        x-data="{
            editingTitle: false,
            title: @js($topic->title),
            locked: @js((bool) $topic->locked_at),
            pinned: @js((bool) $topic->pinned_at),
            loading: false,
            call(url, body) {
                if (this.loading) return Promise.reject();
                this.loading = true;
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ $csrf }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: body || '',
                })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .finally(() => { this.loading = false });
            },
            notify(message) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message, type: 'success' } }));
            },
            fail() {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Une erreur est survenue.', type: 'error' } }));
            },
            saveTitle() {
                this.call('{{ route('topics.update', $topic) }}', '_method=PATCH&title=' + encodeURIComponent(this.title))
                    .then(data => { this.title = data.title; this.editingTitle = false; this.notify('Sujet mis à jour.'); })
                    .catch(() => this.fail());
            },
            toggleLock() {
                const url = this.locked ? '{{ route('moderation.topics.unlock', $topic) }}' : '{{ route('moderation.topics.lock', $topic) }}';
                this.call(url, '_method=PATCH')
                    .then(data => { this.locked = data.locked; this.notify(data.locked ? 'Sujet verrouillé.' : 'Sujet déverrouillé.'); })
                    .catch(() => this.fail());
            },
            togglePin() {
                const url = this.pinned ? '{{ route('moderation.topics.unpin', $topic) }}' : '{{ route('moderation.topics.pin', $topic) }}';
                this.call(url, '_method=PATCH')
                    .then(data => { this.pinned = data.pinned; this.notify(data.pinned ? 'Sujet épinglé.' : 'Sujet désépinglé.'); })
                    .catch(() => this.fail());
            },
            move(categoryId) {
                this.call('{{ route('moderation.topics.move', $topic) }}', '_method=PATCH&category_id=' + categoryId)
                    .then(data => this.notify('Sujet déplacé vers « ' + data.category + ' ».'))
                    .catch(() => this.fail());
            },
        }"
        class="mb-6"
    >
        <div x-show="!editingTitle" class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-1.5 text-lg font-semibold text-ink">
                    <template x-if="pinned"><x-icon name="pin" class="h-4 w-4 text-brand" /></template>
                    <template x-if="locked"><x-icon name="lock" class="h-4 w-4 text-muted" /></template>
                    <span x-text="title"></span>
                </h1>
                <p class="mt-1 text-sm text-muted">
                    Par <a href="{{ route('profile.show', $topic->user) }}" class="font-medium text-ink hover:underline">{{ $topic->user->displayName() }}</a>
                    <x-role-badge :role="$topic->user->role" />
                    · {{ $topic->created_at->diffForHumans() }}
                    <template x-if="locked"> · <span class="text-muted">verrouillé</span></template>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                @if($canEditTitle)
                    <button type="button" @click="editingTitle = true" class="text-muted hover:text-ink">Modifier le titre</button>
                @endif
                @if($canDeleteTopic)
                    <form method="post" action="{{ route('topics.destroy', $topic) }}">
                        @csrf @method('delete')
                        <x-confirm-submit
                            title="Supprimer ce sujet ?"
                            message="Le sujet et tous ses messages seront définitivement supprimés."
                        />
                    </form>
                @endif
                @if($canReport)
                    <x-forum.report-button :reportable="$topic" route="topics.reports.store" />
                @endif
            </div>
        </div>

        <div x-show="editingTitle" x-cloak class="flex gap-2">
            <input type="text" x-model="title" maxlength="180" class="field" required>
            <button type="button" @click="saveTitle()" :disabled="loading" class="btn-primary">Enregistrer</button>
            <button type="button" @click="editingTitle = false" class="btn-secondary">Annuler</button>
        </div>

        @if($canModerate)
            <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-dashed border-ink/15 p-3 text-xs">
                <span class="font-medium text-muted">Modération :</span>
                <button type="button" @click="toggleLock()" :disabled="loading" class="btn-ghost !px-2 !py-1" x-text="locked ? 'Déverrouiller' : 'Verrouiller'"></button>
                <button type="button" @click="togglePin()" :disabled="loading" class="btn-ghost !px-2 !py-1" x-text="pinned ? 'Désépingler' : 'Épingler'"></button>
                <div class="flex items-center gap-1">
                    <select class="field !py-1 text-xs" @change="move($event.target.value)">
                        @foreach(\App\Models\Category::query()->orderBy('name')->get() as $cat)
                            <option value="{{ $cat->id }}" @selected($cat->id === $topic->category_id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-muted">Déplacer</span>
                </div>
            </div>
        @endif
    </div>

    <div id="posts-list" class="space-y-4">
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
            <form method="post" action="{{ route('posts.store', $topic) }}" data-remote="append" data-target="#posts-list" data-reset-on-success>
                @csrf
                <x-editor name="content" placeholder="Votre réponse…" />
                <button type="submit" class="btn-primary mt-3">Répondre</button>
            </form>
        @endif
    </div>
</x-app-layout>
