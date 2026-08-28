<x-app-layout>
    <x-slot:sidebar>
        <div class="card p-4">
            <h2 class="font-semibold text-ink">{{ $siteSettings['site_title'] }}</h2>
            <p class="mt-1 text-sm text-muted">{{ $siteSettings['site_description'] }}</p>
            <div class="mt-3 grid grid-cols-3 gap-2 border-t border-ink/10 pt-3 text-center">
                <div>
                    <p class="text-sm font-semibold text-ink">{{ $stats['members'] }}</p>
                    <p class="text-xs text-muted">Membres</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">{{ $stats['topics'] }}</p>
                    <p class="text-xs text-muted">Sujets</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">{{ $stats['posts'] }}</p>
                    <p class="text-xs text-muted">Messages</p>
                </div>
            </div>
            @auth
                <div class="mt-4">
                    <x-forum.new-topic-modal :categories="$categories" />
                </div>
            @endauth
        </div>
    </x-slot:sidebar>

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-ink">Fil d'actualité</h1>
    </div>

    <div class="space-y-2">
        @forelse($latestTopics as $topic)
            <x-forum.topic-row :topic="$topic" :show-category="true" />
        @empty
            <p class="card px-4 py-6 text-sm text-muted">Aucun sujet pour le moment.</p>
        @endforelse
    </div>
</x-app-layout>
