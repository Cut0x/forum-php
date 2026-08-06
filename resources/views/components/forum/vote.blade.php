@props(['score' => 0, 'userVote' => null, 'canVote' => false, 'action' => null, 'size' => 'md'])

@php
    $iconSize = $size === 'sm' ? 'h-3.5 w-3.5' : 'h-4 w-4';
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
    $gap = $size === 'sm' ? 'gap-0' : 'gap-0.5';
@endphp

@if($canVote && $action)
    <div
        x-data="{
            score: {{ (int) $score }},
            value: {{ $userVote ?? 'null' }},
            loading: false,
            vote(v) {
                if (this.loading) return;
                this.loading = true;
                fetch('{{ $action }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'value=' + v,
                })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(data => { this.score = data.score; this.value = data.value; })
                    .catch(() => window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Impossible d\'enregistrer le vote.', type: 'error' } })))
                    .finally(() => { this.loading = false; });
            },
        }"
        class="flex shrink-0 flex-col items-center {{ $gap }}"
    >
        <button type="button" @click="vote(1)" :disabled="loading" class="vote-btn" :class="value === 1 ? '!text-brand' : ''" aria-label="Vote positif">
            <x-icon name="arrow-up" class="{{ $iconSize }}" />
        </button>
        <span class="min-w-4 text-center {{ $textSize }} font-semibold text-ink" x-text="score"></span>
        <button type="button" @click="vote(-1)" :disabled="loading" class="vote-btn" :class="value === -1 ? '!text-red-600' : ''" aria-label="Vote négatif">
            <x-icon name="arrow-down" class="{{ $iconSize }}" />
        </button>
    </div>
@else
    <div class="flex shrink-0 flex-col items-center {{ $gap }} {{ $size === 'sm' ? 'px-1' : 'px-1.5' }}">
        <x-icon name="arrow-up" class="{{ $iconSize }} text-muted/50" />
        <span class="min-w-4 text-center {{ $textSize }} font-semibold text-muted">{{ $score }}</span>
        <x-icon name="arrow-down" class="{{ $iconSize }} text-muted/50" />
    </div>
@endif
