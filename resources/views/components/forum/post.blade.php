@props(['post', 'topic', 'canReply' => false])

@php
    $deleted = $post->trashed();
    $canEdit = ! $deleted && auth()->check() && auth()->user()->can('update', $post);
    $canDelete = ! $deleted && auth()->check() && auth()->user()->can('delete', $post);
    $canReport = ! $deleted && auth()->check() && auth()->id() !== $post->user_id;
    $canVote = ! $deleted && auth()->check() && auth()->user()->can('vote', $post);
    $userVote = $post->votes->first()?->value;
@endphp

<article id="post-{{ $post->id }}" class="scroll-mt-20 -mx-2 rounded-lg px-2 py-2 transition hover:bg-ink/[0.03]">
    <div class="flex gap-2">
        @if(!$deleted)
            <x-forum.vote
                size="sm"
                :score="$post->score ?? 0"
                :user-vote="$userVote"
                :can-vote="$canVote"
                :action="route('posts.vote', $post)"
            />
        @endif

        <img src="{{ $post->user->avatar ? asset('storage/'.$post->user->avatar) : asset('images/default-avatar.jpg') }}" alt="" class="avatar h-6 w-6 shrink-0">

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <a href="{{ route('profile.show', $post->user) }}" class="text-sm font-semibold text-ink hover:underline">{{ $post->user->displayName() }}</a>
                <span class="text-xs text-muted">{{ '@'.$post->user->username }}</span>
                @if($primaryBadge = $post->user->primaryBadge())
                    <x-badge-tooltip :badge="$primaryBadge" size="h-5 w-5" />
                @endif
                <time class="text-xs text-muted">{{ $post->created_at->diffForHumans() }}</time>
                @if($post->edited_at)
                    <span class="text-xs text-muted">· modifié</span>
                @endif
            </div>

            @if($deleted)
                <p class="mt-2 text-sm italic text-muted">Message supprimé.</p>
            @else
                <div
                    x-data="{ editing: false }"
                    @if($canEdit) x-on:edit-post-{{ $post->id }}.window="editing = true" @endif
                    class="mt-2"
                >
                    <div x-show="!editing" class="prose-forum">{!! $post->rendered_content !!}</div>

                    @if($canEdit)
                        <div x-show="editing" x-cloak class="space-y-2">
                            <form method="post" action="{{ route('posts.update', $post) }}" data-remote="replace" data-target="closest:article">
                                @csrf
                                @method('patch')
                                <x-editor name="content" :value="$post->content" rows="4" />
                                <div class="mt-2 flex gap-2">
                                    <button type="submit" class="btn-primary">Enregistrer</button>
                                    <button type="button" @click="editing = false" class="btn-secondary">Annuler</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            @if(!$deleted)
                <div class="mt-3" x-data="{ replying: false }">
                    <div class="flex items-center gap-3">
                        @if($canReply)
                            <button type="button" @click="replying = !replying" class="text-xs text-muted hover:text-ink" x-text="replying ? 'Annuler' : 'Répondre'"></button>
                        @endif
                        <div class="ml-auto flex items-center gap-3 text-xs">
                            @if($canEdit)
                                <button type="button" x-data @click="$dispatch('edit-post-{{ $post->id }}')" class="text-muted hover:text-ink">Modifier</button>
                            @endif
                            @if($canDelete)
                                <form method="post" action="{{ route('posts.destroy', $post) }}" data-remote="replace" data-target="closest:article">
                                    @csrf
                                    @method('delete')
                                    <x-confirm-submit
                                        title="Supprimer ce message ?"
                                        message="Cette action est irréversible."
                                    />
                                </form>
                            @endif
                            @if($canReport)
                                <x-forum.report-button :reportable="$post" route="posts.reports.store" />
                            @endif
                        </div>
                    </div>

                    @if($canReply)
                        <div x-show="replying" x-cloak class="mt-2">
                            <form
                                method="post"
                                action="{{ route('posts.store', $topic) }}"
                                data-remote="append"
                                data-target="#replies-{{ $post->id }}"
                                data-reset-on-success
                                @submit="replying = false"
                            >
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $post->id }}">
                                <x-editor name="content" :id="'reply-content-'.$post->id" :value="'@'.$post->user->username.' '" rows="3" />
                                <button type="submit" class="btn-primary mt-2 !py-1.5 text-xs">Répondre</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</article>
