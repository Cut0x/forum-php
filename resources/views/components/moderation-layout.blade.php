@props(['title' => 'Modération', 'active' => null])

@php
    $links = [
        'dashboard' => ['label' => 'Vue d\'ensemble', 'route' => 'moderation.dashboard'],
        'reports' => ['label' => 'Signalements', 'route' => 'moderation.reports.index'],
        'users' => ['label' => 'Utilisateurs', 'route' => 'moderation.users.index'],
        'log' => ['label' => 'Journal', 'route' => 'moderation.log.index'],
    ];
@endphp

<x-app-layout :title="$title">
    <div class="grid gap-6 lg:grid-cols-[200px_1fr]">
        <aside class="lg:sticky lg:top-20 lg:self-start">
            <nav class="card flex gap-1 overflow-x-auto p-2 lg:flex-col lg:overflow-visible">
                @foreach($links as $key => $link)
                    <a href="{{ route($link['route']) }}" class="shrink-0 rounded-lg px-3 py-2 text-sm font-medium {{ $active === $key ? 'bg-brand text-white' : 'text-muted hover:bg-ink/5 hover:text-ink' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>
        <div class="min-w-0 space-y-6">
            <h1 class="text-lg font-semibold text-ink">{{ $title }}</h1>
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
