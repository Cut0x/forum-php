@props(['post', 'topic', 'canReply' => false, 'depth' => 0])

@php
    $children = $post->childrenTree ?? collect();
    $capped = $depth >= 6;
@endphp

<div>
    <x-forum.post :post="$post" :topic="$topic" :can-reply="$canReply" />

    <div id="replies-{{ $post->id }}" class="space-y-1 {{ $capped ? '' : 'ms-4 border-l-2 border-ink/10 ps-3 sm:ms-5 sm:ps-4' }}">
        @foreach($children as $child)
            <x-forum.post-thread :post="$child" :topic="$topic" :can-reply="$canReply" :depth="$depth + 1" />
        @endforeach
    </div>
</div>
